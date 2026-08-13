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

<footer class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
        <div class="text-sm text-slate-700">
            <span class="font-medium">{{ auth()->user()->name }}</span>
            <span class="text-slate-300 mx-1">&middot;</span>
            <span class="text-xs text-slate-500">{{ auth()->user()->role->label() }}</span>
        </div>

        <div class="flex items-center text-sm">
            <button wire:click="logout" class="text-xs font-medium text-red-500 hover:text-red-600 transition-colors">
                Logout
            </button>
        </div>
    </div>
</footer>