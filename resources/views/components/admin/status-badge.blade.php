@props(['status' => ''])

{{--
    Status pill. One mapping for the whole panel so the same word never appears
    in two different colours on two different screens.
--}}
@php
    $value = trim((string) $status);

    $tone = match (mb_strtolower($value)) {
        'active', 'published', 'connected', 'success', 'resolved', 'sent', 'completed', 'enabled'
            => 'success',
        'warning', 'pending', 'scheduled', 'open', 'in progress', 'review', 'reviewed'
            => 'warning',
        'offline', 'failed', 'error', 'rejected', 'expired', 'high'
            => 'danger',
        'draft', 'inactive', 'archived', 'disabled', 'low'
            => 'neutral',
        'medium'
            => 'info',
        default => 'navy',
    };
@endphp

<span class="badge badge--{{ $tone }}">{{ $value }}</span>
