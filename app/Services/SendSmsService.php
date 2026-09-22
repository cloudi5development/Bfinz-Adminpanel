<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends transactional SMS through the Nettyfish gateway. All credentials are
 * admin-editable via the Settings table (Setting::get) rather than .env, so
 * they can be rotated without a deploy.
 */
class SendSmsService
{
    private const GATEWAY_URL = 'http://retailsms.nettyfish.com/api/mt/SendSMS';

    public function sendOtp(string $mobile, string $otp): bool
    {
        // Dev/QA bypass: a designated test mobile never triggers a real SMS.
        $testMobile = Setting::get('sms_test_mobile');

        if ($testMobile && $mobile === $testMobile) {
            Log::info('SMS skipped for test mobile', ['mobile' => $this->maskMobile($mobile)]);

            return true;
        }

        $apiKey = Setting::get('sms_nettyfish_api_key');
        $senderId = Setting::get('sms_nettyfish_sender_id');

        if (! $apiKey || ! $senderId) {
            Log::warning('SMS gateway not configured; OTP not sent', ['mobile' => $this->maskMobile($mobile)]);

            return false;
        }

        $message = "{$otp} is your one-time password for account login. Valid for 10 minutes. Do not share with anyone. Thanks, Chotekisan Team";

        try {
            $response = Http::timeout(10)->get(self::GATEWAY_URL, [
                'APIKey' => $apiKey,
                'senderid' => $senderId,
                'channel' => Setting::get('sms_nettyfish_sms_type', 'Trans') === 'promotional' ? 'PROMO' : 'TRANS',
                'DCS' => '0',
                'flashsms' => '0',
                'number' => $mobile,
                'text' => $message,
                'route' => Setting::get('sms_nettyfish_route'),
                'DLTTemplateId' => Setting::get('sms_otp_dlt_template_id'),
            ]);

            dd($response->body());
            $json = $response->json();

            if (($json['ErrorCode'] ?? null) === '000') {
                return true;
            }

            Log::warning('SMS gateway rejected OTP send', ['mobile' => $this->maskMobile($mobile), 'response' => $json]);

            return false;
        } catch (\Throwable $e) {
            Log::error('SMS gateway request failed', ['mobile' => $this->maskMobile($mobile), 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function maskMobile(string $mobile): string
    {
        return substr($mobile, 0, 4).'******';
    }
}
