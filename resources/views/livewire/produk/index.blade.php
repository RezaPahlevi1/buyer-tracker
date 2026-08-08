<?php

use App\Models\Product;
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

    public function delete(Product $product): void
    {
        $this->authorize('delete', $product);

        $product->delete();
    }

    public function with(): array
    {
        return [
            'products' => Product::query()
                ->when($this->search !== '', function ($query) {
                    $term = '%' . $this->search . '%';
                    $query->where(function ($q) use ($term) {
                        $q->where('nama_produk', 'like', $term)
                          ->orWhere('sku', 'like', $term)
                          ->orWhere('kategori', 'like', $term);
                    });
                })
                ->withCount('vendors')
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Produk</h1>

        @can('create', \App\Models\Product::class)
            <a href="{{ route('produk.create') }}" wire:navigate
               class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                + Tambah Produk
            </a>
        @endcan
    </div>

    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama produk, SKU, atau kategori..."
            class="w-full sm:w-96 rounded-md border-gray-300 shadow-sm"
        >
    </div>

    {{-- Mobile: card list --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($products as $product)
            <div wire:key="product-mobile-{{ $product->id }}" class="bg-white rounded-lg shadow p-4 text-sm">
                <div class="font-medium text-gray-800">{{ $product->nama_produk }}</div>
                <div class="text-gray-500 mt-0.5">{{ $product->sku ?? '-' }} &middot; {{ $product->kategori ?? '-' }}</div>
                <div class="text-gray-500 mt-1">{{ $product->vendors_count }} vendor terkait</div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('produk.show', $product) }}" wire:navigate class="text-gray-600 hover:underline">
                        Detail
                    </a>
                    @can('update', $product)
                        <a href="{{ route('produk.edit', $product) }}" wire:navigate class="text-blue-600 hover:underline">
                            Edit
                        </a>
                    @endcan
                    @can('delete', $product)
                        <button
                            wire:click="delete({{ $product->id }})"
                            wire:confirm="Yakin ingin menghapus produk ini?"
                            class="text-red-600 hover:underline"
                        >
                            Hapus
                        </button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-400 text-sm">
                Tidak ada data produk.
            </div>
        @endforelse
    </div>

    {{-- Desktop: table --}}
    <div class="hidden sm:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Nama Produk</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">SKU</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Kategori</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Vendor Terkait</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr wire:key="product-{{ $product->id }}">
                        <td class="px-4 py-3">{{ $product->nama_produk }}</td>
                        <td class="px-4 py-3">{{ $product->sku ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $product->kategori ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $product->vendors_count }} vendor</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('produk.show', $product) }}" wire:navigate class="text-gray-600 hover:underline">
                                Detail
                            </a>
                            @can('update', $product)
                                <a href="{{ route('produk.edit', $product) }}" wire:navigate class="text-blue-600 hover:underline">Edit</a>
                            @endcan
                            @can('delete', $product)
                                <button
                                    wire:click="delete({{ $product->id }})"
                                    wire:confirm="Yakin ingin menghapus produk ini?"
                                    class="text-red-600 hover:underline"
                                >
                                    Hapus
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada data produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>