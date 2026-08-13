<?php

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public Product $product;

    public function mount(Product $product): void
    {
        $this->authorize('view', $product);

        $this->product = $product->load(['creator', 'updater']);
    }

    public bool $confirmingDelete = false;

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->product);

        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function deleteConfirmed(): void
    {
        $this->authorize('delete', $this->product);

        $this->product->delete();

        session()->flash('status', 'Data produk berhasil dihapus.');

        $this->redirect(route('produk.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'vendors' => $this->product->vendors()->orderBy('nama_vendor')->get(),
            'purchases' => $this->product->purchases()
                ->with('buyer:id,nama,no_hp')
                ->latest('tanggal_beli')
                ->paginate(10),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('produk.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">
                &larr; Kembali ke daftar
            </a>
            <h1 class="text-xl font-semibold text-gray-800 mt-1">{{ $product->nama_produk }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $product)
                <a href="{{ route('produk.edit', $product) }}" wire:navigate
                   class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700 text-center">
                    Edit
                </a>
            @endcan
            @can('delete', $product)
                <button
                    wire:click="confirmDelete"
                    class="px-4 py-2 border border-red-300 text-red-600 text-sm rounded-md hover:bg-red-50"
                >
                    Hapus
                </button>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Data Produk</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">SKU</dt>
                <dd class="text-gray-800 mt-0.5">{{ $product->sku ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Kategori</dt>
                <dd class="text-gray-800 mt-0.5">{{ $product->kategori ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Deskripsi</dt>
                <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ $product->deskripsi ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dibuat oleh</dt>
                <dd class="text-gray-800 mt-0.5">
                    {{ $product->creator->name ?? '-' }}
                    <span class="text-gray-400">({{ $product->created_at->format('d M Y, H:i') }})</span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Terakhir diubah</dt>
                <dd class="text-gray-800 mt-0.5">
                    {{ $product->updater->name ?? '-' }}
                    <span class="text-gray-400">({{ $product->updated_at->format('d M Y, H:i') }})</span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">
            Vendor Terkait
            <span class="text-gray-400 font-normal">({{ $vendors->count() }})</span>
        </h2>

        @if ($vendors->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Belum ada vendor yang dikaitkan.</p>
        @else
            <div class="space-y-3 sm:hidden">
                @foreach ($vendors as $vendor)
                    <div wire:key="vendor-mobile-{{ $vendor->id }}" class="border border-gray-100 rounded-md p-3 text-sm">
                        <div class="flex justify-between items-start gap-2">
                            <a href="{{ route('vendor.show', $vendor) }}" wire:navigate
                               class="font-medium text-gray-800 hover:text-blue-600 hover:underline">
                                {{ $vendor->nama_vendor }}
                            </a>
                            @if ($vendor->pivot->is_active)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700 whitespace-nowrap">Aktif</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 whitespace-nowrap">Nonaktif</span>
                            @endif
                        </div>
                        <div class="mt-1 text-gray-500">
                            {{ $vendor->nama_pic ?? '-' }}
                            @if ($vendor->pivot->harga_dari_vendor)
                                &middot; Rp {{ number_format($vendor->pivot->harga_dari_vendor, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Vendor</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">PIC</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Harga dari Vendor</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($vendors as $vendor)
                            <tr wire:key="vendor-{{ $vendor->id }}">
                                <td class="px-4 py-2">
                                    <a href="{{ route('vendor.show', $vendor) }}" wire:navigate class="text-gray-800 hover:text-blue-600 hover:underline">
                                        {{ $vendor->nama_vendor }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">{{ $vendor->nama_pic ?? '-' }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ $vendor->pivot->harga_dari_vendor ? 'Rp ' . number_format($vendor->pivot->harga_dari_vendor, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    @if ($vendor->pivot->is_active)
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

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">
            Riwayat Pembelian oleh Pelanggan
            <span class="text-gray-400 font-normal">({{ $purchases->total() }} total)</span>
        </h2>

        @if ($purchases->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Belum ada pelanggan yang membeli produk ini.</p>
        @else
            <div class="space-y-3 sm:hidden">
                @foreach ($purchases as $purchase)
                    <div wire:key="purchase-mobile-{{ $purchase->id }}" class="border border-gray-100 rounded-md p-3 text-sm">
                        <div class="flex justify-between items-start gap-2">
                            <a href="{{ route('pelanggan.show', $purchase->buyer) }}" wire:navigate
                               class="font-medium text-gray-800 hover:text-blue-600 hover:underline">
                                {{ $purchase->buyer->nama }}
                            </a>
                            <span class="text-gray-500 whitespace-nowrap">{{ $purchase->tanggal_beli->format('d M Y') }}</span>
                        </div>
                        <div class="mt-1 text-gray-500">
                            {{ $purchase->jumlah }} unit
                            @if ($purchase->harga_saat_beli)
                                &middot; Rp {{ number_format($purchase->harga_saat_beli, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Tanggal</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Pelanggan</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Jumlah</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Harga Saat Beli</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($purchases as $purchase)
                            <tr wire:key="purchase-{{ $purchase->id }}">
                                <td class="px-4 py-2">{{ $purchase->tanggal_beli->format('d M Y') }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('pelanggan.show', $purchase->buyer) }}" wire:navigate class="text-gray-800 hover:text-blue-600 hover:underline">
                                        {{ $purchase->buyer->nama }}
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-right">{{ $purchase->jumlah }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ $purchase->harga_saat_beli ? 'Rp ' . number_format($purchase->harga_saat_beli, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
    <x-confirm-modal
            :show="$confirmingDelete"
            title="Hapus Produk"
            :message="'Yakin ingin menghapus produk ' . $product->nama_produk . '? Tindakan ini tidak dapat dibatalkan.'"
            confirmAction="deleteConfirmed"
            cancelAction="cancelDelete"
        />
</div>