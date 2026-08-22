<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
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
            Log::info('FCM: no device tokens', ['user_id' => $notifiable->id]);
            return;
        }

        $payload = $notification->toFcm($notifiable);
        $messaging = app('firebase.messaging');

        foreach ($tokens as $token) {
            try {
                $response = $messaging->send(
                    CloudMessage::withTarget('token', $token)
                        ->withNotification(FirebaseNotification::create(
                            $payload['title'],
                            $payload['body']
                        ))
                        ->withData($payload['data'] ?? [])
                        ->withAndroidConfig([
                            'priority' => 'high',
                            'notification' => [
                                'sound' => 'default',
                                'channel_id' => 'high_importance_channel', // سنقوم بتعريف هذه القناة في الموبايل
                            ],
                        ])
                        ->withApnsConfig([
                            'payload' => [
                                'aps' => ['sound' => 'default'],
                            ],
                        ])

                );

                // Log successful send with the messaging response (message id)
                Log::info('FCM: message sent', [
                    'user_id' => $notifiable->id,
                    'token' => $token,
                    'message_id' => $response,
                    'payload' => $payload,
                ]);
            } catch (NotFound $e) {
                // token no longer valid — remove from DB and log
                Log::warning('FCM: token not found, deleting', [
                    'user_id' => $notifiable->id,
                    'token' => $token,
                    'exception' => $e->getMessage(),
                ]);

                $notifiable->deviceTokens()->where('fcm_token', $token)->delete();
            } catch (\Throwable $e) {
                // Log failures, but don't let them crash the request
                Log::error('FCM: send error', [
                    'user_id' => $notifiable->id,
                    'token' => $token,
                    'exception' => $e->getMessage(),
                ]);

                report($e);
            }
        }
    }
}
