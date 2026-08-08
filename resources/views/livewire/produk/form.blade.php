<?php

use App\Models\Product;
use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?Product $product = null;

    public string $nama_produk = '';
    public ?string $sku = null;
    public ?string $kategori = null;
    public ?string $deskripsi = null;

    /** @var array<int, array{vendor_id: ?int, harga_dari_vendor: ?float, is_active: bool}> */
    public array $selectedVendors = [];

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->authorize('update', $product);

            $this->product = $product;
            $this->nama_produk = $product->nama_produk;
            $this->sku = $product->sku;
            $this->kategori = $product->kategori;
            $this->deskripsi = $product->deskripsi;

            $this->selectedVendors = $product->vendors()
                ->get()
                ->map(fn ($v) => [
                    'vendor_id' => $v->id,
                    'harga_dari_vendor' => $v->pivot->harga_dari_vendor,
                    'is_active' => (bool) $v->pivot->is_active,
                ])
                ->all();
        } else {
            $this->authorize('create', Product::class);
        }

        if (empty($this->selectedVendors)) {
            $this->addVendorRow();
        }
    }

    protected function rules(): array
    {
        return [
            'nama_produk' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'selectedVendors.*.vendor_id' => 'nullable|distinct|exists:vendors,id',
            'selectedVendors.*.harga_dari_vendor' => 'nullable|numeric|min:0',
        ];
    }

    public function addVendorRow(): void
    {
        $this->selectedVendors[] = [
            'vendor_id' => null,
            'harga_dari_vendor' => null,
            'is_active' => true,
        ];
    }

    public function removeVendorRow(int $index): void
    {
        unset($this->selectedVendors[$index]);
        $this->selectedVendors = array_values($this->selectedVendors);
    }

    public function save(): void
    {
        $this->product
            ? $this->authorize('update', $this->product)
            : $this->authorize('create', Product::class);

        $this->validate();

        $product = $this->product ?? new Product();
        $product->fill([
            'nama_produk' => $this->nama_produk,
            'sku' => $this->sku,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
        ]);
        $product->save();

        $syncData = [];

        foreach ($this->selectedVendors as $row) {
            if (! $row['vendor_id']) {
                continue; // baris kosong (belum pilih vendor) dilewati
            }

            $syncData[$row['vendor_id']] = [
                'harga_dari_vendor' => $row['harga_dari_vendor'],
                'is_active' => $row['is_active'],
            ];
        }

        // sync() otomatis handle tambah/update/hapus relasi pivot sekaligus
        $product->vendors()->sync($syncData);

        session()->flash('status', 'Data produk berhasil disimpan.');

        $this->redirect(route('produk.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'vendors' => Vendor::query()->orderBy('nama_vendor')->get(['id', 'nama_vendor']),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">
        {{ $product ? 'Edit Produk' : 'Tambah Produk' }}
    </h1>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
                <input type="text" wire:model="nama_produk" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('nama_produk') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">SKU</label>
                <input type="text" wire:model="sku" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                <input type="text" wire:model="kategori" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea wire:model="deskripsi" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">Vendor Pemasok</h2>
                <button type="button" wire:click="addVendorRow" class="text-sm text-blue-600 hover:underline">
                    + Tambah Vendor
                </button>
            </div>

            <div class="space-y-3">
                @foreach ($selectedVendors as $index => $row)
                    <div wire:key="vendor-row-{{ $index }}"
                         class="grid grid-cols-12 gap-2 items-start border-b border-gray-100 pb-3">
                        <div class="col-span-5">
                            <select wire:model="selectedVendors.{{ $index }}.vendor_id" class="w-full rounded-md border-gray-300 text-sm">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->nama_vendor }}</option>
                                @endforeach
                            </select>
                            @error("selectedVendors.{$index}.vendor_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-3">
                            <input type="number" step="0.01" min="0" wire:model="selectedVendors.{{ $index }}.harga_dari_vendor"
                                   placeholder="Harga dari vendor" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div class="col-span-3 flex items-center gap-2 pt-2">
                            <input type="checkbox" wire:model="selectedVendors.{{ $index }}.is_active" class="rounded border-gray-300">
                            <label class="text-sm text-gray-600">Aktif</label>
                        </div>
                        <div class="col-span-1 text-right pt-2">
                            <button type="button" wire:click="removeVendorRow({{ $index }})"
                                    class="text-red-500 text-sm hover:underline">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                Simpan
            </button>
            <a href="{{ route('produk.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">
                Batal
            </a>
        </div>
    </form>
</div>