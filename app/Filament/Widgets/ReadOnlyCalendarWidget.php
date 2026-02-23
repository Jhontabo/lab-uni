<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Carbon\Carbon;

class ReadOnlyCalendarWidget extends CalendarWidget
{
    public ?int $laboratoryId = null;

    public function mount(): void
    {
        $this->laboratoryId = request()->query('laboratory') ? (int) request()->query('laboratory') : null;
    }

    public static function canView(): bool
    {
        if (request()->routeIs('filament.pages.dashboard')) {
            return false;
        }

        return parent::canView();
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }

    public function config(): array
    {
        return array_merge(parent::config(), [
            'selectable' => false,
            'editable' => false,
            'eventClick' => null,
            'eventDrop' => null,
            'eventResize' => null,
            'hiddenDays' => [0, 6],
            'firstDay' => 1,
        ]);
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $start = Carbon::parse($fetchInfo['start']);
        $end = Carbon::parse($fetchInfo['end']);

        $laboratoryId = $this->laboratoryId;

        $query = Schedule::query()
            ->with('laboratory')
            ->with(['booking' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->where('type', 'unstructured')
            ->whereBetween('start_at', [$start, $end]);

        if ($laboratoryId) {
            $query->where('laboratory_id', $laboratoryId);
        }

        return $query->get()
            ->map(function (Schedule $schedule) {
                $hasApprovedBooking = $schedule->booking->isNotEmpty();

                return [
                    'id' => $schedule->id,
                    'title' => $hasApprovedBooking
                        ? 'Ocupado: '.($schedule->laboratory->name ?? '')
                        : 'Libre: '.($schedule->laboratory->name ?? ''),
                    'start' => $schedule->start_at,
                    'end' => $schedule->end_at,
                    'backgroundColor' => $hasApprovedBooking ? '#ef4444' : '#22c55e',
                    'borderColor' => $hasApprovedBooking ? '#dc2626' : '#16a34a',
                    'textColor' => '#ffffff',
                ];
            })->toArray();
    }
}
