<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new class($this->data['stats'] ?? [], 'Resumen') implements FromCollection, WithHeadings
        {
            private $data;

            private $title;

            public function __construct(array $data, string $title)
            {
                $this->data = $data;
                $this->title = $title;
            }

            public function collection()
            {
                $rows = [];
                foreach ($this->data as $key => $value) {
                    $rows[] = [
                        'Nombre' => ucfirst(str_replace('_', ' ', $key)),
                        'Valor' => $value,
                    ];
                }

                return collect($rows);
            }

            public function headings(): array
            {
                return ['Indicador', 'Valor'];
            }
        };

        $sheets[] = new class($this->data['products'] ?? [], 'Productos') implements FromCollection, WithHeadings
        {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return ['ID', 'Nombre', 'Descripción', 'Cantidad', 'Marca', 'Modelo', 'Serial', 'Ubicación', 'Estado', 'Préstamo', 'Costo'];
            }
        };

        $sheets[] = new class($this->data['recentBookings'] ?? [], 'Reservas Recientes') implements FromCollection, WithHeadings
        {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return ['ID', 'Nombre', 'Email', 'Estado', 'Proyecto', 'Laboratorio', 'Fecha Inicio', 'Fecha Fin'];
            }
        };

        $sheets[] = new class($this->data['recentLoans'] ?? [], 'Préstamos Recientes') implements FromCollection, WithHeadings
        {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return ['ID', 'Usuario', 'Email', 'Producto', 'Estado', 'Solicitado', 'Aprobado', 'Devolución Est.', 'Devolución Real'];
            }
        };

        $sheets[] = new class($this->data['laboratories'] ?? [], 'Laboratorios') implements FromCollection, WithHeadings
        {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return ['ID', 'Nombre', 'Ubicación', 'Capacidad', 'Estado'];
            }
        };

        return $sheets;
    }
}
