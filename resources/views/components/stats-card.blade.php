@props([
    'title' => '',
    'value' => 0,
    'icon' => null,
    'color' => 'rose',
    'change' => null,
    'changeType' => 'increase', // increase, decrease, neutral
    'subtitle' => null
])

@php
    $colorClasses = [
        'rose' => [
            'bg' => 'bg-gradient-to-r from-rose-400 to-pink-500',
            'text' => 'text-rose-600',
            'light' => 'bg-rose-50',
            'border' => 'border-rose-200'
        ],
        'purple' => [
            'bg' => 'bg-gradient-to-r from-purple-400 to-pink-500',
            'text' => 'text-purple-600',
            'light' => 'bg-purple-50',
            'border' => 'border-purple-200'
        ],
        'blue' => [
            'bg' => 'bg-gradient-to-r from-blue-400 to-cyan-500',
            'text' => 'text-blue-600',
            'light' => 'bg-blue-50',
            'border' => 'border-blue-200'
        ],
        'green' => [
            'bg' => 'bg-gradient-to-r from-green-400 to-emerald-500',
            'text' => 'text-green-600',
            'light' => 'bg-green-50',
            'border' => 'border-green-200'
        ],
        'orange' => [
            'bg' => 'bg-gradient-to-r from-orange-400 to-yellow-500',
            'text' => 'text-orange-600',
            'light' => 'bg-orange-50',
            'border' => 'border-orange-200'
        ]
    ];
    
    $colors = $colorClasses[$color] ?? $colorClasses['rose'];
@endphp

<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-xl transition-all duration-200">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 mb-1">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
            
            @if($subtitle)
                <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
            
            @if($change)
                <div class="flex items-center mt-2">
                    @if($changeType === 'increase')
                        <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"/>
                        </svg>
                        <span class="text-sm text-green-600 font-medium">+{{ $change }}%</span>
                    @elseif($changeType === 'decrease')
                        <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"/>
                        </svg>
                        <span class="text-sm text-red-600 font-medium">-{{ $change }}%</span>
                    @else
                        <span class="text-sm text-gray-600 font-medium">{{ $change }}%</span>
                    @endif
                    <span class="text-xs text-gray-500 ml-2">dal mese scorso</span>
                </div>
            @endif
        </div>
        
        @if($icon)
            <div class="ml-4">
                <div class="w-14 h-14 {{ $colors['bg'] }} rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @switch($icon)
                            @case('users')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                @break
                            @case('academic-cap')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                @break
                            @case('currency-dollar')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @break
                            @case('office-building')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                @break
                            @case('chart-bar')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                @break
                            @case('calendar')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                @break
                            @case('clock')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @break
                            @case('star')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                @break
                            @default
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        @endswitch
                    </svg>
                </div>
            </div>
        @endif
    </div>
</div>