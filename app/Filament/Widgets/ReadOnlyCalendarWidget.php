<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class ReadOnlyCalendarWidget extends CalendarWidget
{
    public ?int $laboratory = null;

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

    public function getEvents(array $fetchInfo = []): array
    {
        $labFromQuery = is_numeric(request()->query('laboratory')) ? (int) request()->query('laboratory') : null;
        $effectiveLab = $this->laboratory ?? $labFromQuery ?? (Auth::user()->laboratory_id ?? null);

        $query = Schedule::query()->where('type', 'unstructured');

        if (! empty($effectiveLab)) {
            $query->where('laboratory_id', $effectiveLab);
        }

        return $query->with('laboratory')->get()
            ->map(fn (Schedule $schedule) => [
                'id' => $schedule->id,
                'title' => $schedule->title ?? ($schedule->laboratory->name ?? '—'),
                'start' => $schedule->start_at,
                'end' => $schedule->end_at,
            ])->toArray();
    }
}
