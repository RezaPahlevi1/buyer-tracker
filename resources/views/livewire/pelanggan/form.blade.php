<?php

use App\Models\Buyer;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?Buyer $buyer = null;

    public string $nama = '';
    public string $no_hp = '';
    public ?string $alamat = null;
    public ?string $email = null;
    public ?string $catatan = null;

    /** @var array<int, array{id: ?int, product_id: ?int, jumlah: int, tanggal_beli: ?string, harga_saat_beli: ?float}> */
    public array $purchases = [];

    public function mount(?Buyer $buyer = null): void
    {
        if ($buyer && $buyer->exists) {
            $this->authorize('update', $buyer);

            $this->buyer = $buyer;
            $this->nama = $buyer->nama;
            $this->no_hp = $buyer->no_hp;
            $this->alamat = $buyer->alamat;
            $this->email = $buyer->email;
            $this->catatan = $buyer->catatan;

            $this->purchases = $buyer->purchases()
                ->with('product')
                ->latest('tanggal_beli')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'product_id' => $p->product_id,
                    'jumlah' => $p->jumlah,
                    'tanggal_beli' => $p->tanggal_beli?->format('Y-m-d'),
                    'harga_saat_beli' => $p->harga_saat_beli,
                ])
                ->all();
        } else {
            $this->authorize('create', Buyer::class);
        }

        if (empty($this->purchases)) {
            $this->addPurchaseRow();
        }
    }

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'catatan' => 'nullable|string',
            'purchases.*.product_id' => 'nullable|exists:products,id',
            'purchases.*.jumlah' => 'nullable|integer|min:1',
            'purchases.*.tanggal_beli' => 'nullable|date',
            'purchases.*.harga_saat_beli' => 'nullable|numeric|min:0',
        ];
    }

    public function addPurchaseRow(): void
    {
        $this->purchases[] = [
            'id' => null,
            'product_id' => null,
            'jumlah' => 1,
            'tanggal_beli' => now()->format('Y-m-d'),
            'harga_saat_beli' => null,
        ];
    }

    public function removePurchaseRow(int $index): void
    {
        unset($this->purchases[$index]);
        $this->purchases = array_values($this->purchases);
    }

    public function save(): void
    {
        $this->buyer
            ? $this->authorize('update', $this->buyer)
            : $this->authorize('create', Buyer::class);

        $this->validate();

        $buyer = $this->buyer ?? new Buyer();
        $buyer->fill([
            'nama' => $this->nama,
            'no_hp' => $this->no_hp,
            'alamat' => $this->alamat,
            'email' => $this->email,
            'catatan' => $this->catatan,
        ]);
        $buyer->save();

        $keepIds = [];

        foreach ($this->purchases as $row) {
            if (! $row['product_id']) {
                continue; // baris kosong (belum pilih produk) dilewati, tidak disimpan
            }

            $purchase = $buyer->purchases()->updateOrCreate(
                ['id' => $row['id']],
                [
                    'product_id' => $row['product_id'],
                    'jumlah' => $row['jumlah'] ?: 1,
                    'tanggal_beli' => $row['tanggal_beli'],
                    'harga_saat_beli' => $row['harga_saat_beli'],
                ]
            );

            $keepIds[] = $purchase->id;
        }

        // baris yang dihapus di form ikut dihapus dari DB (bukan soft delete — Purchase tidak pakai SoftDeletes)
        $buyer->purchases()->whereNotIn('id', $keepIds)->delete();

        session()->flash('status', 'Data pelanggan berhasil disimpan.');

        $this->redirect(route('pelanggan.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'products' => Product::query()->orderBy('nama_produk')->get(['id', 'nama_produk']),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">
        {{ $buyer ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}
    </h1>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" wire:model="nama" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">No. HP</label>
                <input type="text" wire:model="no_hp" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('no_hp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" wire:model="email" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Alamat</label>
                <textarea wire:model="alamat" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Catatan</label>
                <textarea wire:model="catatan" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
        </div>

       <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700">Riwayat Pembelian</h2>
            <button type="button" wire:click="addPurchaseRow" class="text-sm text-blue-600 hover:underline">
                + Tambah Produk
            </button>
    </div>

    <div class="space-y-3">
        @foreach ($purchases as $index => $row)
            <div wire:key="purchase-row-{{ $row['id'] ?? 'new-' . $index }}"
                 class="border border-gray-200 rounded-md p-3 sm:border-0 sm:border-b sm:border-gray-100 sm:rounded-none sm:p-0 sm:pb-3">
                <div class="flex items-center justify-between mb-2 sm:hidden">
                    <span class="text-xs font-semibold text-gray-500">Produk #{{ $index + 1 }}</span>
                    <button type="button" wire:click="removePurchaseRow({{ $index }})"
                            class="text-red-500 text-sm hover:underline">Hapus ✕</button>
                </div>
                <div class="flex flex-col gap-3 sm:grid sm:grid-cols-12 sm:gap-2 sm:items-start">
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1 sm:hidden">Produk</label>
                        <select wire:model="purchases.{{ $index }}.product_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->nama_produk }}</option>
                            @endforeach
                            @if ($row['product_id'] && ! $products->contains('id', $row['product_id']))
                                <option value="{{ $row['product_id'] }}" disabled>(Produk telah dihapus — pilih ulang)</option>
                            @endif
                        </select>
                        @error("purchases.{$index}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1 sm:hidden">Jumlah</label>
                        <input type="number" min="1" wire:model="purchases.{{ $index }}.jumlah"
                               placeholder="Qty" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1 sm:hidden">Tanggal Beli</label>
                        <input type="date" wire:model="purchases.{{ $index }}.tanggal_beli"
                               class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1 sm:hidden">Harga</label>
                        <input type="number" step="0.01" min="0" wire:model="purchases.{{ $index }}.harga_saat_beli"
                               placeholder="Harga" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="hidden sm:block sm:col-span-1 sm:text-right">
                        <button type="button" wire:click="removePurchaseRow({{ $index }})"
                                class="text-red-500 text-sm hover:underline">✕</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                Simpan
            </button>
            <a href="{{ route('pelanggan.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">
                Batal
            </a>
        </div>
    </form>
</div>