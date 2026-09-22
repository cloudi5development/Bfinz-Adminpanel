{{--
    Settings — SMS / OTP API.

    Nettyfish gateway credentials that App\Services\SendSmsService reads (via
    Setting::get) to send the mobile app's login OTPs, plus the test
    mobile/OTP pair that App\Services\OtpService accepts as a QA bypass.
    Unlike the other settings pages this one is fully wired: it really saves.
--}}
@extends('backend.template.layouts.template-base')

@section('content')

    <form method="POST" action="{{ route('backend.settings.sms.update') }}">
        @csrf

        <x-admin.page-header
            title="SMS / OTP API"
            description="Nettyfish gateway credentials used to send and verify mobile login OTPs.">
            <button type="submit" class="btn btn--primary">
                <x-admin.icon name="save" /> Save settings
            </button>
        </x-admin.page-header>

        <div class="grid grid--2">

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Gateway credentials</h2>
                        <p class="card__desc">From your Nettyfish account dashboard.</p>
                    </div>
                </div>

                <div class="card__body">
                    <div class="field">
                        <label class="field__label" for="sms_nettyfish_api_key">API key</label>
                        <div style="position:relative">
                            <input
                                class="input"
                                type="password"
                                id="sms_nettyfish_api_key"
                                name="sms_nettyfish_api_key"
                                autocomplete="off"
                                placeholder="{{ App\Models\Setting::get('sms_nettyfish_api_key') ? 'Leave blank to keep the current key' : 'Enter the Nettyfish API key' }}"
                                value="{{ old('sms_nettyfish_api_key') }}"
                                style="padding-right:2.5rem">
                            <button type="button" class="js-toggle-secret" data-target="sms_nettyfish_api_key"
                                    style="position:absolute;right:.5rem;top:50%;transform:translateY(-50%);background:none;border:0;padding:.25rem;cursor:pointer;color:var(--muted, #6b7280)"
                                    aria-label="Show API key">
                                <x-admin.icon name="eye" size="16" />
                            </button>
                        </div>
                        <p class="field__hint">
                            @if (App\Models\Setting::get('sms_nettyfish_api_key'))
                                A key is currently saved. Leave this blank to keep it unchanged.
                            @else
                                Not configured yet — OTPs cannot be sent until this is set.
                            @endif
                        </p>
                        @error('sms_nettyfish_api_key') <p class="field__hint" style="color:#c0392b">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="sms_nettyfish_sender_id">Sender ID</label>
                        <input class="input" id="sms_nettyfish_sender_id" name="sms_nettyfish_sender_id"
                               placeholder="e.g. BFINZL"
                               value="{{ old('sms_nettyfish_sender_id', App\Models\Setting::get('sms_nettyfish_sender_id')) }}">
                        @error('sms_nettyfish_sender_id') <p class="field__hint" style="color:#c0392b">{{ $message }}</p> @enderror
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="sms_nettyfish_sms_type">SMS type</label>
                            <select class="select" id="sms_nettyfish_sms_type" name="sms_nettyfish_sms_type">
                                @php $smsType = old('sms_nettyfish_sms_type', App\Models\Setting::get('sms_nettyfish_sms_type', 'transactional')); @endphp
                                <option value="transactional" @selected($smsType === 'transactional')>Transactional</option>
                                <option value="promotional" @selected($smsType === 'promotional')>Promotional</option>
                            </select>
                            <p class="field__hint">OTPs should stay transactional (DND-exempt).</p>
                        </div>
                        <div class="field">
                            <label class="field__label" for="sms_nettyfish_route">Route</label>
                            <input class="input" id="sms_nettyfish_route" name="sms_nettyfish_route"
                                   value="{{ old('sms_nettyfish_route', App\Models\Setting::get('sms_nettyfish_route')) }}">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="sms_otp_dlt_template_id">DLT template ID</label>
                        <input class="input" id="sms_otp_dlt_template_id" name="sms_otp_dlt_template_id"
                               value="{{ old('sms_otp_dlt_template_id', App\Models\Setting::get('sms_otp_dlt_template_id')) }}">
                        <p class="field__hint">TRAI-registered template ID for the OTP message content.</p>
                        @error('sms_otp_dlt_template_id') <p class="field__hint" style="color:#c0392b">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">QA test bypass</h2>
                        <p class="card__desc">Lets app-store reviewers and QA sign in without a real SMS.</p>
                    </div>
                </div>

                <div class="card__body">
                    <div class="field">
                        <label class="field__label" for="sms_test_mobile">Test mobile number</label>
                        <input class="input" id="sms_test_mobile" name="sms_test_mobile" inputmode="numeric" maxlength="10"
                               placeholder="10-digit number, e.g. 9999999999"
                               value="{{ old('sms_test_mobile', App\Models\Setting::get('sms_test_mobile')) }}">
                        @error('sms_test_mobile') <p class="field__hint" style="color:#c0392b">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="sms_test_otp">Test OTP</label>
                        <input class="input" id="sms_test_otp" name="sms_test_otp" inputmode="numeric" maxlength="4"
                               placeholder="4-digit code, e.g. 1234"
                               value="{{ old('sms_test_otp', App\Models\Setting::get('sms_test_otp')) }}">
                        @error('sms_test_otp') <p class="field__hint" style="color:#c0392b">{{ $message }}</p> @enderror
                    </div>

                    <div class="flash flash--warning" style="margin-bottom:0">
                        <x-admin.icon name="alert" />
                        <div>
                            When set, this mobile number skips the real SMS gateway entirely and always
                            accepts this exact OTP. Anyone who knows both values can sign in as that
                            account — leave both blank in production once testing is done.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.js-toggle-secret').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    </script>
@endpush
