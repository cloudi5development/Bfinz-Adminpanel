<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Saves the Nettyfish SMS gateway credentials that App\Services\SendSmsService
 * and App\Services\OtpService read via Setting::get() to send/verify mobile
 * login OTPs. See resources/views/backend/pages/settings-sms.blade.php.
 */
class SmsSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sms_nettyfish_api_key' => ['nullable', 'string', 'max:255'],
            'sms_nettyfish_sender_id' => ['nullable', 'string', 'max:60'],
            'sms_nettyfish_sms_type' => ['nullable', 'in:transactional,promotional'],
            'sms_nettyfish_route' => ['nullable', 'string', 'max:60'],
            'sms_otp_dlt_template_id' => ['nullable', 'string', 'max:60'],
            'sms_test_mobile' => ['nullable', 'digits:10'],
            'sms_test_otp' => ['nullable', 'digits:4'],
        ]);

        // API key is masked in the form once set; leaving it blank on a later
        // save must keep the stored value rather than wiping it out.
        if (blank($data['sms_nettyfish_api_key'] ?? null)) {
            unset($data['sms_nettyfish_api_key']);
        }

        Setting::putMany($data);

        return back()->with('success', 'SMS / OTP API settings saved.');
    }
}
