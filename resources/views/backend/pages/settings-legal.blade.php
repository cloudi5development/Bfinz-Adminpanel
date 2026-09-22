{{--
    Settings — legal documents.

    One view serves Privacy Policy, Terms & Conditions and Disclaimer; the page
    entry in AdminMenu decides which. The editor is a styled shell: the toolbar
    is presentational until a rich-text library is chosen, so no editor
    dependency is committed to at this stage.
--}}
@extends('backend.template.layouts.template-base')

@php
    $document = match ($page['name']) {
        'terms'      => [
            'label'   => 'Terms & Conditions',
            'updated' => '12 Sep 2026',
            'intro'   => 'The agreement every Bfinz user accepts when they create an account.',
            'body'    => [
                ['Acceptance of terms', 'By creating a Bfinz account you agree to these terms and to our Privacy Policy. If you do not agree, please discontinue use of the app.'],
                ['Nature of the service', 'Bfinz aggregates publicly available financial information — gold and silver rates, currency and fuel prices, deposit and loan rates, and banking reference data such as IFSC and MICR codes. Bfinz is not a bank, lender, broker or investment adviser.'],
                ['No financial advice', 'Calculator results, comparisons and goal projections are indicative and for information only. They are not a recommendation to buy, sell or hold any financial product.'],
                ['Accuracy of information', 'Rates are sourced from third parties and may lag the market. Always confirm the current rate with the relevant bank or provider before acting on it.'],
                ['Account security', 'You are responsible for activity on your account. Bfinz will never ask for your banking password, PIN, CVV or OTP.'],
            ],
        ],
        'disclaimer' => [
            'label'   => 'Disclaimer',
            'updated' => '12 Sep 2026',
            'intro'   => 'The limitation of liability shown in the app footer and on every calculator result.',
            'body'    => [
                ['Information only', 'All content in the Bfinz app is provided for general information. It does not constitute financial, investment, tax or legal advice, and it does not take account of your personal circumstances.'],
                ['Third-party data', 'Rates and reference data originate from third-party sources including IBJA, the RBI reference rate, oil marketing companies and published bank schedules. Bfinz does not warrant that this data is accurate, complete or current.'],
                ['Calculator results', 'Figures produced by the calculators are estimates based on the assumptions you enter. Actual EMIs, returns, maturity values and tax liabilities are determined by your lender, fund house or the tax authority.'],
                ['No liability', 'Bfinz accepts no liability for loss arising from reliance on information in the app. Verify any figure with the relevant institution before making a financial decision.'],
            ],
        ],
        default      => [
            'label'   => 'Privacy Policy',
            'updated' => '14 Sep 2026',
            'intro'   => 'How Bfinz collects, uses and protects personal information.',
            'body'    => [
                ['Information we collect', 'Account details you provide (name, email address and mobile number), the goals and calculations you save, and technical information such as device model, operating system version and app version.'],
                ['How we use it', 'To operate your account, save your goals and calculations across devices, send the notifications you have opted into, and understand which features are used so we can improve them.'],
                ['What we never collect', 'Bfinz does not ask for, store or transmit banking passwords, card numbers, PINs, CVVs or OTPs. No part of the app requires your net banking credentials.'],
                ['Sharing', 'We do not sell personal information. Data is shared only with processors who host and operate the service on our behalf, under contract and only as needed to run Bfinz.'],
                ['Your rights', 'You may request a copy of your data, correct it, or delete your account at any time from the app or by writing to privacy@bfinz.com.'],
                ['Retention', 'Account data is retained while your account is active and for a limited period afterwards to meet legal and accounting obligations.'],
            ],
        ],
    };
@endphp

