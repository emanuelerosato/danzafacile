@props([
    'title' => '',
    'description' => '',
    'instructor' => '',
    'level' => '',
    'students' => 0,
    'maxStudents' => 0,
    'price' => 0,
    'schedule' => '',
    'image' => null,
    'status' => 'active', // active, full, suspended
    'href' => '#',
    'actions' => null
])

@php
    $statusClasses = [
        'active' => 'bg-green-100 text-green-800',
        'full' => 'bg-orange-100 text-orange-800',
        'suspended' => 'bg-red-100 text-red-800'
    ];
    
    $statusLabels = [
        'active' => 'Attivo',
        'full' => 'Completo',
        'suspended' => 'Sospeso'
    ];
    
    $percentage = $maxStudents > 0 ? ($students / $maxStudents) * 100 : 0;
@endphp

<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden hover:shadow-xl transition-all duration-300 group">
    <!-- Course Image -->
    <div class="relative h-48 bg-gradient-to-r from-rose-400 via-pink-500 to-purple-600 overflow-hidden">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover">
        @else
            <!-- Default dance illustration -->
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
        @endif
        
        <!-- Status Badge -->
        <div class="absolute top-4 right-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$status] ?? $statusClasses['active'] }}">
                {{ $statusLabels[$status] ?? $status }}
            </span>
        </div>
        
        <!-- Level Badge -->
        @if($level)
            <div class="absolute top-4 left-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white backdrop-blur-sm">
                    {{ $level }}
                </span>
            </div>
        @endif
    </div>
    
    <!-- Course Content -->
    <div class="p-6">
        <!-- Title and Description -->
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-rose-600 transition-colors duration-200">
                <a href="{{ $href }}" class="hover:underline">{{ $title }}</a>
            </h3>
            @if($description)
                <p class="text-gray-600 text-sm line-clamp-2">{{ $description }}</p>
            @endif
        </div>
        
        <!-- Instructor -->
        @if($instructor)
            <div class="flex items-center mb-3">
                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-sm text-gray-600">{{ $instructor }}</span>
            </div>
        @endif
        
        <!-- Schedule -->
        @if($schedule)
            <div class="flex items-center mb-3">
                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm text-gray-600">{{ $schedule }}</span>
            </div>
        @endif
        
        <!-- Students Progress -->
        @if($maxStudents > 0)
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Studenti iscritti</span>
                    <span class="text-sm font-medium text-gray-900">{{ $students }}/{{ $maxStudents }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-rose-400 to-purple-500 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $percentage }}%"></div>
                </div>
            </div>
        @endif
        
        <!-- Price and Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            @if($price > 0)
                <div class="text-2xl font-bold text-rose-600">
                    €{{ number_format($price, 2) }}
                    <span class="text-sm font-normal text-gray-500">/mese</span>
                </div>
            @else
                <div class="text-lg font-semibold text-green-600">Gratuito</div>
            @endif
            
            @if($actions)
                <div class="flex items-center space-x-2">
                    {{ $actions }}
                </div>
            @else
                <a href="{{ $href }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    Dettagli
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>
</div>