<?php
use App\Models\Buyer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.app')] class extends Component
{
    public array $menus = [];
    public string $backgroundType = 'color';
    public ?string $backgroundValue = null;
    public int $totalPelanggan = 0;
    public int $totalVendor = 0;
    public int $totalProduk = 0;
    public int $transaksiBulanIni = 0;
    public function mount(): void
    {
        $user = auth()->user();
        $this->menus = collect(config('menus'))
            ->filter(fn (array $menu) => $menu['roles'] === null || in_array($user->role->value, $menu['roles'], true))
            ->values()
            ->all();
        $this->backgroundType = Setting::get('menu_background_type', 'color');
        $this->backgroundValue = Setting::get('menu_background_value', '#f3f4f6');

        $this->totalPelanggan = Buyer::count();
        $this->totalVendor = Vendor::count();
        $this->totalProduk = Product::count();
        $this->transaksiBulanIni = Purchase::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
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
            <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
    @foreach ($menus as $menu)
        <a
            href="{{ $menu['status'] === 'active' ? route($menu['route']) : '#' }}"
            @class([
                'w-[calc(50%-0.375rem)] sm:w-[calc(33.333%-0.667rem)]',
                'aspect-square flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-lg bg-white shadow text-center',
                'hover:shadow-md hover:scale-105 hover:-translate-y-0.5 transition-all duration-200 ease-out cursor-pointer' => $menu['status'] === 'active',
                'opacity-60 cursor-not-allowed' => $menu['status'] === 'coming_soon',
            ])
        >
            <div class="w-8 h-8 sm:w-9 sm:h-9 lg:w-12 lg:h-12 text-gray-500">
                {!! $menu['icon'] !!}
            </div>
            <span class="text-xs sm:text-sm lg:text-base font-medium text-gray-700 leading-tight">{{ $menu['label'] }}</span>
            @if ($menu['status'] === 'coming_soon')
                <span class="text-xs text-gray-400">Segera hadir</span>
            @endif
        </a>
    @endforeach
</div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-700 mb-3">Ringkasan</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs sm:text-sm text-gray-500">Total Pelanggan</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $totalPelanggan }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs sm:text-sm text-gray-500">Total Vendor</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $totalVendor }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs sm:text-sm text-gray-500">Total Produk</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $totalProduk }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs sm:text-sm text-gray-500">Transaksi Bulan Ini</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $transaksiBulanIni }}</p>
            </div>
        </div>
    </div>
</div>