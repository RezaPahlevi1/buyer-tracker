@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-0 border-b border-gray-300 bg-transparent px-0 py-1.5 text-gray-800 placeholder:text-gray-400 focus:border-b-2 focus:border-[#2F5D50] focus:ring-2 focus:ring-[#2F5D50]/20 transition-colors']) }}>