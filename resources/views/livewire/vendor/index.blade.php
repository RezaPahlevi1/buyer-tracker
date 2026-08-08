<?php

use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Vendor $vendor): void
    {
        $this->authorize('delete', $vendor);

        $vendor->delete();
    }

    public function with(): array
    {
        return [
            'vendors' => Vendor::query()
                ->when($this->search !== '', function ($query) {
                    $term = '%' . $this->search . '%';
                    $query->where(function ($q) use ($term) {
                        $q->where('nama_vendor', 'like', $term)
                          ->orWhere('nama_pic', 'like', $term);
                    });
                })
                ->withCount('products')
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Vendor</h1>

        @can('create', \App\Models\Vendor::class)
            <a href="{{ route('vendor.create') }}" wire:navigate
               class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                + Tambah Vendor
            </a>
        @endcan
    </div>

    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama vendor atau PIC..."
            class="w-full sm:w-96 rounded-md border-gray-300 shadow-sm"
        >
    </div>

    {{-- Mobile: card list --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($vendors as $vendor)
            <div wire:key="vendor-mobile-{{ $vendor->id }}" class="bg-white rounded-lg shadow p-4 text-sm">
                <div class="font-medium text-gray-800">{{ $vendor->nama_vendor }}</div>
                <div class="text-gray-500 mt-0.5">{{ $vendor->nama_pic ?? '-' }} &middot; {{ $vendor->no_hp }}</div>
                <div class="text-gray-500 mt-1">{{ $vendor->products_count }} produk terkait</div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('vendor.show', $vendor) }}" wire:navigate class="text-gray-600 hover:underline">
                        Detail
                    </a>
                    @can('update', $vendor)
                        <a href="{{ route('vendor.edit', $vendor) }}" wire:navigate class="text-blue-600 hover:underline">
                            Edit
                        </a>
                    @endcan
                    @can('delete', $vendor)
                        <button
                            wire:click="delete({{ $vendor->id }})"
                            wire:confirm="Yakin ingin menghapus vendor ini?"
                            class="text-red-600 hover:underline"
                        >
                            Hapus
                        </button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-400 text-sm">
                Tidak ada data vendor.
            </div>
        @endforelse
    </div>

    {{-- Desktop: table --}}
    <div class="hidden sm:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Nama Vendor</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">PIC</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">No. HP</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Produk Terkait</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($vendors as $vendor)
                    <tr wire:key="vendor-{{ $vendor->id }}">
                        <td class="px-4 py-3">{{ $vendor->nama_vendor }}</td>
                        <td class="px-4 py-3">{{ $vendor->nama_pic ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $vendor->no_hp }}</td>
                        <td class="px-4 py-3">{{ $vendor->products_count }} produk</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('vendor.show', $vendor) }}" wire:navigate class="text-gray-600 hover:underline">
                                Detail
                            </a>
                            @can('update', $vendor)
                                <a href="{{ route('vendor.edit', $vendor) }}" wire:navigate class="text-blue-600 hover:underline">Edit</a>
                            @endcan
                            @can('delete', $vendor)
                                <button
                                    wire:click="delete({{ $vendor->id }})"
                                    wire:confirm="Yakin ingin menghapus vendor ini?"
                                    class="text-red-600 hover:underline"
                                >
                                    Hapus
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada data vendor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vendors->links() }}
    </div>
</div>