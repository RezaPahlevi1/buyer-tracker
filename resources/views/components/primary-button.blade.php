<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center w-full px-4 py-2.5 bg-blue-600 border border-blue-600 rounded-lg font-medium text-sm text-white hover:bg-blue-700 hover:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-150']) }}>
    {{ $slot }}
</button>