@section('content')

    <x-admin.page-header :title="$document['label']" :description="$document['intro']">
        <button type="button" class="btn btn--ghost"
                data-toast="Preview opened"
                data-toast-body="The published view opens in the app once content is connected."
                data-toast-tone="info">
            <x-admin.icon name="eye" /> Preview
        </button>
        <button type="button" class="btn btn--primary" data-modal-open="publishLegalModal">
            <x-admin.icon name="check" /> Publish
        </button>
    </x-admin.page-header>

    <div class="grid grid--2-1">

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Document</h2>
                    <p class="card__desc">Last published {{ $document['updated'] }}.</p>
                </div>
                <x-admin.status-badge status="Published" />
            </div>

            <div class="card__body">
                <div class="field-row">
                    <div class="field">
                        <label class="field__label" for="docTitle">Title</label>
                        <input class="input" id="docTitle" value="{{ $document['label'] }}">
                    </div>
                    <div class="field">
                        <label class="field__label" for="docEffective">Effective date</label>
                        <input class="input" type="date" id="docEffective" value="2026-09-14">
                    </div>
                </div>

                <div class="field" style="margin-bottom:0">
                    <span class="field__label">Content</span>

                    <div class="editor">
                        <div class="editor__bar" role="toolbar" aria-label="Formatting">
                            <button type="button" class="editor__btn" title="Bold" aria-label="Bold"><x-admin.icon name="bold" /></button>
                            <button type="button" class="editor__btn" title="Italic" aria-label="Italic"><x-admin.icon name="italic" /></button>
                            <button type="button" class="editor__btn" title="Underline" aria-label="Underline"><x-admin.icon name="underline" /></button>
                            <span class="editor__sep" aria-hidden="true"></span>
                            <button type="button" class="editor__btn" title="Heading" aria-label="Heading"><x-admin.icon name="heading" /></button>
                            <button type="button" class="editor__btn" title="Bullet list" aria-label="Bullet list"><x-admin.icon name="list" /></button>
                            <button type="button" class="editor__btn" title="Insert link" aria-label="Insert link"><x-admin.icon name="link" /></button>
                        </div>

                        <div class="editor__area" contenteditable="true" role="textbox" aria-multiline="true"
                             aria-label="{{ $document['label'] }} content">
                            @foreach ($document['body'] as $section)
                                <h3>{{ $loop->iteration }}. {{ $section[0] }}</h3>
                                <p>{{ $section[1] }}</p>
                            @endforeach
                        </div>
                    </div>

                    <p class="field__hint">
                        The toolbar is presentational for now — a rich-text library will be chosen when
                        legal content moves into the database.
                    </p>
                </div>
            </div>

            <div class="card__foot">
                <span class="table-foot-note">Changes are not saved until you publish.</span>
                <div class="u-row u-row--sm">
                    <button type="button" class="btn btn--ghost btn--sm"
                            data-toast="Draft saved" data-toast-tone="info">
                        <x-admin.icon name="save" /> Save draft
                    </button>
                    <button type="button" class="btn btn--primary btn--sm" data-modal-open="publishLegalModal">
                        Publish
                    </button>
                </div>
            </div>
        </div>

        <div class="stack">

            <div class="card">
                <div class="card__head">
                    <h2 class="card__title">Version History</h2>
                </div>
                <div class="card__body">
                    <div class="timeline">
                        @foreach ([
                            ['v1.3', 'Published by Admin', $document['updated']],
                            ['v1.2', 'Published by Priya Menon', '02 Aug 2026'],
                            ['v1.1', 'Published by Admin', '19 Jun 2026'],
                            ['v1.0', 'First publication', '11 Apr 2026'],
                        ] as $version)
                            <div class="tl">
                                <span class="tl__icon" aria-hidden="true"><x-admin.icon name="file" /></span>
                                <div>
                                    <div class="tl__text"><b>{{ $version[0] }}</b></div>
                                    <div class="tl__meta">{{ $version[1] }}</div>
                                </div>
                                <span class="tl__time">{{ $version[2] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Mobile App Preview</h2>
                        <p class="card__desc">How the document reads in the app.</p>
                    </div>
                </div>
                <div class="card__body">
                    <x-admin.phone-preview
                        :title="$document['label']"
                        :subtitle="'Updated ' . $document['updated']"
                        note="Legal documents render as a scrollable screen in the app.">

                        @foreach (array_slice($document['body'], 0, 3) as $section)
                            <div class="phone__card">
                                <div class="phone__card-title">{{ $loop->iteration }}. {{ $section[0] }}</div>
                                <div class="phone__card-body">{{ Str::limit($section[1], 120) }}</div>
                            </div>
                        @endforeach

                    </x-admin.phone-preview>
                </div>
            </div>

        </div>

    </div>

    <x-admin.modal id="publishLegalModal" :title="'Publish ' . $document['label'] . '?'"
                   icon="alert" tone="warning"
                   description="The updated document becomes visible to every app user immediately.">
        <div class="kv">
            <div class="kv__row"><span class="kv__k">Document</span><span class="kv__v">{{ $document['label'] }}</span></div>
            <div class="kv__row"><span class="kv__k">Previous version</span><span class="kv__v">v1.3 &middot; {{ $document['updated'] }}</span></div>
            <div class="kv__row"><span class="kv__k">New version</span><span class="kv__v">v1.4</span></div>
        </div>

        <div class="flash flash--warning u-mt-3" style="margin-bottom:0">
            <x-admin.icon name="alert" />
            <div>Users may be asked to re-accept the document the next time they open the app.</div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn--ghost" data-modal-close>Cancel</button>
            <button type="button" class="btn btn--primary" data-modal-close
                    data-toast="Document published"
                    data-toast-body="Publishing becomes live once legal content is connected."
                    data-toast-tone="success">
                Publish now
            </button>
        </x-slot:footer>
    </x-admin.modal>

@endsection
