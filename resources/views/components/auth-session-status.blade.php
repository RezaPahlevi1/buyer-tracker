@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-[#2F5D50]']) }}>
        {{ $status }}
    </div>
@endif