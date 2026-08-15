<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class AppointmentNoShowNotification extends Notification
{
    public function __construct(public Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Patient No-Show',
            'body' => "Patient did not show up for the appointment on {$this->appointment->scheduled_at}.",
            'appointment_id' => $this->appointment->id,
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Patient No-Show',
            'body' => "Patient did not show up for the appointment on {$this->appointment->scheduled_at}.",
            'data' => [
                'type' => 'appointment_no_show',
                'id' => (string) $this->appointment->id,
            ],
        ];
    }
}
