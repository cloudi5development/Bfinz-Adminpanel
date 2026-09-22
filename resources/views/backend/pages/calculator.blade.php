{{--
    Financial tool configuration.

    A calculator is managed, not computed, here: the admin controls whether it
    is available in the app and the bounds its inputs accept. The maths stays in
    the mobile client, so no calculation logic lives in this panel.
--}}
@extends('backend.template.layouts.template-base')

@section('content')

    <x-admin.page-header :title="$tool['name']" :description="$tool['desc']">
        <button type="button" class="btn btn--ghost" data-modal-open="toolResetModal">
            <x-admin.icon name="refresh" /> Reset defaults
        </button>
        <button type="button" class="btn btn--primary"
                data-toast="Configuration saved"
                data-toast-body="Settings will persist once this module is connected to the API."
                data-toast-tone="success">
            <x-admin.icon name="save" /> Save changes
        </button>
    </x-admin.page-header>

    <div class="grid grid--4 u-mb-3">
        <x-admin.stat-card label="Usage this month" :value="$tool['uses']" icon="activity" tone="primary" />
        <x-admin.stat-card label="Status" :value="$tool['status']" icon="check" tone="success" />
        <x-admin.stat-card label="Input fields" :value="count($tool['fields'])" icon="sliders" tone="royal" />
        <x-admin.stat-card label="Last updated" :value="$tool['updated']" icon="clock" tone="warning" />
    </div>

    <div class="grid grid--2-1">

        <div class="stack">

            {{-- Tool details -------------------------------------------- --}}
            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Tool Details</h2>
                        <p class="card__desc">How this calculator is presented in the app.</p>
                    </div>
                    <x-admin.status-badge :status="$tool['status']" />
                </div>

                <div class="card__body">
                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="toolName">Calculator name <span class="field__req">*</span></label>
                            <input class="input" id="toolName" value="{{ $tool['name'] }}">
                        </div>
                        <div class="field">
                            <label class="field__label" for="toolIcon">Icon</label>
                            <select class="select" id="toolIcon">
                                <option>{{ ucfirst($tool['icon']) }}</option>
                                <option>Calculator</option>
                                <option>Chart</option>
                                <option>Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="toolDesc">Description</label>
                        <textarea class="textarea" id="toolDesc">{{ $tool['desc'] }}</textarea>
                        <p class="field__hint">Shown under the calculator title on the tool screen.</p>
                    </div>

                    <x-admin.toggle
                        label="Available in the app"
                        description="Turning this off hides the calculator without deleting its configuration."
                        :checked="true" />

                    <x-admin.toggle
                        label="Show on the home screen"
                        description="Featured tools appear in the shortcut row on the app home screen."
                        :checked="true" />
                </div>
            </div>

            {{-- Input configuration -------------------------------------- --}}
            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Input Configuration</h2>
                        <p class="card__desc">Bounds the app enforces on each field before it calculates.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th scope="col">Field</th>
                                <th scope="col">Minimum Value</th>
                                <th scope="col">Maximum Value</th>
                                <th scope="col">Default Value</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tool['fields'] as $field)
                                <tr>
                                    <td class="is-strong">{{ $field[0] }}</td>
                                    <td><input class="input" value="{{ $field[1] }}" aria-label="{{ $field[0] }} minimum"></td>
                                    <td><input class="input" value="{{ $field[2] }}" aria-label="{{ $field[0] }} maximum"></td>
                                    <td><input class="input" value="{{ $field[3] }}" aria-label="{{ $field[0] }} default"></td>
                                    <td><x-admin.status-badge status="Active" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- App preview ---------------------------------------------------- --}}
        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Mobile App Preview</h2>
                    <p class="card__desc">How a result looks to the user.</p>
                </div>
                <x-admin.icon name="smartphone" style="color:var(--text-3)" />
            </div>
            <div class="card__body">
                <x-admin.phone-preview
                    :title="$tool['name']"
                    subtitle="Bfinz Financial Tools"
                    :heroLabel="$tool['preview']['primary_label']"
                    :heroValue="$tool['preview']['primary']"
                    :rows="$tool['preview']['rows']"
                    cta="Recalculate" />
            </div>
        </div>

    </div>

    <x-admin.confirm-dialog
        id="toolResetModal"
        title="Reset to default configuration?"
        body="This restores the original minimum, maximum and default values for every field on this calculator."
        confirm="Reset defaults" />

@endsection
