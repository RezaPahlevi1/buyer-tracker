<?php

use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Vendor $vendor;

    public function mount(Vendor $vendor): void
    {
        $this->authorize('view', $vendor);

        $this->vendor = $vendor->load(['creator', 'updater']);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->vendor);

        $this->vendor->delete();

        session()->flash('status', 'Data vendor berhasil dihapus.');

        $this->redirect(route('vendor.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'products' => $this->vendor->products()->orderBy('nama_produk')->get(),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('vendor.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">
                &larr; Kembali ke daftar
            </a>
            <h1 class="text-xl font-semibold text-gray-800 mt-1">{{ $vendor->nama_vendor }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $vendor)
                <a href="{{ route('vendor.edit', $vendor) }}" wire:navigate
                   class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700 text-center">
                    Edit
                </a>
            @endcan
            @can('delete', $vendor)
                <button
                    wire:click="delete"
                    wire:confirm="Yakin ingin menghapus vendor ini?"
                    class="px-4 py-2 border border-red-300 text-red-600 text-sm rounded-md hover:bg-red-50"
                >
                    Hapus
                </button>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Data Vendor</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Nama PIC</dt>
                <dd class="text-gray-800 mt-0.5">{{ $vendor->nama_pic ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">No. HP</dt>
                <dd class="text-gray-800 mt-0.5">{{ $vendor->no_hp }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email</dt>
                <dd class="text-gray-800 mt-0.5">{{ $vendor->email ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Alamat</dt>
                <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ $vendor->alamat ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Catatan</dt>
                <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ $vendor->catatan ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dibuat oleh</dt>
                <dd class="text-gray-800 mt-0.5">
                    {{ $vendor->creator->name ?? '-' }}
                    <span class="text-gray-400">({{ $vendor->created_at->format('d M Y, H:i') }})</span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Terakhir diubah</dt>
                <dd class="text-gray-800 mt-0.5">
                    {{ $vendor->updater->name ?? '-' }}
                    <span class="text-gray-400">({{ $vendor->updated_at->format('d M Y, H:i') }})</span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">
            Produk Terkait
            <span class="text-gray-400 font-normal">({{ $products->count() }})</span>
        </h2>

        @if ($products->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Belum ada produk yang dikaitkan.</p>
        @else
            <div class="space-y-3 sm:hidden">
                @foreach ($products as $product)
                    <div wire:key="product-mobile-{{ $product->id }}" class="border border-gray-100 rounded-md p-3 text-sm">
                        <div class="flex justify-between items-start gap-2">
                            <a href="{{ route('produk.show', $product) }}" wire:navigate
                               class="font-medium text-gray-800 hover:text-blue-600 hover:underline">
                                {{ $product->nama_produk }}
                            </a>
                            @if ($product->pivot->is_active)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700 whitespace-nowrap">Aktif</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 whitespace-nowrap">Nonaktif</span>
                            @endif
                        </div>
                        <div class="mt-1 text-gray-500">
                            {{ $product->sku ?? '-' }}
                            @if ($product->pivot->harga_dari_vendor)
                                &middot; Rp {{ number_format($product->pivot->harga_dari_vendor, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Produk</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">SKU</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Harga dari Vendor</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($products as $product)
                            <tr wire:key="product-{{ $product->id }}">
                                <td class="px-4 py-2">
                                    <a href="{{ route('produk.show', $product) }}" wire:navigate class="text-gray-800 hover:text-blue-600 hover:underline">
                                        {{ $product->nama_produk }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">{{ $product->sku ?? '-' }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ $product->pivot->harga_dari_vendor ? 'Rp ' . number_format($product->pivot->harga_dari_vendor, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    @if ($product->pivot->is_active)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">Aktif</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>