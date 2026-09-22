@props(['label' => '', 'description' => null, 'checked' => false, 'name' => null])

{{-- Labelled on/off switch used across Settings. --}}
<div class="u-between" style="padding:13px 0;border-bottom:1px solid var(--border-soft)">
    <div class="u-grow">
        <div class="u-strong" style="font-size:13.5px">{{ $label }}</div>
        @if ($description)
            <div class="u-xs u-muted u-mt-1">{{ $description }}</div>
        @endif
    </div>

    <label class="toggle">
        <span class="visually-hidden">{{ $label }}</span>
        <input type="checkbox" @checked($checked) @if ($name) name="{{ $name }}" @endif>
        <span class="toggle__track" aria-hidden="true"></span>
    </label>
</div>
