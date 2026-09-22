{{--
    The standard list screen.

    Driven entirely by the page entry in AdminMenu and the matching definition
    in MockTables, so every module in the panel gets the same toolbar, table,
    empty state and pagination without a bespoke view.
--}}
@extends('backend.template.layouts.template-base')

@php
    // A stable, CSS-safe id per module so the table and its modals never clash.
    $tableId = \Illuminate\Support\Str::camel(str_replace(['backend.', '.', '-'], ['', '_', '_'], $page['route']));
@endphp

@section('content')

    <x-admin.page-header
        :title="$page['label']"
        :description="'Manage ' . strtolower($page['label']) . ' records for the Bfinz mobile app. ' . ($table['total'] ? number_format($table['total']) . ' records in this module.' : '')">

        <button type="button" class="btn btn--ghost"
                data-toast="Filters"
                data-toast-body="Advanced filtering arrives with the API phase."
                data-toast-tone="info">
            <x-admin.icon name="filter" /> Filters
        </button>

        @if ($table['primary'])
            <button type="button" class="btn btn--primary" data-modal-open="{{ $tableId }}AddModal">
                <x-admin.icon name="plus" /> {{ $table['primary'] }}
            </button>
        @endif
    </x-admin.page-header>

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
