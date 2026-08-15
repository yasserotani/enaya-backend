<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class AppointmentRescheduledNotification extends Notification
{
    public function __construct(public Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Appointment Rescheduled',
            'body' => "Your appointment has been rescheduled to {$this->appointment->scheduled_at}.",
            'appointment_id' => $this->appointment->id,
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Appointment Rescheduled',
            'body' => "Your appointment has been rescheduled to {$this->appointment->scheduled_at}.",
            'data' => [
                'type' => 'appointment_rescheduled',
                'id' => (string) $this->appointment->id,
            ],
        ];
    }
}
