{{--
    Settings — General.

    Brand identity and contact details the app and its emails read from.
--}}
@extends('backend.template.layouts.template-base')

@section('content')

    <x-admin.page-header
        title="General Settings"
        description="Identity and contact details shown across the Bfinz app and its notifications.">
        <button type="button" class="btn btn--primary"
                data-toast="Settings saved"
                data-toast-body="Settings persist once the module is connected to the database."
                data-toast-tone="success">
            <x-admin.icon name="save" /> Save settings
        </button>
    </x-admin.page-header>

    <div class="grid grid--2-1">

        <div class="stack">

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Application</h2>
                        <p class="card__desc">The name and logo used throughout the product.</p>
                    </div>
                </div>

                <div class="card__body">
                    <div class="field">
                        <label class="field__label" for="appName">App name <span class="field__req">*</span></label>
                        <input class="input" id="appName" value="Bfinz">
                    </div>

                    <div class="field">
                        <span class="field__label">Logo</span>
                        <div class="u-row" style="gap:14px">
                            <span style="background:#fff;border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px">
                                <img src="{{ asset('assets/img/bfinz-logo-sm.png') }}" alt="Bfinz logo" style="height:24px;width:auto">
                            </span>
                            <label class="upload u-grow" for="logoUpload">
                                <span class="upload__icon" aria-hidden="true"><x-admin.icon name="upload" /></span>
                                <span>
                                    <span class="upload__title">Replace logo</span>
                                    <span class="upload__hint">PNG with transparency, at least 480 px wide.</span>
                                </span>
                                <input type="file" id="logoUpload" class="visually-hidden">
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="appTagline">Tagline</label>
                        <input class="input" id="appTagline"
                               value="Smarter financial decisions — rates, loans and planning in one app">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Contact</h2>
                        <p class="card__desc">Shown on the app support screen and in transactional email.</p>
                    </div>
                </div>

                <div class="card__body">
                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="contactEmail">Contact email</label>
                            <input class="input" type="email" id="contactEmail" value="hello@bfinz.com">
                        </div>
                        <div class="field">
                            <label class="field__label" for="supportPhone">Support phone</label>
                            <input class="input" id="supportPhone" value="+91 44 4000 1200">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="website">Website</label>
                        <input class="input" id="website" value="https://bfinz.com">
                    </div>

                    <div class="field">
                        <label class="field__label" for="address">Registered address</label>
                        <textarea class="textarea" id="address">Bfinz Technologies Pvt Ltd, Anna Salai, Chennai 600002, Tamil Nadu, India</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Social Links</h2>
                        <p class="card__desc">Linked from the app About screen.</p>
                    </div>
                </div>

                <div class="card__body">
                    @foreach ([
                        ['LinkedIn', 'https://linkedin.com/company/bfinz'],
                        ['X (Twitter)', 'https://x.com/bfinz'],
                        ['Instagram', 'https://instagram.com/bfinz'],
                        ['YouTube', 'https://youtube.com/@bfinz'],
                    ] as $index => [$network, $url])
                        <div class="field">
                            <label class="field__label" for="social{{ $index }}">{{ $network }}</label>
                            <input class="input" id="social{{ $index }}" value="{{ $url }}">
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Mobile App Preview</h2>
                    <p class="card__desc">The About screen built from these values.</p>
                </div>
                <x-admin.icon name="smartphone" style="color:var(--text-3)" />
            </div>
            <div class="card__body">
                <x-admin.phone-preview
                    title="About Bfinz"
                    subtitle="Version 1.0"
                    :rows="[
                        ['Support', 'hello@bfinz.com'],
                        ['Phone', '+91 44 4000 1200'],
                        ['Website', 'bfinz.com'],
                        ['Location', 'Chennai, India'],
                    ]"
                    cta="Contact Support" />
            </div>
        </div>

    </div>

@endsection
