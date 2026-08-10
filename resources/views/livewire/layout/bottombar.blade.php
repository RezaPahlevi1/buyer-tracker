<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<footer class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            {{ auth()->user()->name }}
            <span class="text-gray-300 mx-1">&middot;</span>
            <span class="text-xs uppercase tracking-wide text-gray-400">{{ auth()->user()->role->label() }}</span>
        </div>

        <div class="flex items-center text-sm">
            <button wire:click="logout" class="text-red-500 hover:text-red-600">
                Logout
            </button>
        </div>
    </div>
</footer>