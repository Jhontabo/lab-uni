<?php

namespace App\Services;

use App\Exports\DashboardExport;
use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Loan;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    private function sanitize($value)
    {
        if (is_null($value)) {
            return null;
        }
        if (is_string($value)) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

            return $value;
        }
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        return $value;
    }

    public function generateDashboardReport()
    {
        $data = $this->getDashboardData();

        $pdf = PDF::loadView('reports.dashboard', $data)
            ->setPaper('a4', 'portrait')
            ->output();

        $filename = 'reporte_dashboard_'.Carbon::now()->format('Y-m-d_His').'.pdf';

        return response()->make($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function getDashboardData(): array
    {
        // ============ ESTADÍSTICAS GENERALES ============
        $productsCount = Product::count();
        $laboratoriesCount = Laboratory::count();
        $usersActive = User::where('status', true)->count();
        $usersTotal = User::count();
        $totalBookings = Booking::count();
        $totalLoans = Loan::count();
        $bookingsByLaboratoryRaw = Laboratory::query()
            ->leftJoin('bookings', 'laboratories.id', '=', 'bookings.laboratory_id')
            ->select(
                'laboratories.id',
                'laboratories.name',
                'laboratories.location',
                'laboratories.capacity',
                DB::raw('COUNT(bookings.id) as total_bookings')
            )
            ->groupBy('laboratories.id', 'laboratories.name', 'laboratories.location', 'laboratories.capacity')
            ->orderBy('total_bookings', 'desc')
            ->get();

        $bookingsByLaboratory = [];
        foreach ($bookingsByLaboratoryRaw as $item) {
            $bookingsByLaboratory[] = [
                'id' => $item->id,
                'name' => $this->sanitize($item->name),
                'location' => $this->sanitize($item->location),
                'capacity' => $item->capacity,
                'total_bookings' => (int) $item->total_bookings,
            ];
        }

        // ============ RESERVAS POR ESTADO ============
        $bookingsByStatusRaw = Booking::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $bookingsByStatus = [];
        foreach ($bookingsByStatusRaw as $item) {
            $bookingsByStatus[$this->sanitize($item->status)] = (int) $item->total;
        }

        // ============ RESERVAS POR TIPO DE PROYECTO ============
        $bookingsByProjectTypeRaw = Booking::select('project_type', DB::raw('count(*) as total'))
            ->whereNotNull('project_type')
            ->groupBy('project_type')
            ->get();

        $bookingsByProjectType = [];
        foreach ($bookingsByProjectTypeRaw as $item) {
            $bookingsByProjectType[$this->sanitize($item->project_type)] = (int) $item->total;
        }

        // ============ RESERVAS POR MES (ÚLTIMOS 12 MESES) ============
        $bookingsByMonthRaw = Booking::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $bookingsByMonth = [];
        foreach ($bookingsByMonthRaw as $item) {
            $monthName = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
            $bookingsByMonth[$monthName] = (int) $item->total;
        }

        // ============ PRÉSTAMOS POR ESTADO ============
        $loansByStatusRaw = Loan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $loansByStatus = [];
        foreach ($loansByStatusRaw as $item) {
            $loansByStatus[$this->sanitize($item->status)] = (int) $item->total;
        }

        $pendingLoans = Loan::where('status', 'pending')->count();
        $approvedLoans = Loan::where('status', 'approved')->count();
        $returnedLoans = Loan::where('status', 'returned')->count();
        $rejectedLoans = Loan::where('status', 'rejected')->count();

        // ============ PRÉSTAMOS VENCIDOS ============
        $overdueLoans = Loan::where('status', 'approved')
            ->where('estimated_return_at', '<', Carbon::now())
            ->count();

        // ============ PRODUCTOS MÁS SOLICITADOS (PRÉSTAMOS) ============
        $topProductsLoansRaw = Product::select(
            'products.id',
            'products.name',
            'products.available_quantity',
            DB::raw('COUNT(loans.id) as total_loans')
        )
            ->leftJoin('loans', 'products.id', '=', 'loans.product_id')
            ->groupBy('products.id', 'products.name', 'products.available_quantity')
            ->orderBy('total_loans', 'desc')
            ->limit(10)
            ->get();

        $topProductsLoans = [];
        foreach ($topProductsLoansRaw as $item) {
            $topProductsLoans[] = [
                'name' => $this->sanitize($item->name),
                'available_quantity' => (int) $item->available_quantity,
                'total_loans' => (int) $item->total_loans,
            ];
        }

        // ============ ÚLTIMAS RESERVAS (LISTA COMPLETA) ============
        $recentBookingsRaw = Booking::select([
            'bookings.id',
            'bookings.name',
            'bookings.last_name',
            'bookings.email',
            'bookings.status',
            'bookings.project_type',
            'bookings.start_at',
            'bookings.end_at',
            'bookings.created_at',
            'laboratories.name as laboratory_name',
            'users.name as user_name',
        ])
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('laboratories', 'bookings.laboratory_id', '=', 'laboratories.id')
            ->orderBy('bookings.created_at', 'desc')
            ->limit(20)
            ->get();

        $recentBookings = [];
        foreach ($recentBookingsRaw as $booking) {
            $recentBookings[] = [
                'id' => $booking->id,
                'name' => $this->sanitize($booking->name),
                'last_name' => $this->sanitize($booking->last_name),
                'email' => $this->sanitize($booking->email),
                'status' => $this->sanitize($booking->status),
                'project_type' => $this->sanitize($booking->project_type),
                'start_at' => $booking->start_at ? Carbon::parse($booking->start_at)->format('d/m/Y H:i') : null,
                'end_at' => $booking->end_at ? Carbon::parse($booking->end_at)->format('d/m/Y H:i') : null,
                'created_at' => Carbon::parse($booking->created_at)->format('d/m/Y H:i'),
                'laboratory_name' => $this->sanitize($booking->laboratory_name),
                'user_name' => $this->sanitize($booking->user_name),
            ];
        }

        // ============ ÚLTIMOS PRÉSTAMOS (LISTA COMPLETA) ============
        $recentLoansRaw = Loan::select([
            'loans.id',
            'loans.status',
            'loans.requested_at',
            'loans.approved_at',
            'loans.estimated_return_at',
            'loans.actual_return_at',
            'loans.observations',
            'users.name as user_name',
            'users.email as user_email',
            'products.name as product_name',
        ])
            ->leftJoin('users', 'loans.user_id', '=', 'users.id')
            ->leftJoin('products', 'loans.product_id', '=', 'products.id')
            ->orderBy('loans.created_at', 'desc')
            ->limit(20)
            ->get();

        $recentLoans = [];
        foreach ($recentLoansRaw as $loan) {
            $recentLoans[] = [
                'id' => $loan->id,
                'status' => $this->sanitize($loan->status),
                'requested_at' => $loan->requested_at ? Carbon::parse($loan->requested_at)->format('d/m/Y H:i') : null,
                'approved_at' => $loan->approved_at ? Carbon::parse($loan->approved_at)->format('d/m/Y H:i') : null,
                'estimated_return_at' => $loan->estimated_return_at ? Carbon::parse($loan->estimated_return_at)->format('d/m/Y') : null,
                'actual_return_at' => $loan->actual_return_at ? Carbon::parse($loan->actual_return_at)->format('d/m/Y H:i') : null,
                'observations' => $this->sanitize($loan->observations),
                'user_name' => $this->sanitize($loan->user_name),
                'user_email' => $this->sanitize($loan->user_email),
                'product_name' => $this->sanitize($loan->product_name),
            ];
        }

        // ============ INVENTARIO COMPLETO DE PRODUCTOS ============
        $productsRaw = Product::select([
            'id',
            'name',
            'description',
            'available_quantity',
            'brand',
            'model',
            'serial_number',
            'location',
            'status',
            'available_for_loan',
            'acquisition_date',
            'unit_cost',
        ])
            ->orderBy('name')
            ->get();

        $products = [];
        foreach ($productsRaw as $product) {
            $products[] = [
                'id' => $product->id,
                'name' => $this->sanitize($product->name),
                'description' => $this->sanitize($product->description),
                'available_quantity' => (int) $product->available_quantity,
                'brand' => $this->sanitize($product->brand),
                'model' => $this->sanitize($product->model),
                'serial_number' => $this->sanitize($product->serial_number),
                'location' => $this->sanitize($product->location),
                'status' => $this->sanitize($product->status),
                'available_for_loan' => $product->available_for_loan,
                'acquisition_date' => $product->acquisition_date ? Carbon::parse($product->acquisition_date)->format('d/m/Y') : null,
                'unit_cost' => $product->unit_cost ? number_format($product->unit_cost, 2) : null,
            ];
        }

        // ============ LABORATORIOS COMPLETOS ============
        $laboratoriesRaw = Laboratory::select([
            'id',
            'name',
            'location',
            'capacity',
            'user_id',
        ])
            ->orderBy('name')
            ->get();

        $laboratories = [];
        foreach ($laboratoriesRaw as $lab) {
            $laboratories[] = [
                'id' => $lab->id,
                'name' => $this->sanitize($lab->name),
                'location' => $this->sanitize($lab->location),
                'capacity' => $lab->capacity,
                'status' => 'Activo',
            ];
        }

        // ============ USUARIOS POR ROL ============
        $usersByRoleRaw = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name as role_name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        $usersByRole = [];
        foreach ($usersByRoleRaw as $item) {
            $usersByRole[$this->sanitize($item->role_name)] = (int) $item->total;
        }

        // ============ RESERVAS PENDIENTES ============
        $pendingBookings = Booking::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingBookingsArray = [];
        foreach ($pendingBookings as $booking) {
            $pendingBookingsArray[] = [
                'id' => $booking->id,
                'name' => $this->sanitize($booking->name),
                'last_name' => $this->sanitize($booking->last_name),
                'email' => $this->sanitize($booking->email),
                'project_type' => $this->sanitize($booking->project_type),
                'start_at' => $booking->start_at ? Carbon::parse($booking->start_at)->format('d/m/Y H:i') : null,
                'created_at' => Carbon::parse($booking->created_at)->format('d/m/Y H:i'),
            ];
        }

        return [
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
            'stats' => [
                'products' => $productsCount,
                'laboratories' => $laboratoriesCount,
                'usersActive' => $usersActive,
                'usersTotal' => $usersTotal,
                'totalBookings' => $totalBookings,
                'totalLoans' => $totalLoans,
                'overdueLoans' => $overdueLoans,
            ],
            'bookingsByLaboratory' => $bookingsByLaboratory,
            'bookingsByStatus' => $bookingsByStatus,
            'bookingsByProjectType' => $bookingsByProjectType,
            'bookingsByMonth' => $bookingsByMonth,
            'loansByStatus' => $loansByStatus,
            'loans' => [
                'pending' => $pendingLoans,
                'approved' => $approvedLoans,
                'returned' => $returnedLoans,
                'rejected' => $rejectedLoans,
                'overdue' => $overdueLoans,
            ],
            'topProductsLoans' => $topProductsLoans,
            'recentBookings' => $recentBookings,
            'recentLoans' => $recentLoans,
            'products' => $products,
            'laboratories' => $laboratories,
            'usersByRole' => $usersByRole,
            'pendingBookings' => $pendingBookingsArray,
        ];
    }

    public function generateExcelReport()
    {
        $data = $this->getDashboardData();

        $export = new DashboardExport($data);

        $filename = 'reporte_dashboard_'.Carbon::now()->format('Y-m-d_His');

        return Excel::download($export, $filename.'.xlsx');
    }
}
