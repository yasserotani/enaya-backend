<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class UserLoggedInNotification extends Notification
{
    public function __construct(public ?string $ip = null, public ?string $agent = null) {}

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Login Successful',
            'body' => 'You have logged in successfully.',
            'ip' => $this->ip,
            'agent' => $this->agent,
            'time' => now()->toDateTimeString(),
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Login Successful',
            'body' => 'Welcome back!',
            'data' => [
                'type' => 'login_success',
                'time' => (string) now(),
            ],
        ];
    }
}
