<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification
{
    public function __construct(public Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Appointment Cancelled',
            'body' => "Your appointment on {$this->appointment->scheduled_at} has been cancelled.",
            'appointment_id' => $this->appointment->id,
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Appointment Cancelled',
            'body' => "Your appointment on {$this->appointment->scheduled_at} has been cancelled.",
            'data' => [
                'type' => 'appointment_cancelled',
                'id' => (string) $this->appointment->id,
            ],
        ];
    }
}
