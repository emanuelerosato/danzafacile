<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Scuole
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Tutte le scuole registrate nel sistema
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="$dispatch('open-modal', 'import-schools')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Importa
                </button>
                <a href="{{ route('super-admin.schools.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuova Scuola
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
        <li class="text-gray-900 font-medium">Scuole</li>
    </x-slot>

    <div class="space-y-6">
        <!-- Filters and Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stats-card 
                title="Totale Scuole"
                :value="$schools->total()"
                icon="office-building"
                color="rose"
                subtitle="Tutte le scuole"
            />
            
            <x-stats-card 
                title="Scuole Attive"
                :value="$schools->where('active', true)->count()"
                icon="check-circle"
                color="green"
                subtitle="{{ round(($schools->where('active', true)->count() / max(1, $schools->total())) * 100) }}% del totale"
            />
            
            <x-stats-card 
                title="Città Coperte"
                :value="$cities->count()"
                icon="star"
                color="purple"
                subtitle="Presenza territoriale"
            />
            
            <x-stats-card 
                title="Media Utenti/Scuola"
                :value="round($schools->avg('users_count') ?? 0)"
                icon="currency-dollar"
                color="blue"
                subtitle="Utenti per scuola"
            />
        </div>

        <!-- Schools Table -->
        <div x-data="schoolsDataTable()" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <!-- Table Header with Filters -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Search Input -->
                        <div class="relative max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input x-model="search" 
                                   type="text" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-rose-500 focus:border-rose-500" 
                                   placeholder="Cerca scuole...">
                        </div>
                        
                        <!-- Status Filter -->
                        <select x-model="statusFilter" class="block w-40 px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-rose-500 focus:border-rose-500">
                            <option value="">Tutti gli stati</option>
                            <option value="active">Attive</option>
                            <option value="inactive">Non attive</option>
                        </select>
                        
                        <!-- City Filter -->
                        <select x-model="cityFilter" class="block w-40 px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-rose-500 focus:border-rose-500">
                            <option value="">Tutte le città</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Export Button -->
                        <button @click="exportData()" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Esporta CSV
                        </button>
                        
                        <!-- Bulk Actions -->
                        <div class="relative" x-show="selectedItems.length > 0">
                            <button @click="bulkMenuOpen = !bulkMenuOpen" 
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200">
                                <span x-text="`${selectedItems.length} selezionati`"></span>
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <!-- Bulk Actions Menu -->
                            <div x-show="bulkMenuOpen" @click.away="bulkMenuOpen = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="py-1">
                                    <button @click="bulkAction('activate')" 
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-900">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                        </svg>
                                        Attiva
                                    </button>
                                    <button @click="bulkAction('deactivate')" 
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-900">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                        </svg>
                                        Disattiva
                                    </button>
                                    <button @click="bulkAction('delete')" 
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Elimina
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" 
                                       @change="toggleAll($event.target.checked)"
                                       :checked="allSelected"
                                       class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" 
                                @click="sortBy('name')">
                                <div class="flex items-center">
                                    Scuola
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" 
                                @click="sortBy('city')">
                                <div class="flex items-center">
                                    Città
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contatti
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statistiche
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" 
                                @click="sortBy('active')">
                                <div class="flex items-center">
                                    Stato
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Azioni
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(school, index) in filteredItems" :key="school.id">
                            <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': selectedItems.includes(school.id) }">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           :value="school.id"
                                           @change="toggleSelection(school.id, $event.target.checked)"
                                           :checked="selectedItems.includes(school.id)"
                                           class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold">
                                                <span x-text="school.name.substring(0, 2).toUpperCase()"></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900" x-text="school.name"></div>
                                            <div class="text-sm text-gray-500" x-text="school.description ? school.description.substring(0, 50) + '...' : 'Nessuna descrizione'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="school.city"></div>
                                    <div class="text-sm text-gray-500" x-text="school.postal_code"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div x-show="school.phone" class="flex items-center mb-1">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <span x-text="school.phone"></span>
                                        </div>
                                        <div x-show="school.email" class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span x-text="school.email"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            <span x-text="school.users_count || 0"></span> utenti
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                            <span x-text="school.courses_count || 0"></span> corsi
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button @click="toggleStatus(school.id)" 
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200"
                                            :class="school.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        <span class="w-2 h-2 mr-2 rounded-full" :class="school.active ? 'bg-green-400' : 'bg-red-400'"></span>
                                        <span x-text="school.active ? 'Attiva' : 'Non attiva'"></span>
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a :href="`/super-admin/schools/${school.id}`" 
                                           class="text-rose-600 hover:text-rose-900 p-1 rounded-full hover:bg-rose-100" title="Visualizza dettagli">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a :href="`/super-admin/schools/${school.id}/edit`" 
                                           class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-100" title="Modifica">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button @click="exportSchool(school.id)" 
                                                class="text-green-600 hover:text-green-900 p-1 rounded-full hover:bg-green-100" title="Esporta">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </button>
                                        <button @click="deleteSchool(school.id)" 
                                                class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-100" title="Elimina">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                
                <!-- Empty State -->
                <div x-show="filteredItems.length === 0" class="text-center py-12">
                    <div class="flex flex-col items-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna scuola trovata</h3>
                        <p class="mt-1 text-sm text-gray-500">Non ci sono scuole che corrispondono ai filtri selezionati.</p>
                        <div class="mt-6">
                            <a href="{{ route('super-admin.schools.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Aggiungi prima scuola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando 
                        <span class="font-medium" x-text="((currentPage - 1) * itemsPerPage) + 1"></span>
                        a 
                        <span class="font-medium" x-text="Math.min(currentPage * itemsPerPage, filteredItems.length)"></span> 
                        di 
                        <span class="font-medium" x-text="filteredItems.length"></span> 
                        risultati
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="previousPage()" :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Precedente
                        </button>
                        <div class="flex items-center space-x-1">
                            <template x-for="page in Array.from({length: totalPages}, (_, i) => i + 1).slice(Math.max(0, currentPage - 3), Math.min(totalPages, currentPage + 2))" :key="page">
                                <button @click="goToPage(page)" 
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
                                        :class="page === currentPage ? 'bg-rose-600 text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'"
                                        x-text="page"></button>
                            </template>
                        </div>
                        <button @click="nextPage()" :disabled="currentPage === totalPages"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Successiva
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function schoolsDataTable() {
        return {
            items: @json($schools->items()),
            search: '',
            statusFilter: '',
            cityFilter: '',
            sortField: 'name',
            sortDirection: 'asc',
            selectedItems: [],
            bulkMenuOpen: false,
            currentPage: {{ $schools->currentPage() }},
            itemsPerPage: {{ $schools->perPage() }},
            loading: false,

            get filteredItems() {
                let filtered = this.items;
                
                // Search filter
                if (this.search) {
                    filtered = filtered.filter(school => 
                        school.name.toLowerCase().includes(this.search.toLowerCase()) ||
                        school.city.toLowerCase().includes(this.search.toLowerCase()) ||
                        (school.email && school.email.toLowerCase().includes(this.search.toLowerCase()))
                    );
                }
                
                // Status filter
                if (this.statusFilter) {
                    if (this.statusFilter === 'active') {
                        filtered = filtered.filter(school => school.active);
                    } else if (this.statusFilter === 'inactive') {
                        filtered = filtered.filter(school => !school.active);
                    }
                }
                
                // City filter
                if (this.cityFilter) {
                    filtered = filtered.filter(school => school.city === this.cityFilter);
                }
                
                // Sort
                filtered.sort((a, b) => {
                    let aVal = a[this.sortField];
                    let bVal = b[this.sortField];
                    
                    if (typeof aVal === 'string') {
                        aVal = aVal.toLowerCase();
                        bVal = bVal.toLowerCase();
                    }
                    
                    if (this.sortDirection === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });
                
                return filtered;
            },
            
            get totalPages() {
                return Math.ceil(this.filteredItems.length / this.itemsPerPage);
            },
            
            get allSelected() {
                return this.items.length > 0 && this.selectedItems.length === this.items.length;
            },
            
            sortBy(field) {
                if (this.sortField === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortField = field;
                    this.sortDirection = 'asc';
                }
            },
            
            toggleSelection(itemId, checked) {
                if (checked) {
                    if (!this.selectedItems.includes(itemId)) {
                        this.selectedItems.push(itemId);
                    }
                } else {
                    this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                }
            },
            
            toggleAll(checked) {
                if (checked) {
                    this.selectedItems = this.items.map(item => item.id);
                } else {
                    this.selectedItems = [];
                }
            },
            
            async toggleStatus(schoolId) {
                this.loading = true;
                try {
                    const response = await fetch(`/super-admin/schools/${schoolId}/toggle-active`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update local item
                        const school = this.items.find(s => s.id === schoolId);
                        if (school) {
                            school.active = data.status;
                        }
                        this.showToast(data.message, 'success');
                    } else {
                        this.showToast(data.message || 'Errore durante l\'aggiornamento', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showToast('Errore di connessione', 'error');
                } finally {
                    this.loading = false;
                }
            },
            
            async bulkAction(action) {
                if (this.selectedItems.length === 0) return;
                
                this.loading = true;
                this.bulkMenuOpen = false;
                
                try {
                    const response = await fetch('/super-admin/schools/bulk-action', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action: action,
                            school_ids: this.selectedItems
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Reload page to refresh data
                        window.location.reload();
                    } else {
                        this.showToast(data.message || 'Errore durante l\'operazione', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showToast('Errore di connessione', 'error');
                } finally {
                    this.loading = false;
                }
            },
            
            exportData() {
                window.open('{{ route('super-admin.schools.export-all') }}', '_blank');
            },
            
            exportSchool(schoolId) {
                window.open(`/super-admin/schools/${schoolId}/export`, '_blank');
            },
            
            async deleteSchool(schoolId) {
                if (!confirm('Sei sicuro di voler eliminare questa scuola? L\'operazione non può essere annullata.')) {
                    return;
                }
                
                this.loading = true;
                
                try {
                    const response = await fetch(`/super-admin/schools/${schoolId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove from local items
                        this.items = this.items.filter(s => s.id !== schoolId);
                        this.selectedItems = this.selectedItems.filter(id => id !== schoolId);
                        this.showToast(data.message, 'success');
                    } else {
                        this.showToast(data.message || 'Errore durante l\'eliminazione', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showToast('Errore di connessione', 'error');
                } finally {
                    this.loading = false;
                }
            },
            
            previousPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },
            
            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },
            
            goToPage(page) {
                this.currentPage = page;
            },
            
            showToast(message, type = 'info') {
                // Simple toast implementation
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg text-white z-50 ${
                    type === 'success' ? 'bg-green-600' : 
                    type === 'error' ? 'bg-red-600' : 
                    'bg-blue-600'
                }`;
                toast.textContent = message;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        }
    }
    </script>

    <!-- Import Modal -->
    <x-modal name="import-schools" maxWidth="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Importa Scuole</h3>
                <button @click="$dispatch('close-modal', 'import-schools')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">
                    Carica un file CSV o Excel con i dati delle scuole da importare.
                </p>
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="mt-4">
                        <label class="cursor-pointer">
                            <span class="mt-2 block text-sm font-medium text-gray-900">
                                Clicca per caricare o trascina qui i file
                            </span>
                            <input type="file" class="sr-only" accept=".csv,.xlsx,.xls">
                        </label>
                        <p class="mt-1 text-xs text-gray-500">CSV, XLS, XLSX fino a 10MB</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <a href="#" class="text-sm text-rose-600 hover:text-rose-700">
                    Scarica template di esempio
                </a>
                <div class="flex space-x-3">
                    <button @click="$dispatch('close-modal', 'import-schools')" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Annulla
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        Importa
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
</x-app-layout>