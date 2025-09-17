@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestione Orari
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Visualizza e gestisci gli orari dei corsi
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="previousWeek()"
                    class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Precedente
            </button>
            <span id="current-week" class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg font-medium">
                {{ $weekStart->format('d/m') }} - {{ $weekEnd->format('d/m/Y') }}
            </span>
            <button onclick="nextWeek()"
                    class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                Successiva
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <a href="{{ route('admin.schedules.manage') }}"
               class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Gestisci Orari
            </a>
            <button onclick="exportSchedule()"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Esporta CSV
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Corsi Totali</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total_courses'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Istruttori</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total_instructors'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Ore Settimanali</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['weekly_hours'] }}h</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Utilizzo Sale</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['room_utilization'] }}%</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Programma Settimanale</h3>
        </div>
        <div class="overflow-x-auto">
            <div class="grid grid-cols-7 gap-0 min-w-full" id="schedule-grid">
                @foreach($weeklySchedule as $day => $data)
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-gray-900">{{ $data['name'] }}</h4>
                        <p class="text-sm text-gray-500">{{ $data['date'] }}</p>
                    </div>
                    <div class="p-4 space-y-3 min-h-96">
                        @forelse($data['courses'] as $course)
                        <div class="bg-gradient-to-r from-rose-50 to-purple-50 border border-rose-200 rounded-lg p-3 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-start justify-between mb-2">
                                <h5 class="font-medium text-gray-900 text-sm truncate">{{ $course['name'] }}</h5>
                                <span class="text-xs bg-white px-2 py-1 rounded-full text-gray-600">
                                    {{ $course['time'] }}
                                </span>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $course['instructor'] }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $course['location'] }}
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                        {{ $course['level'] }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $course['students_count'] }}/{{ $course['max_students'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 flex justify-end">
                                <a href="{{ route('admin.schedules.show', $course['id']) }}"
                                   class="text-xs text-rose-600 hover:text-rose-800 font-medium">
                                    Dettagli →
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-400">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm">Nessun corso</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
let currentWeek = '{{ $weekStart->format('Y-m-d') }}';

function previousWeek() {
    const date = new Date(currentWeek);
    date.setDate(date.getDate() - 7);
    currentWeek = date.toISOString().split('T')[0];
    loadSchedule();
}

function nextWeek() {
    const date = new Date(currentWeek);
    date.setDate(date.getDate() + 7);
    currentWeek = date.toISOString().split('T')[0];
    loadSchedule();
}

function loadSchedule() {
    const url = new URL(window.location.href);
    url.searchParams.set('week', currentWeek);
    window.location.href = url.toString();
}

function exportSchedule() {
    const url = '{{ route('admin.schedules.export') }}?format=csv&week=' + currentWeek;
    window.open(url, '_blank');
}
</script>
@endsection