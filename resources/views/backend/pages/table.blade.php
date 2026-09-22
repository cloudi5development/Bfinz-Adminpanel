{{--
    The standard list screen.

    Plain HTML for the page heading, then the one data-table component, which
    carries its own toolbar, table, pagination and modals. Nothing else is
    pulled in — the only Blade component on this page is <x-admin.data-table>.

    Column definitions and rows come from App\Support\MockTables, keyed by the
    current route, so each module shows its own records through the same table.
--}}
@extends('backend.template.layouts.template-base')

@php
    // A stable, CSS-safe id per module so the table and its modals never clash.
    $tableId = \Illuminate\Support\Str::camel(str_replace(['backend.', '.', '-'], ['', '_', '_'], $page['route']));
@endphp

@section('content')

    <div class="dt-page-head">
        <div>
            <h1>{{ $page['label'] }}</h1>
            <p>
                Manage {{ strtolower($page['label']) }} records for the Bfinz mobile app.
                @if ($table['total'])
                    {{ number_format($table['total']) }} records in this module.
                @endif
            </p>
        </div>

        <div class="dt-page-actions">
            <button type="button" class="dt-btn dt-btn--ghost" data-dt-toast="Filters coming with the API phase">
                <i class="fa-solid fa-filter"></i> Filters
            </button>

            @if ($table['primary'])
                <button type="button" class="dt-btn dt-btn--primary" data-dt-open="{{ $tableId }}FormModal">
                    <i class="fa-solid fa-plus"></i> {{ $table['primary'] }}
                </button>
            @endif
        </div>
    </div>

    <x-admin.data-table
        :id="$tableId"
        :columns="$table['columns']"
        :rows="$table['rows']"
        :filters="$table['filters']"
        :search="$table['search']"
        :primary="$table['primary']"
        :export="$table['export']"
        :total="$table['total']"
        :empty="$table['empty']" />

@endsection
