<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $course->name }} - Dettagli
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Informazioni complete e gestione del corso
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.courses.edit', $course) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifica
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.courses.index') }}" class="text-gray-500 hover:text-gray-700">Corsi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">{{ $course->name }}</li>
    </x-slot>

    <div class="space-y-6">
        <!-- Course Header Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-rose-500 to-purple-600 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 bg-white/20 rounded-xl flex items-center justify-center text-white font-bold text-2xl">
                        {{ strtoupper(substr($course->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 text-white">
                        <h1 class="text-xl md:text-2xl font-bold">{{ $course->name }}</h1>
                        <p class="text-rose-100 mt-1">{{ $course->description ?? 'Corso di danza' }} • {{ $course->instructor->name ?? 'Nessun istruttore' }}</p>
                        <div class="flex items-center mt-2">
                            <span class="{{ $course->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} text-xs font-medium px-2.5 py-0.5 rounded-full">
                                {{ $course->active ? 'Attivo' : 'Non Attivo' }}
                            </span>
                            <span class="ml-3 text-rose-100 text-sm">
                                ID: {{ $course->id }} • Inizio: {{ $course->start_date ? $course->start_date->format('d/m/Y') : 'Non definito' }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        @php
                            $enrolledCount = $stats['enrolled_students'] ?? 0;
                            $maxStudents = $course->max_students ?? 1;
                            $percentage = $maxStudents > 0 ? ($enrolledCount / $maxStudents) * 100 : 0;
                        @endphp
                        <div class="text-xl md:text-2xl font-bold">{{ $enrolledCount }}/{{ $maxStudents }}</div>
                        <p class="text-rose-100 text-sm">studenti iscritti</p>
                        <div class="w-24 bg-white/20 rounded-full h-2 mt-2">
                            <div class="bg-white h-2 rounded-full" style="width: {{ min(100, $percentage) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stats-card
                title="Studenti Iscritti"
                :value="$stats['enrolled_students'] ?? 0"
                icon="users"
                color="blue"
                :subtitle="($stats['available_spots'] ?? 0) . ' posti disponibili'"
            />

            <x-stats-card
                title="Durata Corso"
                :value="$course->duration_weeks ?? 'N/D'"
                icon="calendar"
                color="purple"
                subtitle="settimane"
            />

            <x-stats-card
                title="Ricavo Totale"
                :value="'€' . number_format($stats['total_revenue'] ?? 0, 2, ',', '.')"
                icon="currency-dollar"
                color="green"
                :subtitle="'€' . number_format($stats['revenue_per_student'] ?? 0, 2, ',', '.') . ' per studente'"
            />

            <x-stats-card
                title="Frequenza"
                :value="number_format($stats['attendance_rate'] ?? 0, 1) . '%'"
                icon="check-circle"
                color="rose"
                subtitle="tasso di partecipazione"
            />
        </div>

        <!-- Content Tabs -->
        <div x-data="{ activeTab: 'overview' }" class="bg-white rounded-2xl shadow-lg border border-gray-200">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <button @click="activeTab = 'overview'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Panoramica
                    </button>
                    <button @click="activeTab = 'students'"
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'students', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'students' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Studenti ({{ $stats['enrolled_students'] ?? 0 }})
                    </button>
                    <button @click="activeTab = 'schedule'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'schedule', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'schedule' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Orario
                    </button>
                    {{-- Removed attendance and materials tabs - not implemented yet --}}
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Course Details -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Dettagli Corso</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Livello:</span>
                                    <span class="font-medium text-gray-900">{{ ucfirst($course->level ?? 'Non specificato') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Durata:</span>
                                    <span class="font-medium text-gray-900">
                                        @if($course->duration_weeks)
                                            {{ $course->duration_weeks }} settimane
                                        @elseif($course->start_date && $course->end_date)
                                            {{ $course->start_date->diffInWeeks($course->end_date) }} settimane
                                        @else
                                            Non specificato
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Orario:</span>
                                    <div class="font-medium text-gray-900">
                                        @if($course->schedule_data && is_array($course->schedule_data) && count($course->schedule_data) > 0)
                                            @foreach($course->schedule_data as $slot)
                                                <div class="text-sm">
                                                    {{ $slot['day'] ?? 'N/A' }}: {{ $slot['start_time'] ?? 'N/A' }} - {{ $slot['end_time'] ?? 'N/A' }}
                                                    @if(isset($slot['location']))
                                                        ({{ $slot['location'] }})
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span>Da definire</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Data inizio:</span>
                                    <span class="font-medium text-gray-900">{{ $course->start_date ? $course->start_date->format('d/m/Y') : 'Non definito' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Data fine:</span>
                                    <span class="font-medium text-gray-900">{{ $course->end_date ? $course->end_date->format('d/m/Y') : 'Non definito' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Prezzo:</span>
                                    <span class="font-medium text-green-600">{{ $course->formatted_price }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Sala:</span>
                                    <span class="font-medium text-gray-900">{{ $course->location ?? 'Non specificata' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Instructor Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Istruttore</h3>
                            @if($course->instructor)
                                <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg p-4 border border-rose-200">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-16 h-16 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            {{ strtoupper(substr($course->instructor->name, 0, 1) . (explode(' ', $course->instructor->name)[1][0] ?? '')) }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900">{{ $course->instructor->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ $course->instructor->email ?? 'Email non disponibile' }}</p>
                                            <p class="text-sm text-gray-600">Istruttore della scuola</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex space-x-3">
                                        <a href="mailto:{{ $course->instructor->email }}" class="flex-1 px-4 py-2 bg-white text-rose-600 border border-rose-600 rounded-lg hover:bg-rose-50 text-sm font-medium text-center">
                                            Contatta
                                        </a>
                                        @php
                                            $staffRecord = \App\Models\Staff::where('user_id', $course->instructor_id)
                                                                           ->where('school_id', $course->school_id)
                                                                           ->first();
                                        @endphp
                                        @if($staffRecord)
                                            <a href="{{ route('admin.staff.show', $staffRecord->id) }}" class="flex-1 px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-medium text-center">
                                                Profilo Staff
                                            </a>
                                        @else
                                            <a href="{{ route('admin.users.show', $course->instructor_id) }}" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium text-center">
                                                Profilo Utente
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
                                    <p class="text-gray-500">Nessun istruttore assegnato</p>
                                    <button class="mt-3 px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-medium">
                                        Assegna Istruttore
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Course Description -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Descrizione Corso</h3>
                        <div class="prose prose-gray max-w-none">
                            @if($course->description)
                                <p class="text-gray-700">{{ $course->description }}</p>
                            @else
                                <p class="text-gray-500 italic">Nessuna descrizione disponibile per questo corso.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Prerequisites & Requirements section removed - was hardcoded content --}}
                </div>

                <!-- Students Tab -->
                <div x-show="activeTab === 'students'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Studenti Iscritti ({{ $stats['enrolled_students'] ?? 0 }}/{{ $course->max_students }})</h3>
                        <div class="flex space-x-3">
                            <button @click="$dispatch('open-modal', 'add-student')" 
                                    class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-medium">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Aggiungi Studente
                            </button>
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                                Esporta Lista
                            </button>
                        </div>
                    </div>

                    <!-- Students Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @if($course->enrollments && $course->enrollments->count() > 0)
                            @foreach ($course->enrollments as $enrollment)
                                <div class="bg-white p-4 rounded-lg border border-gray-200 hover:border-rose-300 transition-colors">
                                    <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0 mb-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                            @if($enrollment->user && $enrollment->user->name)
                                                @php
                                                    $nameParts = explode(' ', $enrollment->user->name);
                                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                                @endphp
                                                {{ $initials }}
                                            @else
                                                ?
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $enrollment->user->name ?? 'Nome non disponibile' }}</h4>
                                            <p class="text-sm text-gray-500">{{ $enrollment->user->email ?? 'Email non disponibile' }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Iscritto:</span>
                                            <span class="text-gray-900">{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : $enrollment->created_at->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Stato:</span>
                                            <span class="font-medium {{ $enrollment->status === 'attiva' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ ucfirst($enrollment->status ?? 'Sconosciuto') }}
                                            </span>
                                        </div>
                                        @if($enrollment->notes)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Note:</span>
                                                <span class="text-gray-900 text-xs">{{ Str::limit($enrollment->notes, 20) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-3 flex space-x-2">
                                        <a href="{{ route('admin.students.show', $enrollment->user_id) }}" class="flex-1 px-3 py-1 bg-rose-50 text-rose-600 rounded text-xs font-medium hover:bg-rose-100 text-center">
                                            Dettagli
                                        </a>
                                        @if($enrollment->user && $enrollment->user->email)
                                            <a href="mailto:{{ $enrollment->user->email }}" class="flex-1 px-3 py-1 bg-gray-50 text-gray-600 rounded text-xs font-medium hover:bg-gray-100 text-center">
                                                Contatta
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuno studente iscritto</h3>
                                <p class="mt-1 text-sm text-gray-500">Gli studenti iscritti a questo corso appariranno qui</p>
                                <div class="mt-6">
                                    <button @click="$dispatch('open-modal', 'add-student')"
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Aggiungi primo studente
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Schedule Tab -->
                <div x-show="activeTab === 'schedule'" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Orario delle Lezioni</h3>
                    @if($course->schedule_data && is_array($course->schedule_data) && count($course->schedule_data) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($course->schedule_data as $index => $slot)
                                <div class="bg-gradient-to-r from-rose-50 to-purple-50 p-6 rounded-lg border border-rose-200">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 {{ $index % 2 === 0 ? 'bg-rose-500' : 'bg-purple-500' }} rounded-lg flex items-center justify-center text-white">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $slot['day'] ?? 'Giorno non specificato' }}</h4>
                                            <p class="text-gray-600">
                                                {{ $slot['start_time'] ?? 'N/A' }} - {{ $slot['end_time'] ?? 'N/A' }}
                                            </p>
                                            @if(isset($slot['location']))
                                                <p class="text-sm text-gray-500">{{ $slot['location'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($course->location && $course->location !== ($course->schedule_data[0]['location'] ?? ''))
                            <div class="mt-4 bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <div class="text-blue-700">
                                    <strong>Sede principale:</strong> {{ $course->location }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Orario non definito</h3>
                            <p class="mt-1 text-sm text-gray-500">L'orario delle lezioni verrà definito a breve</p>
                        </div>
                    @endif
                </div>

                {{-- Attendance and Materials tabs content removed - not implemented yet --}}
            </div>
        </div>
    </div>

    {{-- Duplicate course modal removed - contained hardcoded content --}}
</x-app-layout>
