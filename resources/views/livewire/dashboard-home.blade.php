<?php
use App\Models\Buyer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.app')] class extends Component
{
    public array $menus = [];
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

        $this->totalPelanggan = Buyer::count();
        $this->totalVendor = Vendor::count();
        $this->totalProduk = Product::count();
        $this->transaksiBulanIni = Purchase::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }
}; ?>
<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
    @foreach ($menus as $menu)
        <a
            href="{{ $menu['status'] === 'active' ? route($menu['route']) : '#' }}"
                @class([
                    'w-[calc(50%-0.375rem)] sm:w-[calc(33.333%-0.667rem)]',
                    'aspect-square flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-lg bg-white border border-slate-200 shadow-sm text-center group',
                    'hover:border-blue-500 hover:shadow-md transition-all duration-150 cursor-pointer' => $menu['status'] === 'active',
                    'opacity-50 cursor-not-allowed' => $menu['status'] === 'coming_soon',
                ])
            >
                <div class="w-8 h-8 sm:w-9 sm:h-9 lg:w-12 lg:h-12 text-slate-600 group-hover:text-blue-600 transition-colors">
                    {!! $menu['icon'] !!}
                </div>
                <span class="text-xs sm:text-sm lg:text-base font-medium text-slate-700 group-hover:text-blue-600 leading-tight transition-colors">{{ $menu['label'] }}</span>
                @if ($menu['status'] === 'coming_soon')
                    <span class="text-xs text-slate-400">Segera hadir</span>
                @endif
            </a>
    @endforeach
</div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @php
            $ringkasan = [
                ['label' => 'Pelanggan', 'value' => $totalPelanggan],
                ['label' => 'Vendor', 'value' => $totalVendor],
                ['label' => 'Produk', 'value' => $totalProduk],
                ['label' => 'Transaksi Bulan Ini', 'value' => $transaksiBulanIni],
            ];
        @endphp

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mt-4">
            <div class="px-5 py-3 border-b border-slate-200">
                <span class="text-sm font-medium text-slate-700">Ringkasan</span>
            </div>

            {{-- Mobile: grid 2x2 --}}
            <div class="grid grid-cols-2 sm:hidden">
                @foreach ($ringkasan as $item)
                    <div @class([
                        'text-center px-3 py-4 border-slate-200',
                        'border-r' => $loop->index % 2 === 0,
                        'border-b' => $loop->index < 2,
                    ])>
                        <p class="text-xs text-slate-500 mb-1.5">
                            {{ $item['label'] }}
                        </p>
                        <p class="text-2xl font-semibold text-slate-800 tabular-nums">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- sm ke atas: strip horizontal --}}
            <div class="hidden sm:flex divide-x divide-slate-200">
                @foreach ($ringkasan as $item)
                    <div class="flex-1 min-w-0 px-6 py-5 text-center">
                        <p class="text-xs text-slate-500 mb-1.5">
                            {{ $item['label'] }}
                        </p>
                        <p class="text-3xl font-semibold text-slate-800 tabular-nums">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>