<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FcmChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $tokens = $notifiable->deviceTokens()->pluck('fcm_token');

        if ($tokens->isEmpty()) {
            return;
        }

        $payload = $notification->toFcm($notifiable);
        $messaging = app('firebase.messaging');

        foreach ($tokens as $token) {
            try {
                $messaging->send(
                    CloudMessage::withTarget('token', $token)
                        ->withNotification(FirebaseNotification::create(
                            $payload['title'],
                            $payload['body']
                        ))
                        ->withData($payload['data'] ?? [])
                );
            } catch (NotFound $e) {
                $notifiable->deviceTokens()->where('fcm_token', $token)->delete();
            } catch (\Throwable $e) {
                // catches EVERYTHING else — timeouts, no internet, Firebase down, anything
                // log it, but never let it crash the request
                report($e);
            }
        }
    }
}
