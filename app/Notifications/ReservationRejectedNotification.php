<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationRejectedNotification extends Notification implements ShouldQueue
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
        $reasonText = $this->booking->rejection_reason ? " Motivo: {$this->booking->rejection_reason}" : '';

        return [
            'title' => 'Reserva Rechazada ❌',
            'body' => "Tu reserva para el laboratorio {$this->booking->laboratory->name} ha sido rechazada.{$reasonText}",
            'icon' => 'heroicon-o-x-circle',
            'iconColor' => 'danger',
        ];
    }
}
