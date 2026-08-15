<?php

namespace App\Notifications;

use App\Models\AppointmentSession;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class SessionStatusNotification extends Notification
{
    private array $messages = [
        'in_progress' => ['title' => 'Session Started', 'body' => 'Your doctor has started your session.'],
        'completed' => ['title' => 'Session Completed', 'body' => 'Your session has been completed.'],
        'cancelled' => ['title' => 'Session Cancelled', 'body' => 'Your session has been cancelled.'],
    ];

    public function __construct(public AppointmentSession $session) {}

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        $message = $this->messages[$this->session->status] ?? ['title' => 'Session Update', 'body' => 'Your session status has changed.'];

        return [
            'title' => $message['title'],
            'body' => $message['body'],
            'session_id' => $this->session->id,
        ];
    }

    public function toFcm($notifiable): array
    {
        $message = $this->messages[$this->session->status] ?? ['title' => 'Session Update', 'body' => 'Your session status has changed.'];

        return [
            'title' => $message['title'],
            'body' => $message['body'],
            'data' => [
                'type' => 'session_'.$this->session->status,
                'id' => (string) $this->session->id,
            ],
        ];
    }
}
