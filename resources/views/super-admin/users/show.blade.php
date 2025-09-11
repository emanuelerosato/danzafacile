@extends('layouts.app')

@section('title', 'Dettaglio Utente - Super Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50" x-data="userDetails()">
    <!-- Header Section -->
    <div class="bg-white/30 backdrop-blur-sm border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('super-admin.users.index') }}" 
                       class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Torna alla lista
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">👤 Profilo Utente</h1>
                        <p class="text-sm text-gray-600">Dettagli completi di {{ $user->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($user->role !== 'super_admin' && $user->id !== auth()->id())
                        <form action="{{ route('super-admin.users.impersonate', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-700 bg-purple-100 border border-purple-300 rounded-lg hover:bg-purple-200 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Impersona
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('super-admin.users.edit', $user) }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-pink-600 rounded-lg hover:from-rose-600 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifica
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - User Profile -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-8 text-center">
                        <div class="mx-auto h-24 w-24 rounded-full bg-gradient-to-r from-rose-400 to-pink-500 flex items-center justify-center text-white font-bold text-3xl mb-4">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $user->name }}</h2>
                        <p class="text-gray-600 mb-4">{{ $user->email }}</p>
                        
                        <div class="flex flex-wrap justify-center gap-2 mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->role === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role === 'instructor' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $user->role === 'student' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $user->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->active ? 'Attivo' : 'Inattivo' }}
                            </span>
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                    ✅ Verificato
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    ⏳ Non verificato
                                </span>
                            @endif
                        </div>

                        @if($user->school)
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-medium text-gray-500">Scuola di Appartenenza</h3>
                                <p class="text-lg font-medium text-gray-900 mt-1">{{ $user->school->name }}</p>
                                @if($user->school->email)
                                    <p class="text-sm text-gray-600">{{ $user->school->email }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($user->role !== 'super_admin' && $user->id !== auth()->id())
                        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200">
                            <button @click="toggleUserStatus()" 
                                    :class="userActive ? 'bg-red-50 text-red-700 border-red-300 hover:bg-red-100' : 'bg-green-50 text-green-700 border-green-300 hover:bg-green-100'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="userActive" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"/>
                                    <path x-show="!userActive" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="userActive ? 'Disattiva Utente' : 'Attiva Utente'"></span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Contact Information -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
                        <h3 class="text-lg font-semibold text-gray-900">📞 Informazioni di Contatto</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-sm font-medium text-gray-900">{{ $user->email }}</p>
                            </div>
                        </div>
                        
                        @if($user->phone)
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Telefono</p>
                                <p class="text-sm font-medium text-gray-900">{{ $user->phone }}</p>
                            </div>
                        </div>
                        @endif

                        @if($user->date_of_birth)
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Data di Nascita</p>
                                <p class="text-sm font-medium text-gray-900">{{ $user->date_of_birth->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $user->date_of_birth->age }} anni</p>
                            </div>
                        </div>
                        @endif

                        @if($user->address)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Indirizzo</p>
                                <p class="text-sm font-medium text-gray-900">{{ $user->address }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Account Details -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-slate-50">
                        <h3 class="text-lg font-semibold text-gray-900">📊 Dettagli Account</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Registrato</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Ultimo aggiornamento</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($user->email_verified_at)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Email verificata</span>
                            <span class="text-sm font-medium text-green-600">{{ $user->email_verified_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-500">ID Utente</span>
                            <span class="text-sm font-mono text-gray-900">#{{ $user->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Statistics and Activities -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Statistics Cards -->
                @if($user->role === 'student')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Enrolled Courses -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $user->courseEnrollments()->count() }}</p>
                                <p class="text-sm text-gray-600">Corsi Iscritti</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Payments -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">€{{ number_format($user->payments()->sum('amount'), 2) }}</p>
                                <p class="text-sm text-gray-600">Totale Pagato</p>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $user->documents()->count() }}</p>
                                <p class="text-sm text-gray-600">Documenti</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($user->role === 'admin' || $user->role === 'instructor')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Managed Courses (for admin/instructor) -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                @if($user->role === 'admin')
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->school?->courses()->count() ?? 0 }}</p>
                                    <p class="text-sm text-gray-600">Corsi della Scuola</p>
                                @else
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->instructedCourses()->count() }}</p>
                                    <p class="text-sm text-gray-600">Corsi Insegnati</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Managed Students -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                @if($user->role === 'admin')
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->school?->users()->where('role', 'student')->count() ?? 0 }}</p>
                                    <p class="text-sm text-gray-600">Studenti della Scuola</p>
                                @else
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->instructedCourses()->withCount('enrollments')->get()->sum('enrollments_count') }}</p>
                                    <p class="text-sm text-gray-600">Studenti Seguiti</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Recent Activity -->
                @if($user->role === 'student')
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-rose-50 to-pink-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">📚 Corsi Iscritti</h3>
                            <span class="text-sm text-gray-500">{{ $user->courseEnrollments()->count() }} corsi</span>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($user->courseEnrollments()->count() > 0)
                            <div class="space-y-4">
                                @foreach($user->courseEnrollments()->with('course')->latest()->take(5)->get() as $enrollment)
                                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900">{{ $enrollment->course->name }}</h4>
                                                <p class="text-sm text-gray-500">{{ $enrollment->course->description }}</p>
                                                <p class="text-xs text-gray-400">Iscritto il {{ $enrollment->created_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $enrollment->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $enrollment->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                            <span class="text-sm font-medium text-gray-900">€{{ number_format($enrollment->course->price, 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun corso</h3>
                                <p class="text-gray-500">L'utente non è iscritto a nessun corso</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Recent Payments -->
                @if($user->payments()->count() > 0)
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">💳 Pagamenti Recenti</h3>
                            <span class="text-sm text-gray-500">{{ $user->payments()->count() }} pagamenti</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($user->payments()->with('course')->latest()->take(5)->get() as $payment)
                                <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 {{ $payment->status === 'completed' ? 'bg-green-100' : 'bg-yellow-100' }} rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 {{ $payment->status === 'completed' ? 'text-green-600' : 'text-yellow-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $payment->description ?? ($payment->course ? $payment->course->name : 'Pagamento') }}</h4>
                                            <p class="text-sm text-gray-500">{{ $payment->payment_method }} • {{ $payment->payment_date?->format('d/m/Y') ?? 'Data non disponibile' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-900">€{{ number_format($payment->amount, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Internal Notes -->
                @if($user->notes)
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                        <h3 class="text-lg font-semibold text-gray-900">📝 Note Interne</h3>
                        <p class="text-sm text-gray-600">Visibili solo agli amministratori</p>
                    </div>
                    <div class="p-6">
                        <div class="prose prose-sm max-w-none">
                            <p class="text-gray-700 whitespace-pre-line">{{ $user->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js User Details -->
<script>
function userDetails() {
    return {
        userActive: {{ $user->active ? 'true' : 'false' }},
        
        async toggleUserStatus() {
            if (!confirm(`Sei sicuro di voler ${this.userActive ? 'disattivare' : 'attivare'} questo utente?`)) {
                return;
            }
            
            try {
                const response = await fetch(`/super-admin/users/{{ $user->id }}/toggle-active`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ active: !this.userActive })
                });
                
                if (response.ok) {
                    this.userActive = !this.userActive;
                    window.location.reload();
                } else {
                    alert('Errore durante l\'aggiornamento dello status utente');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Errore durante l\'aggiornamento dello status utente');
            }
        }
    }
}
</script>

@endsection