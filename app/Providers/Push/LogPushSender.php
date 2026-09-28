<?php

namespace App\Providers\Push;

use App\Contracts\Providers\PushSender;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder until Firebase project credentials exist (docs/BUILD_SPEC.md
 * §13 Q10 — hosting/keys still open). Every "push" just logs instead of
 * calling FCM, so alert-evaluation and notification creation can be built
 * and tested end-to-end now; swapping in a real
 * App\Providers\Push\FcmPushSender later is one class + a config change.
 */
class LogPushSender implements PushSender
{
    public function send(array $fcmTokens, string $title, string $body, array $data = []): void
    {
        Log::info('Push notification (no FCM provider configured)', [
            'tokens' => count($fcmTokens),
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
