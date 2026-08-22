<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NewAppointmentNotification extends Notification
{
    public function __construct(public Appointment $appointment) {}

    public function via($notifiable): array
    {
        Log::info('NewAppointmentNotification channels selected', [
            'notifiable_id' => $notifiable->id,
            'channels' => ['database', FcmChannel::class],
            'appointment_id' => $this->appointment->id,
        ]);

        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        Log::info('NewAppointmentNotification preparing database payload', [
            'notifiable_id' => $notifiable->id,
            'appointment_id' => $this->appointment->id,
        ]);

        return [
            'title' => 'New Appointment',
            'body' => "You have a new appointment on {$this->appointment->scheduled_at}",
            'appointment_id' => $this->appointment->id,
            'patient_name' => $this->appointment->patient->name,
        ];
    }

    public function toFcm($notifiable): array
    {
        Log::info('NewAppointmentNotification preparing FCM payload', [
            'notifiable_id' => $notifiable->id,
            'appointment_id' => $this->appointment->id,
        ]);

        return [
            'title' => 'New Appointment',
            'body' => "You have a new appointment on {$this->appointment->scheduled_at}",
            'data' => [
                'type' => 'appointment',
                'id' => (string) $this->appointment->id,
                'patient_name' => (string) $this->appointment->patient->name,
            ],
        ];
    }
}
