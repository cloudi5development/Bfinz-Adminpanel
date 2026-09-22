{{--
    Settings — App.

    Release and availability controls for the mobile client: version gating,
    maintenance mode and the support channels shown inside the app.
--}}
@extends('backend.template.layouts.template-base')

@section('content')

    <x-admin.page-header
        title="App Settings"
        description="Release, availability and support configuration for the Bfinz mobile app.">
        <button type="button" class="btn btn--primary"
                data-toast="App settings saved"
                data-toast-body="Settings persist once the module is connected to the database."
                data-toast-tone="success">
            <x-admin.icon name="save" /> Save settings
        </button>
    </x-admin.page-header>

    <div class="grid grid--4 u-mb-3">
        <x-admin.stat-card label="Current version" value="1.0.0" icon="smartphone" tone="primary" />
        <x-admin.stat-card label="Minimum supported" value="0.9.4" icon="lock" tone="royal" />
        <x-admin.stat-card label="Maintenance mode" value="Off" icon="check" tone="success" />
        <x-admin.stat-card label="Devices on latest" value="82.4%" change="+6.1%" direction="up" caption="this month" icon="activity" tone="warning" />
    </div>

    <div class="grid grid--2">

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Release</h2>
                    <p class="card__desc">Version gating for the published app.</p>
                </div>
            </div>

            <div class="card__body">
                <div class="field-row">
                    <div class="field">
                        <label class="field__label" for="appVersion">App version</label>
                        <input class="input" id="appVersion" value="1.0.0">
                    </div>
                    <div class="field">
                        <label class="field__label" for="minVersion">Minimum supported version</label>
                        <input class="input" id="minVersion" value="0.9.4">
                        <p class="field__hint">Older builds are asked to update before continuing.</p>
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="updateMessage">Update prompt message</label>
                    <textarea class="textarea" id="updateMessage">A newer version of Bfinz is available with faster rate updates and new calculators.</textarea>
                </div>

                <x-admin.toggle label="Force update below minimum version"
                                description="Blocks the app until the user updates, rather than only prompting."
                                :checked="true" />
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Availability</h2>
                    <p class="card__desc">Take the app offline for maintenance.</p>
                </div>
            </div>

            <div class="card__body">
                <x-admin.toggle label="Maintenance mode"
                                description="Shows a maintenance screen instead of the app home screen."
                                :checked="false" />

                <div class="field u-mt-3">
                    <label class="field__label" for="maintenanceMessage">Maintenance message</label>
                    <textarea class="textarea" id="maintenanceMessage">Bfinz is briefly offline for scheduled maintenance. Rates and calculators will be back shortly.</textarea>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label class="field__label" for="windowStart">Window start</label>
                        <input class="input" type="datetime-local" id="windowStart" value="2026-10-05T01:00">
                    </div>
                    <div class="field">
                        <label class="field__label" for="windowEnd">Window end</label>
                        <input class="input" type="datetime-local" id="windowEnd" value="2026-10-05T03:00">
                    </div>
                </div>

                <div class="flash flash--warning" style="margin-bottom:0">
                    <x-admin.icon name="alert" />
                    <div>Maintenance mode affects every user immediately once the module is connected.</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Support</h2>
                    <p class="card__desc">Channels offered on the app help screen.</p>
                </div>
            </div>

            <div class="card__body">
                <div class="field">
                    <label class="field__label" for="supportEmail">Support email</label>
                    <input class="input" type="email" id="supportEmail" value="support@bfinz.com">
                </div>
                <div class="field">
                    <label class="field__label" for="supportLine">Support phone</label>
                    <input class="input" id="supportLine" value="+91 44 4000 1200">
                </div>
                <div class="field">
                    <label class="field__label" for="supportHours">Support hours</label>
                    <input class="input" id="supportHours" value="Mon–Sat, 09:00–18:00 IST">
                </div>

                <x-admin.toggle label="Show in-app chat"
                                description="Adds a chat entry point to the help screen."
                                :checked="false" />
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Data Refresh</h2>
                    <p class="card__desc">How often the app pulls fresh data.</p>
                </div>
            </div>

            <div class="card__body">
                <div class="field">
                    <label class="field__label" for="rateRefresh">Rate refresh interval</label>
                    <select class="select" id="rateRefresh">
                        <option>Every 30 minutes</option>
                        <option selected>Every hour</option>
                        <option>Twice daily</option>
                        <option>Once daily</option>
                    </select>
                </div>
                <div class="field">
                    <label class="field__label" for="cacheWindow">Offline cache window</label>
                    <select class="select" id="cacheWindow">
                        <option>6 hours</option>
                        <option selected>24 hours</option>
                        <option>3 days</option>
                    </select>
                    <p class="field__hint">How long cached rates stay readable without a connection.</p>
                </div>

                <x-admin.toggle label="Show last-updated timestamps"
                                description="Displays when each rate was last refreshed, next to the value."
                                :checked="true" />
            </div>
        </div>

    </div>

@endsection
