<?php

namespace App\Contracts\Providers;

interface PushSender
{
    /**
     * @param  list<string>  $fcmTokens
     * @param  array<string, mixed>  $data  deep-link payload, e.g. { "type": "gold", "route": "gold" }
     */
    public function send(array $fcmTokens, string $title, string $body, array $data = []): void;
}
