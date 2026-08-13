@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-[11px] font-semibold tracking-[0.15em] text-[#6B6560] uppercase']) }}>
    {{ $value ?? $slot }}
</label>