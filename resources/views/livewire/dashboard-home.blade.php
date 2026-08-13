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
                'aspect-square flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-lg bg-white border border-[#D8D3CA] text-center',
                'hover:border-[#2B2926] hover:-translate-y-0.5 transition-all duration-200 ease-out cursor-pointer' => $menu['status'] === 'active',
                'opacity-60 cursor-not-allowed' => $menu['status'] === 'coming_soon',
            ])
        >
            <div class="w-8 h-8 sm:w-9 sm:h-9 lg:w-12 lg:h-12 text-[#2B2926]">
                {!! $menu['icon'] !!}
            </div>
            <span class="text-xs sm:text-sm lg:text-base font-medium text-[#2B2926] leading-tight">{{ $menu['label'] }}</span>
            @if ($menu['status'] === 'coming_soon')
                <span class="text-xs text-[#6B6560]">Segera hadir</span>
            @endif
        </a>
    @endforeach
</div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-baseline gap-3 mb-4">
            <h2 class="font-serif text-lg text-[#2B2926]">Ringkasan</h2>
            <div class="flex-1 h-px bg-[#D8D3CA]"></div>
        </div>

        @php
            $ringkasan = [
                ['label' => 'Pelanggan', 'value' => $totalPelanggan],
                ['label' => 'Vendor', 'value' => $totalVendor],
                ['label' => 'Produk', 'value' => $totalProduk],
                ['label' => 'Transaksi Bulan Ini', 'value' => $transaksiBulanIni],
            ];
        @endphp

        <div class="bg-white border border-[#D8D3CA] rounded-lg overflow-hidden">
            {{-- Mobile: grid 2x2 ala sel tabel buku besar --}}
            <div class="grid grid-cols-2 sm:hidden">
                @foreach ($ringkasan as $item)
                    <div @class([
                        'text-center px-3 py-4 border-[#D8D3CA]',
                        'border-r' => $loop->index % 2 === 0,
                        'border-b' => $loop->index < 2,
                    ])>
                        <p class="text-[10px] font-semibold tracking-[0.15em] text-[#6B6560] uppercase mb-1.5">
                            {{ $item['label'] }}
                        </p>
                        <p class="font-serif text-2xl font-semibold text-[#2B2926] tabular-nums">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- sm ke atas: strip horizontal --}}
            <div class="hidden sm:flex divide-x divide-[#D8D3CA]">
                @foreach ($ringkasan as $item)
                    <div class="flex-1 min-w-0 px-6 py-5 text-center">
                        <p class="text-[10px] font-semibold tracking-[0.15em] text-[#6B6560] uppercase mb-1.5">
                            {{ $item['label'] }}
                        </p>
                        <p class="font-serif text-3xl font-semibold text-[#2B2926] tabular-nums">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>