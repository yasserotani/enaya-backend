<?php

namespace App\Notifications;

use App\Models\Prescription;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class NewPrescriptionNotification extends Notification
{
    public function __construct(public Prescription $prescription) {}

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'New Prescription',
            'body' => 'Your doctor has issued a new prescription for you.',
            'prescription_id' => $this->prescription->id,
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'New Prescription',
            'body' => 'Your doctor has issued a new prescription for you.',
            'data' => [
                'type' => 'prescription',
                'id' => (string) $this->prescription->id,
            ],
        ];
    }
}
