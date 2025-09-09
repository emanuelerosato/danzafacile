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
                :value="12"
                icon="office-building"
                color="rose"
                :change="8"
                changeType="increase"
            />
            
            <x-stats-card 
                title="Scuole Attive"
                :value="11"
                icon="check-circle"
                color="green"
                subtitle="91% del totale"
            />
            
            <x-stats-card 
                title="Nuove Questo Mese"
                :value="3"
                icon="star"
                color="purple"
                :change="50"
                changeType="increase"
            />
            
            <x-stats-card 
                title="Ricavo Totale"
                :value="'€45,290'"
                icon="currency-dollar"
                color="blue"
                subtitle="Media mensile"
            />
        </div>

        <!-- Schools Table -->
        <x-data-table 
            :headers="[
                ['label' => 'Scuola', 'key' => 'name', 'sortable' => true],
                ['label' => 'Proprietario', 'key' => 'owner', 'sortable' => true],
                ['label' => 'Città', 'key' => 'city', 'sortable' => true],
                ['label' => 'Studenti', 'key' => 'students', 'sortable' => true],
                ['label' => 'Corsi', 'key' => 'courses', 'sortable' => true],
                ['label' => 'Ricavi', 'key' => 'revenue', 'sortable' => true],
                ['label' => 'Stato', 'key' => 'status', 'sortable' => true],
                ['label' => 'Azioni', 'key' => 'actions']
            ]"
            searchPlaceholder="Cerca scuole..."
            :items="[
                [
                    'id' => 1,
                    'name' => 'Accademia Balletto Milano',
                    'owner' => 'Maria Rossi',
                    'city' => 'Milano',
                    'students' => 156,
                    'courses' => 12,
                    'revenue' => '€12,450',
                    'status' => 'active',
                    'created_at' => '2024-01-15'
                ],
                [
                    'id' => 2,
                    'name' => 'Danza Moderna Roma',
                    'owner' => 'Giuseppe Verdi',
                    'city' => 'Roma',
                    'students' => 134,
                    'courses' => 10,
                    'revenue' => '€10,890',
                    'status' => 'active',
                    'created_at' => '2024-02-20'
                ],
                [
                    'id' => 3,
                    'name' => 'Centro Danza Firenze',
                    'owner' => 'Elena Bianchi',
                    'city' => 'Firenze',
                    'students' => 98,
                    'courses' => 8,
                    'revenue' => '€8,790',
                    'status' => 'active',
                    'created_at' => '2024-03-10'
                ]
            ]"
        >
            <x-slot name="actions">
                <div class="flex items-center space-x-2">
                    <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option>Tutte le città</option>
                        <option>Milano</option>
                        <option>Roma</option>
                        <option>Firenze</option>
                        <option>Napoli</option>
                    </select>
                    <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option>Tutti gli stati</option>
                        <option>Attive</option>
                        <option>Sospese</option>
                        <option>In revisione</option>
                    </select>
                </div>
            </x-slot>

            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold">
                                <span x-text="item.name.substring(0, 2).toUpperCase()"></span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900" x-text="item.name"></div>
                            <div class="text-sm text-gray-500">
                                Registrata il <span x-text="new Date(item.created_at).toLocaleDateString('it-IT')"></span>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900" x-text="item.owner"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900" x-text="item.city"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900" x-text="item.students"></div>
                    <div class="text-xs text-gray-500">studenti</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900" x-text="item.courses"></div>
                    <div class="text-xs text-gray-500">corsi attivi</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-green-600" x-text="item.revenue"></div>
                    <div class="text-xs text-gray-500">mensile</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                        'bg-green-100 text-green-800': item.status === 'active',
                        'bg-red-100 text-red-800': item.status === 'suspended',
                        'bg-yellow-100 text-yellow-800': item.status === 'pending'
                    }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                        <span x-text="item.status === 'active' ? 'Attiva' : item.status === 'suspended' ? 'Sospesa' : 'In Revisione'"></span>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                        <a :href="`/super-admin/schools/${item.id}`" 
                           class="text-rose-600 hover:text-rose-900 p-1 rounded-full hover:bg-rose-100"
                           title="Visualizza dettagli">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a :href="`/super-admin/schools/${item.id}/edit`" 
                           class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-100"
                           title="Modifica">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <button @click="$dispatch('open-modal', 'delete-school-' + item.id)" 
                                class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-100"
                                title="Elimina">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="py-1">
                                    <a :href="`/super-admin/schools/${item.id}/login`" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        Accedi come Admin
                                    </a>
                                    <a :href="`/super-admin/schools/${item.id}/reports`" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        Vedi Report
                                    </a>
                                    <button @click="alert('Funzione non ancora implementata')"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                                        </svg>
                                        Sospendi Attività
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </x-data-table>
    </div>

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