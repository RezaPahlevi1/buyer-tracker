@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-0 border-b border-[#D8D3CA] bg-transparent px-0 py-1.5 text-[#2B2926] placeholder:text-[#6B6560]/60 focus:border-b-2 focus:border-[#2B2926] focus:ring-2 focus:ring-[#2B2926]/10 transition-colors']) }}>