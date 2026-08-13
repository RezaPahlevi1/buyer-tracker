<?php

use App\Models\Buyer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $confirmingDeleteId = null;
    public string $confirmingDeleteLabel = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $buyer = Buyer::findOrFail($id);
        $this->authorize('delete', $buyer);

        $this->confirmingDeleteId = $id;
        $this->confirmingDeleteLabel = $buyer->nama;
    }

    public function cancelDelete(): void
    {
        $this->reset(['confirmingDeleteId', 'confirmingDeleteLabel']);
    }

    public function deleteConfirmed(): void
    {
        $buyer = Buyer::findOrFail($this->confirmingDeleteId);
        $this->authorize('delete', $buyer);

        $buyer->delete();

        $this->cancelDelete();
    }

    public function with(): array
    {
        return [
            'buyers' => Buyer::query()
                ->when($this->search !== '', function ($query) {
                    $term = '%' . $this->search . '%';
                    $query->where(function ($q) use ($term) {
                        $q->where('nama', 'like', $term)
                          ->orWhereHas('purchases.product', function ($q) use ($term) {
                              $q->where('nama_produk', 'like', $term);
                          });
                    });
                })
                ->withCount('purchases')
                ->with('latestPurchase.product')
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Pelanggan</h1>

        @can('create', \App\Models\Buyer::class)
            <a href="{{ route('pelanggan.create') }}" wire:navigate
               class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                + Tambah Pelanggan
            </a>
        @endcan
    </div>

    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama pelanggan atau produk..."
            class="w-full sm:w-96 rounded-md border-gray-300 shadow-sm"
        >
    </div>

    {{-- Mobile: card list, tidak perlu scroll horizontal --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($buyers as $buyer)
            <div wire:key="buyer-mobile-{{ $buyer->id }}" class="bg-white rounded-lg shadow p-4 text-sm">
                <div class="font-medium text-gray-800">{{ $buyer->nama }}</div>
                <div class="text-gray-500 mt-0.5">{{ $buyer->no_hp }}</div>
                <div class="text-gray-500 mt-1">
                    @if ($buyer->latestPurchase)
                        {{ $buyer->latestPurchase->product?->nama_produk ?? 'Produk telah dihapus' }}
                        <span class="text-gray-400">({{ $buyer->purchases_count }} transaksi)</span>
                    @else
                        <span class="text-gray-400">Belum ada transaksi</span>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('pelanggan.show', $buyer) }}" wire:navigate class="text-gray-600 hover:underline">
                        Detail
                    </a>
                    @can('update', $buyer)
                        <a href="{{ route('pelanggan.edit', $buyer) }}" wire:navigate class="text-blue-600 hover:underline">
                            Edit
                        </a>
                    @endcan
                    @can('delete', $buyer)
                        <button
                            wire:click="confirmDelete({{ $buyer->id }})"
                            class="text-red-600 hover:underline"
                        >
                            Hapus
                        </button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-400 text-sm">
                Tidak ada data pelanggan.
            </div>
        @endforelse
    </div>

    {{-- Desktop: table --}}
    <div class="hidden sm:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">No. HP</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Produk Terakhir</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($buyers as $buyer)
                    <tr wire:key="buyer-{{ $buyer->id }}">
                        <td class="px-4 py-3">{{ $buyer->nama }}</td>
                        <td class="px-4 py-3">{{ $buyer->no_hp }}</td>
                        <td class="px-4 py-3">
                            @if ($buyer->latestPurchase)
                                {{ $buyer->latestPurchase->product?->nama_produk ?? 'Produk telah dihapus' }}
                                <span class="text-gray-400">({{ $buyer->purchases_count }} transaksi)</span>
                            @else
                                <span class="text-gray-400">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('pelanggan.show', $buyer) }}" wire:navigate class="text-gray-600 hover:underline">
                                Detail
                            </a>
                            @can('update', $buyer)
                                <a href="{{ route('pelanggan.edit', $buyer) }}" wire:navigate class="text-blue-600 hover:underline">Edit</a>
                            @endcan
                            @can('delete', $buyer)
                                <button
                                    wire:click="confirmDelete({{ $buyer->id }})"
                                    class="text-red-600 hover:underline"
                                >
                                    Hapus
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">Tidak ada data pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $buyers->links() }}
    </div>

    <x-confirm-modal
        :show="$confirmingDeleteId !== null"
        title="Hapus Pelanggan"
        :message="'Yakin ingin menghapus pelanggan ' . $confirmingDeleteLabel . '? Tindakan ini tidak dapat dibatalkan.'"
        confirmAction="deleteConfirmed"
        cancelAction="cancelDelete"
    />
</div>