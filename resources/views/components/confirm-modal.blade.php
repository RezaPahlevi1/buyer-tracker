@props([
    'show' => false,
    'title' => 'Konfirmasi Hapus',
    'message' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'confirmAction' => null,
    'cancelAction' => null,
    'confirmLabel' => 'Hapus',
    'cancelLabel' => 'Batal',
])

@if ($show)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4" wire:click.self="{{ $cancelAction }}">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ $title }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ $message }}</p>

            <div class="flex justify-end gap-3">
                <button type="button" wire:click="{{ $cancelAction }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                    {{ $cancelLabel }}
                </button>
                <button type="button" wire:click="{{ $confirmAction }}" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50">
                    {{ $confirmLabel }}
                </button>
            </div>
        </div>
    </div>
@endif