<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Studente
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione dettagli della tua scuola
                </p>
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
        <li class="text-gray-900 font-medium">Dettagli</li>
    </x-slot>




<div class="space-y-6" x-data="studentProfile">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.students.index') }}"
               class="inline-flex items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex items-center space-x-4">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ $student->name }}</h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <span class="w-2 h-2 mr-1.5 rounded-full {{ $student->active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            {{ $student->active ? 'Attivo' : 'Non attivo' }}
                        </span>
                        @if($student->date_of_birth)
                            <span class="text-gray-500 text-sm">
                                {{ $student->date_of_birth->age }} anni
                            </span>
                        @endif
                        <span class="text-gray-500 text-sm">
                            Iscritto il {{ $student->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <button @click="toggleStatus"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $student->active ? 'text-red-700 bg-red-100 hover:bg-red-200' : 'text-green-700 bg-green-100 hover:bg-green-200' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($student->active)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>
                {{ $student->active ? 'Disattiva' : 'Attiva' }}
            </button>

            <a href="{{ route('admin.students.edit', $student) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifica
            </a>

            <a href="{{ route('admin.enrollments.index', ['student_id' => $student->id]) }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Gestisci Iscrizioni
            </a>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-rose-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('admin.students.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-rose-600 md:ml-2">
                        Studenti
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $student->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Courses -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Corsi Totali</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900">{{ $stats['total_courses'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">{{ $stats['active_courses'] }} attivi</p>
        </div>

        <!-- Total Payments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pagamenti</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900">€{{ number_format($stats['total_payments'], 2, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
            @if($stats['pending_payments'] > 0)
                <p class="mt-2 text-xs text-orange-600">€{{ number_format($stats['pending_payments'], 2, ',', '.') }} in sospeso</p>
            @else
                <p class="mt-2 text-xs text-green-600">Tutti i pagamenti in regola</p>
            @endif
        </div>

        <!-- Attendance Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Presenza</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900">{{ $stats['attendance_rate'] }}%</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Media presenze</p>
        </div>

        <!-- Documents Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Documenti</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900">{{ $stats['documents_status']['total'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                {{ $stats['documents_status']['approved'] }} approvati,
                {{ $stats['documents_status']['pending'] }} in revisione
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Student Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Informazioni Personali
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $student->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $student->email }}" class="text-rose-600 hover:text-rose-700">{{ $student->email }}</a>
                            </dd>
                        </div>
                        @if($student->phone)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Telefono</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a href="tel:{{ $student->phone }}" class="text-rose-600 hover:text-rose-700">{{ $student->phone }}</a>
                                </dd>
                            </div>
                        @endif
                        @if($student->date_of_birth)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Data di Nascita</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $student->date_of_birth->format('d/m/Y') }}
                                    <span class="text-gray-500">({{ $student->date_of_birth->age }} anni)</span>
                                </dd>
                            </div>
                        @endif
                        @if($student->address)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Indirizzo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $student->address }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Emergency Contacts -->
            @if($student->emergency_contact_name || $student->emergency_contact_phone)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Contatti di Emergenza
                        </h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($student->emergency_contact_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nome</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $student->emergency_contact_name }}</dd>
                                </div>
                            @endif
                            @if($student->emergency_contact_phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Telefono</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="tel:{{ $student->emergency_contact_phone }}" class="text-rose-600 hover:text-rose-700">{{ $student->emergency_contact_phone }}</a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif

            <!-- Medical Information -->
            @if($student->medical_conditions)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Informazioni Mediche
                        </h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-900 whitespace-pre-line">{{ $student->medical_conditions }}</p>
                    </div>
                </div>
            @endif

            <!-- Enrollments -->
            @if($student->enrollments->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Iscrizioni ({{ $student->enrollments->count() }})
                        </h3>
                        <a href="{{ route('admin.enrollments.index', ['student_id' => $student->id]) }}"
                           class="text-sm text-rose-600 hover:text-rose-700">
                            Vedi tutte
                        </a>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach($student->enrollments->take(5) as $enrollment)
                            <div class="p-6 flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $enrollment->course->name }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Iscritto il {{ $enrollment->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($enrollment->status === 'active') bg-green-100 text-green-800
                                        @elseif($enrollment->status === 'enrolled') bg-blue-100 text-blue-800
                                        @elseif($enrollment->status === 'completed') bg-gray-100 text-gray-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Recent Payments -->
            @if($student->payments->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Pagamenti Recenti
                        </h3>
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($student->payments->take(5) as $payment)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">€{{ number_format($payment->amount, 2, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'Data non specificata' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($payment->status === 'completed') bg-green-100 text-green-800
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Documents -->
            @if($student->documents->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Documenti
                        </h3>
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($student->documents->take(5) as $document)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $document->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $document->uploaded_at ? $document->uploaded_at->format('d/m/Y') : 'Data non specificata' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($document->status === 'approved') bg-green-100 text-green-800
                                    @elseif($document->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($document->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Attendance -->
            @if($student->attendance->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Presenze Recenti
                        </h3>
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($student->attendance->take(10) as $attendance)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $attendance->date->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $attendance->attendable_type === 'App\\Models\\Course' ? 'Corso' : 'Evento' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($attendance->status === 'present') bg-green-100 text-green-800
                                    @elseif($attendance->status === 'late') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    @if($attendance->status === 'present') Presente
                                    @elseif($attendance->status === 'late') In ritardo
                                    @else Assente @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentProfile', () => ({
        async toggleStatus() {
            try {
                const response = await fetch(`/admin/students/{{ $student->id }}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const event = new CustomEvent('show-toast', {
                        detail: { message: data.message, type: 'success' }
                    });
                    window.dispatchEvent(event);

                    // Reload page to update UI
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    const event = new CustomEvent('show-toast', {
                        detail: { message: data.message || 'Errore durante il cambio di stato', type: 'error' }
                    });
                    window.dispatchEvent(event);
                }
            } catch (error) {
                console.error('Error:', error);
                const event = new CustomEvent('show-toast', {
                    detail: { message: 'Errore di connessione', type: 'error' }
                });
                window.dispatchEvent(event);
            }
        }
    }));
});
</script>
</x-app-layout>
