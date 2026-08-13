<?php

use App\Models\Buyer;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public Buyer $buyer;

    public function mount(Buyer $buyer): void
    {
        $this->authorize('view', $buyer);

        $this->buyer = $buyer->load(['creator', 'updater']);
    }

    public bool $confirmingDelete = false;

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->buyer);

        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function deleteConfirmed(): void
    {
        $this->authorize('delete', $this->buyer);

        $this->buyer->delete();

        session()->flash('status', 'Data pelanggan berhasil dihapus.');

        $this->redirect(route('pelanggan.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'purchases' => $this->buyer->purchases()
                ->with('product:id,nama_produk,sku')
                ->latest('tanggal_beli')
                ->paginate(10),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('pelanggan.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">
                &larr; Kembali ke daftar
            </a>
            <h1 class="text-xl font-semibold text-gray-800 mt-1">{{ $buyer->nama }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $buyer)
                <a href="{{ route('pelanggan.edit', $buyer) }}" wire:navigate
                   class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700 text-center">
                    Edit
                </a>
            @endcan
            @can('delete', $buyer)
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
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Data Pelanggan</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">No. HP</dt>
                <dd class="text-gray-800 mt-0.5">{{ $buyer->no_hp }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email</dt>
                <dd class="text-gray-800 mt-0.5">{{ $buyer->email ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Alamat</dt>
                <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ $buyer->alamat ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Catatan</dt>
                <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ $buyer->catatan ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dibuat oleh</dt>
                <dd class="text-gray-800 mt-0.5">
                    {{ $buyer->creator->name ?? '-' }}
                    <span class="text-gray-400">({{ $buyer->created_at->format('d M Y, H:i') }})</span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Terakhir diubah</dt>
                <dd class="text-gray-800 mt-0.5">
                    {{ $buyer->updater->name ?? '-' }}
                    <span class="text-gray-400">({{ $buyer->updated_at->format('d M Y, H:i') }})</span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">
            Riwayat Transaksi
            <span class="text-gray-400 font-normal">({{ $purchases->total() }} total)</span>
        </h2>

        @if ($purchases->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Belum ada riwayat transaksi.</p>
        @else
            <div class="space-y-3 sm:hidden">
                @foreach ($purchases as $purchase)
                    <div wire:key="purchase-mobile-{{ $purchase->id }}" class="border border-gray-100 rounded-md p-3 text-sm">
                        <div class="flex justify-between items-start gap-2">
                            <span class="font-medium text-gray-800">{{ $purchase->product?->nama_produk ?? 'Produk telah dihapus' }}</span>
                            <span class="text-gray-500 whitespace-nowrap">{{ $purchase->tanggal_beli->format('d M Y') }}</span>
                        </div>
                        <div class="mt-1 text-gray-500">
                            {{ $purchase->jumlah }} unit
                            @if ($purchase->harga_saat_beli)
                                &middot; Rp {{ number_format($purchase->harga_saat_beli, 0, ',', '.') }}
                            @endif
                        </div>
                        @if ($purchase->catatan)
                            <div class="mt-1 text-gray-400 text-xs">{{ $purchase->catatan }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Tanggal</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Produk</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Jumlah</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Harga Saat Beli</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($purchases as $purchase)
                            <tr wire:key="purchase-{{ $purchase->id }}">
                                <td class="px-4 py-2">{{ $purchase->tanggal_beli->format('d M Y') }}</td>
                                <td class="px-4 py-2">{{ $purchase->product?->nama_produk ?? 'Produk telah dihapus' }}</td>
                                <td class="px-4 py-2 text-right">{{ $purchase->jumlah }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ $purchase->harga_saat_beli ? 'Rp ' . number_format($purchase->harga_saat_beli, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-2">{{ $purchase->catatan ?? '-' }}</td>
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
        title="Hapus Pelanggan"
        :message="'Yakin ingin menghapus pelanggan ' . $buyer->nama . '? Riwayat transaksinya juga akan ikut terhapus.'"
        confirmAction="deleteConfirmed"
        cancelAction="cancelDelete"
    />
</div>