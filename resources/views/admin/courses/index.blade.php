@extends('layouts.admin')

@section('title', 'Gestione Corsi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestione Corsi</h1>
            <p class="text-gray-600">Tutti i corsi della tua scuola di danza</p>
        </div>
            <div class="flex items-center space-x-3">
                <button @click="$dispatch('open-modal', 'bulk-actions')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Azioni Multiple
                </button>
                <a href="{{ route('admin.courses.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuovo Corso
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Corsi</li>
    </x-slot>

    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stats-card 
                title="Totale Corsi"
                :value="12"
                icon="academic-cap"
                color="rose"
                subtitle="8 attivi, 4 in pausa"
            />
            
            <x-stats-card 
                title="Studenti Iscritti"
                :value="186"
                icon="users"
                color="purple"
                :change="12"
                changeType="increase"
                subtitle="Media 15.5 per corso"
            />
            
            <x-stats-card 
                title="Ricavo Mensile"
                :value="'€12,450'"
                icon="currency-dollar"
                color="green"
                :change="8"
                changeType="increase"
            />
            
            <x-stats-card 
                title="Tasso Occupazione"
                :value="'78%'"
                icon="chart-bar"
                color="blue"
                subtitle="Media posti occupati"
            />
        </div>

        <!-- Course Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-course-card 
                title="Danza Classica Intermedio"
                description="Corso di perfezionamento tecnica classica con focus su adagio e allegro. Preparazione per esami RAD."
                instructor="Prof. Martina Rossi"
                level="Intermedio"
                :students="28"
                :maxStudents="30"
                :price="85"
                schedule="Lun/Mer/Ven 16:00-17:30"
                status="active"
                href="{{ route('admin.courses.show', 1) }}"
            >
                <x-slot name="actions">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Attivo
                        </span>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="py-1">
                                    <a href="{{ route('admin.courses.edit', 1) }}" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Modifica
                                    </a>
                                    <a href="{{ route('admin.enrollments.index', ['course' => 1]) }}" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                        Gestisci Iscrizioni
                                    </a>
                                    <a href="{{ route('admin.schedules.show', 1) }}" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Orario Lezioni
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <button class="flex items-center w-full px-4 py-2 text-sm text-orange-700 hover:bg-orange-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Sospendi Corso
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-slot>
            </x-course-card>
            
            <x-course-card 
                title="Hip Hop Avanzato"
                description="Stili urban e street dance con coreografie moderne. Partecipazione a contest e battle."
                instructor="Prof. Marco Bianchi"
                level="Avanzato"
                :students="24"
                :maxStudents="25"
                :price="75"
                schedule="Mar/Gio 18:30-19:30"
                status="full"
                href="{{ route('admin.courses.show', 2) }}"
            >
                <x-slot name="actions">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            Completo
                        </span>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="py-1">
                                    <a href="{{ route('admin.courses.edit', 2) }}" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Modifica
                                    </a>
                                    <button class="flex items-center w-full px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Aumenta Posti
                                    </button>
                                    <button class="flex items-center w-full px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Lista d'Attesa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-slot>
            </x-course-card>
            
            <x-course-card 
                title="Danza Contemporanea"
                description="Espressione corporea e movimento fluido attraverso tecniche moderne e improvvisazione."
                instructor="Prof. Elena Conti"
                level="Intermedio"
                :students="19"
                :maxStudents="25"
                :price="95"
                schedule="Ven 19:00-20:30"
                status="active"
                href="{{ route('admin.courses.show', 3) }}"
            >
                <x-slot name="actions">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Attivo
                        </span>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </x-slot>
            </x-course-card>
            
            <!-- Add Course Card -->
            <div class="bg-gray-50/80 backdrop-blur-sm rounded-2xl shadow-lg border-2 border-dashed border-gray-300 p-6 flex flex-col items-center justify-center hover:border-rose-400 hover:bg-rose-50/50 transition-all duration-300">
                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aggiungi Nuovo Corso</h3>
                <p class="text-gray-600 text-center mb-4">Crea un nuovo corso per espandere la tua offerta formativa</p>
                <a href="{{ route('admin.courses.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Crea Corso
                </a>
            </div>
        </div>

        <!-- Course Filters and List View Toggle -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Filtri Avanzati</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Vista:</span>
                    <button class="p-2 text-rose-600 bg-rose-100 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </button>
                    <button class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option>Tutti i livelli</option>
                    <option>Principiante</option>
                    <option>Intermedio</option>
                    <option>Avanzato</option>
                    <option>Professionale</option>
                </select>
                
                <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option>Tutti gli istruttori</option>
                    <option>Prof. Martina Rossi</option>
                    <option>Prof. Marco Bianchi</option>
                    <option>Prof. Elena Conti</option>
                </select>
                
                <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option>Tutti gli stati</option>
                    <option>Attivi</option>
                    <option>In pausa</option>
                    <option>Completi</option>
                    <option>In preparazione</option>
                </select>
                
                <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option>Ordina per: Nome</option>
                    <option>Data creazione</option>
                    <option>Numero studenti</option>
                    <option>Prezzo</option>
                    <option>Istruttore</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Modal -->
    <x-modal name="bulk-actions" maxWidth="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Azioni Multiple</h3>
                <button @click="$dispatch('close-modal', 'bulk-actions')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <button class="flex items-center justify-center p-4 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Aggiorna Orari
                    </button>
                    
                    <button class="flex items-center justify-center p-4 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aggiorna Prezzi
                    </button>
                    
                    <button class="flex items-center justify-center p-4 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Sospendi Corsi
                    </button>
                    
                    <button class="flex items-center justify-center p-4 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Esporta Dati
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
</x-app-layout>