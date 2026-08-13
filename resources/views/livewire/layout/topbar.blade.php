<?php

use App\Models\Setting;
use Livewire\Volt\Component;

new class extends Component
{
    public string $appTitle = '';

    public function mount(): void
    {
        $this->appTitle = Setting::get('app_title', '');
    }
}; ?>

<header class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-center">
        <a href="{{ route('dashboard') }}" wire:navigate class="text-lg font-semibold text-slate-800">
            {{ $appTitle }}
        </a>
    </div>
</header>