<?php

use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?Vendor $vendor = null;

    public string $nama_vendor = '';
    public ?string $nama_pic = null;
    public string $no_hp = '';
    public ?string $alamat = null;
    public ?string $email = null;
    public ?string $catatan = null;

    public function mount(?Vendor $vendor = null): void
    {
        if ($vendor && $vendor->exists) {
            $this->authorize('update', $vendor);

            $this->vendor = $vendor;
            $this->nama_vendor = $vendor->nama_vendor;
            $this->nama_pic = $vendor->nama_pic;
            $this->no_hp = $vendor->no_hp;
            $this->alamat = $vendor->alamat;
            $this->email = $vendor->email;
            $this->catatan = $vendor->catatan;
        } else {
            $this->authorize('create', Vendor::class);
        }
    }

    protected function rules(): array
    {
        return [
            'nama_vendor' => 'required|string|max:255',
            'nama_pic' => 'nullable|string|max:255',
            'no_hp' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'catatan' => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->vendor
            ? $this->authorize('update', $this->vendor)
            : $this->authorize('create', Vendor::class);

        $this->validate();

        $vendor = $this->vendor ?? new Vendor();
        $vendor->fill([
            'nama_vendor' => $this->nama_vendor,
            'nama_pic' => $this->nama_pic,
            'no_hp' => $this->no_hp,
            'alamat' => $this->alamat,
            'email' => $this->email,
            'catatan' => $this->catatan,
        ]);
        $vendor->save();

        session()->flash('status', 'Data vendor berhasil disimpan.');

        $this->redirect(route('vendor.index'), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">
        {{ $vendor ? 'Edit Vendor' : 'Tambah Vendor' }}
    </h1>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Vendor</label>
                <input type="text" wire:model="nama_vendor" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('nama_vendor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Nama PIC</label>
                <input type="text" wire:model="nama_pic" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('nama_pic') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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

        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                Simpan
            </button>
            <a href="{{ route('vendor.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">
                Batal
            </a>
        </div>
    </form>
</div>