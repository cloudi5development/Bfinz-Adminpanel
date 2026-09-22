{{--
    Goal template configuration.

    Goals are templates the app offers users, so this screen edits the template
    — its copy, recommended parameters and tips — rather than any individual
    plan created from it.
--}}
@extends('backend.template.layouts.template-base')

@section('content')

    <x-admin.page-header :title="$goal['name']" :description="$goal['desc']">
        <button type="button" class="btn btn--ghost" data-modal-open="goalArchiveModal">
            <x-admin.icon name="trash" /> Archive template
        </button>
        <button type="button" class="btn btn--primary"
                data-toast="Goal template saved"
                data-toast-body="Changes will persist once this module is connected to the API."
                data-toast-tone="success">
            <x-admin.icon name="save" /> Save changes
        </button>
    </x-admin.page-header>

    <div class="grid grid--4 u-mb-3">
        <x-admin.stat-card label="Active plans" :value="$goal['plans']" icon="target" tone="primary" />
        <x-admin.stat-card label="Typical target" :value="$goal['target']" icon="chart" tone="royal" />
        <x-admin.stat-card label="Suggested monthly" :value="$goal['monthly']" icon="calculator" tone="success" />
        <x-admin.stat-card label="Recommended duration" :value="$goal['duration']" icon="clock" tone="warning" />
    </div>

    <div class="grid grid--2-1">

        <div class="stack">

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Goal Details</h2>
                        <p class="card__desc">Copy and imagery shown when a user starts this goal.</p>
                    </div>
                    <x-admin.status-badge :status="$goal['status']" />
                </div>

                <div class="card__body">
                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="goalName">Goal name <span class="field__req">*</span></label>
                            <input class="input" id="goalName" value="{{ $goal['name'] }}">
                        </div>
                        <div class="field">
                            <label class="field__label" for="goalDuration">Recommended duration</label>
                            <input class="input" id="goalDuration" value="{{ $goal['duration'] }}">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="goalDesc">Description</label>
                        <textarea class="textarea" id="goalDesc">{{ $goal['desc'] }}</textarea>
                    </div>

                    <div class="field">
                        <span class="field__label">Goal image</span>
                        <label class="upload" for="goalImage">
                            <span class="upload__icon" aria-hidden="true"><x-admin.icon name="image" /></span>
                            <span>
                                <span class="upload__title">Upload goal artwork</span>
                                <span class="upload__hint">PNG or JPG, 1080 &times; 720 recommended. Max 2 MB.</span>
                            </span>
                            <input type="file" id="goalImage" class="visually-hidden">
                        </label>
                    </div>

                    <x-admin.toggle label="Available in the app"
                                    description="Hides this template from the goal picker when switched off."
                                    :checked="true" />
                </div>
            </div>

            <div class="card">
                <div class="card__head">
                    <h2 class="card__title">Recommended Parameters</h2>
                </div>
                <div class="card__body">
                    <div class="kv">
                        @foreach ($goal['params'] as $param)
                            <div class="kv__row">
                                <span class="kv__k">{{ $param[0] }}</span>
                                <span class="kv__v">{{ $param[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Planning Tips</h2>
                        <p class="card__desc">Shown as guidance cards inside the goal screen.</p>
                    </div>
                    <button type="button" class="btn btn--ghost btn--sm" data-modal-open="goalTipModal">
                        <x-admin.icon name="plus" /> Add tip
                    </button>
                </div>
                <div class="card__body">
                    <div class="timeline">
                        @foreach ($goal['tips'] as $tip)
                            <div class="tl">
                                <span class="tl__icon" aria-hidden="true"><x-admin.icon name="info" /></span>
                                <div><div class="tl__text">{{ $tip }}</div></div>
                                <span class="tl__time">
                                    <button type="button" class="row-btn" aria-label="Edit this tip">
                                        <x-admin.icon name="edit" />
                                    </button>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Mobile App Preview</h2>
                    <p class="card__desc">How this goal appears to a user.</p>
                </div>
                <x-admin.icon name="smartphone" style="color:var(--text-3)" />
            </div>
            <div class="card__body">
                <x-admin.phone-preview
                    :title="$goal['name']"
                    subtitle="Bfinz Goal Planner"
                    heroLabel="Target amount"
                    :heroValue="$goal['target']"
                    :rows="[
                        ['Suggested monthly', $goal['monthly']],
                        ['Duration', $goal['duration']],
                        ['Active plans', $goal['plans']],
                    ]"
                    cta="Start this goal" />
            </div>
        </div>

    </div>

    <x-admin.modal id="goalTipModal" title="Add a planning tip"
                   description="Short, practical guidance shown inside the goal screen.">
        <div class="field">
            <label class="field__label" for="tipText">Tip</label>
            <textarea class="textarea" id="tipText"
                      placeholder="Keep the down payment in debt funds within three years of the purchase."></textarea>
        </div>
        <x-slot:footer>
            <button type="button" class="btn btn--ghost" data-modal-close>Cancel</button>
            <button type="button" class="btn btn--primary" data-modal-close
                    data-toast="Tip added" data-toast-tone="success">Add tip</button>
        </x-slot:footer>
    </x-admin.modal>

    <x-admin.confirm-dialog
        id="goalArchiveModal"
        title="Archive this goal template?"
        body="Users with an existing plan keep it, but the template stops appearing in the goal picker for new users."
        confirm="Archive template" />

@endsection
