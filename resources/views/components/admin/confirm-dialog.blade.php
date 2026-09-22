@props([
    'id' => 'confirmDialog',
    'title' => 'Delete this record?',
    'body' => 'Are you sure you want to delete this record? This action cannot be undone.',
    'confirm' => 'Delete',
])

{{-- Destructive confirmation. Deliberately explicit: the danger icon, the
     consequence spelled out, and Cancel sitting before the red action. --}}
<x-admin.modal :id="$id" :title="$title" icon="alert" tone="danger">
    <p style="font-size:13.5px;color:#334155;line-height:1.65">{{ $body }}</p>

    <div class="flash flash--danger u-mt-3" style="margin-bottom:0">
        <x-admin.icon name="alert" />
        <div>Records removed here also disappear from the Bfinz mobile app.</div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn--ghost" data-modal-close>Cancel</button>
        <button type="button" class="btn btn--danger" data-modal-close
                data-toast="Record deleted"
                data-toast-body="Deletion is simulated while the module runs on static data."
                data-toast-tone="danger">
            <x-admin.icon name="trash" /> {{ $confirm }}
        </button>
    </x-slot:footer>
</x-admin.modal>
