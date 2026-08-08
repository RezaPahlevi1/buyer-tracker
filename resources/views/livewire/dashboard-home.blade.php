<?php
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.app')] class extends Component
{
    public array $menus = [];
    public string $backgroundType = 'color';
    public ?string $backgroundValue = null;
    public function mount(): void
    {
        $user = auth()->user();
        $this->menus = collect(config('menus'))
            ->filter(fn (array $menu) => $menu['roles'] === null || in_array($user->role->value, $menu['roles'], true))
            ->values()
            ->all();
        $this->backgroundType = Setting::get('menu_background_type', 'color');
        $this->backgroundValue = Setting::get('menu_background_value', '#f3f4f6');
    }
}; ?>
<div>
    <div
        class="w-screen -mt-8 ml-[calc(50%-50vw)] mr-[calc(50%-50vw)]"
        @if ($backgroundType === 'image' && $backgroundValue)
            style="background-image: url('{{ Illuminate\Support\Facades\Storage::url($backgroundValue) }}'); background-size: cover; background-position: center;"
        @else
            style="background-color: {{ $backgroundValue }};"
        @endif
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-3 gap-4">
                @foreach ($menus as $menu)
                    <a
                        href="{{ $menu['status'] === 'active' ? route($menu['route']) : '#' }}"
                        @class([
                            'aspect-square flex flex-col items-center justify-center gap-2 p-4 rounded-lg bg-white shadow text-center',
                            'hover:shadow-md transition cursor-pointer' => $menu['status'] === 'active',
                            'opacity-60 cursor-not-allowed' => $menu['status'] === 'coming_soon',
                        ])
                    >
                        <div class="w-9 h-9 text-gray-500">
                            {!! $menu['icon'] !!}
                        </div>
                        <span class="text-sm font-medium text-gray-700 leading-tight">{{ $menu['label'] }}</span>
                        @if ($menu['status'] === 'coming_soon')
                            <span class="text-xs text-gray-400">Segera hadir</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>