<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => '¡Reserva Aprobada! 🎉',
            'body' => "Tu reserva para el laboratorio {$this->booking->laboratory->name} ha sido aprobada. Horario: {$this->booking->start_at->format('d/m/Y H:i')} - {$this->booking->end_at->format('d/m/Y H:i')}",
            'actions' => [
                'view' => [
                    'label' => 'Ver Detalles',
                    'url' => route('filament.admin.resources.booking.requests.view', $this->booking->id),
                ],
            ],
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'success',
        ];
    }
}
