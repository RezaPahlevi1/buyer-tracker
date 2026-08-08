<?php

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public string $appTitle = '';

    public string $backgroundType = 'color';
    public string $backgroundColor = '#f3f4f6';
    public $backgroundImage = null; // file upload sementara
    public ?string $currentBackgroundImagePath = null; // path tersimpan di disk 'public', kalau type = image

    public function mount(): void
    {
        $this->appTitle = Setting::get('app_title', '');

        $this->backgroundType = Setting::get('menu_background_type', 'color');
        $storedValue = Setting::get('menu_background_value', '#f3f4f6');

        if ($this->backgroundType === 'image') {
            $this->currentBackgroundImagePath = $storedValue;
        } else {
            $this->backgroundColor = $storedValue;
        }
    }

    protected function rules(): array
    {
        return [
            'appTitle' => 'required|string|max:255',
            'backgroundType' => 'required|in:color,image',
            'backgroundColor' => 'required_if:backgroundType,color|nullable|string|max:20',
            'backgroundImage' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Setting::set('app_title', $this->appTitle);

        if ($this->backgroundType === 'color') {
            // kalau sebelumnya pakai gambar, hapus filenya biar tidak menumpuk jadi sampah di disk
            if ($this->currentBackgroundImagePath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->currentBackgroundImagePath);
                $this->currentBackgroundImagePath = null;
            }

            Setting::set('menu_background_type', 'color');
            Setting::set('menu_background_value', $this->backgroundColor);
        } else {
            if ($this->backgroundImage) {
                // ganti gambar lama dengan yang baru
                if ($this->currentBackgroundImagePath) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($this->currentBackgroundImagePath);
                }

                $this->currentBackgroundImagePath = $this->backgroundImage->store('settings', 'public');
                $this->backgroundImage = null;
            }

            if (! $this->currentBackgroundImagePath) {
                $this->addError('backgroundImage', 'Silakan unggah gambar terlebih dahulu.');
                return;
            }

            Setting::set('menu_background_type', 'image');
            Setting::set('menu_background_value', $this->currentBackgroundImagePath);
        }

        session()->flash('status', 'Pengaturan berhasil disimpan.');
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-xl font-semibold text-gray-800">Pengaturan Aplikasi</h1>

    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Judul Aplikasi</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700">Judul (tampil di bar atas)</label>
                <input type="text" wire:model="appTitle" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('appTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Background Menu Dashboard</h2>

            <div class="flex flex-col sm:flex-row gap-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" value="color" wire:model.live="backgroundType" class="border-gray-300">
                    Warna Polos
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" value="image" wire:model.live="backgroundType" class="border-gray-300">
                    Gambar
                </label>
            </div>

            @if ($backgroundType === 'color')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Warna</label>
                    <div class="flex items-center gap-3 mt-1">
                        <input type="color" wire:model="backgroundColor" class="h-10 w-16 rounded border-gray-300">
                        <span class="text-sm text-gray-500">{{ $backgroundColor }}</span>
                    </div>
                    @error('backgroundColor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unggah Gambar</label>
                    <input type="file" wire:model="backgroundImage" accept="image/png,image/jpeg,image/webp"
                           class="mt-1 w-full text-sm text-gray-600">
                    <p class="text-xs text-gray-400 mt-1">Format JPG/PNG/WEBP, maksimal 2MB.</p>
                    @error('backgroundImage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                    <div wire:loading wire:target="backgroundImage" class="text-xs text-gray-400 mt-1">
                        Mengunggah...
                    </div>
                </div>

                <div class="mt-2">
                    <span class="block text-sm font-medium text-gray-700 mb-1">Pratinjau</span>
                    <div class="w-full h-32 sm:h-40 rounded-md border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                        @if ($backgroundImage)
                            <img src="{{ $backgroundImage->temporaryUrl() }}" class="w-full h-full object-cover" alt="Pratinjau background">
                        @elseif ($currentBackgroundImagePath)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($currentBackgroundImagePath) }}" class="w-full h-full object-cover" alt="Pratinjau background">
                        @else
                            <span class="text-sm text-gray-400">Belum ada gambar</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                Simpan Pengaturan
            </button>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm text-gray-500 hover:underline text-center sm:text-left">
                Kembali ke Dashboard
            </a>
        </div>
    </form>
</div>