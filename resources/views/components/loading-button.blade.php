@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'default',
    'loadingText' => 'Caricamento...',
    'icon' => null,
    'disabled' => false
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = [
    'primary' => 'bg-gradient-to-r from-rose-500 to-purple-600 text-white hover:from-rose-600 hover:to-purple-700 focus:ring-rose-500',
    'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'warning' => 'bg-gradient-to-r from-amber-500 to-orange-600 text-white hover:from-amber-600 hover:to-orange-700 focus:ring-amber-500',
    'outline' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500'
];

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-xs',
    'default' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base'
];

$classes = $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @if($disabled) disabled @endif
    x-data="{ loading: false }"
    x-on:click="setTimeout(() => { loading = true; setTimeout(() => loading = false, 3000); }, 100);"
    :disabled="loading || {{ $disabled ? 'true' : 'false' }}"
>
    <!-- Loading Spinner -->
    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <!-- Icon (when not loading) -->
    @if($icon)
        <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @switch($icon)
                @case('save')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                    @break
                @case('plus')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    @break
                @case('edit')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    @break
                @case('trash')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    @break
                @case('check')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    @break
                @case('upload')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    @break
                @case('download')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    @break
                @case('refund')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    @break
                @default
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            @endswitch
        </svg>
    @endif

    <!-- Button Text -->
    <span x-show="!loading">{{ $slot }}</span>
    <span x-show="loading">{{ $loadingText }}</span>
</button>