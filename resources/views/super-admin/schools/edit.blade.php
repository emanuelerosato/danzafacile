<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Scuola
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Aggiorna le informazioni della scuola
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('super-admin.schools.show', $school) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Visualizza
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
        <li class="flex items-center">
            <a href="{{ route('super-admin.schools.index') }}" class="text-gray-500 hover:text-gray-700">Scuole</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('super-admin.schools.show', $school) }}" class="text-gray-500 hover:text-gray-700">{{ $school->name }}</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Modifica</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
        <form action="{{ route('super-admin.schools.update', $school) }}" method="POST" enctype="multipart/form-data" 
              x-data="{ activeTab: 'basic', logoPreview: null }">
            @csrf
            @method('PUT')

            <!-- Progress Indicator -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Progresso Modifica</h3>
                    <span class="text-sm text-gray-500">4 sezioni</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div :class="activeTab === 'basic' ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">1</div>
                        <span class="ml-2 text-sm" :class="activeTab === 'basic' ? 'text-rose-600 font-medium' : 'text-gray-500'">Informazioni Base</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded-full">
                        <div :class="['basic', 'contact', 'settings', 'billing'].indexOf(activeTab) > 0 ? 'w-1/3' : 'w-0'" 
                             class="h-1 bg-rose-600 rounded-full transition-all duration-300"></div>
                    </div>
                    <div class="flex items-center">
                        <div :class="['contact', 'settings', 'billing'].indexOf(activeTab) >= 0 ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                        <span class="ml-2 text-sm" :class="['contact', 'settings', 'billing'].indexOf(activeTab) >= 0 ? 'text-rose-600 font-medium' : 'text-gray-500'">Contatti</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded-full">
                        <div :class="['settings', 'billing'].indexOf(activeTab) >= 0 ? 'w-2/3' : 'w-0'" 
                             class="h-1 bg-rose-600 rounded-full transition-all duration-300"></div>
                    </div>
                    <div class="flex items-center">
                        <div :class="['settings', 'billing'].indexOf(activeTab) >= 0 ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                        <span class="ml-2 text-sm" :class="['settings', 'billing'].indexOf(activeTab) >= 0 ? 'text-rose-600 font-medium' : 'text-gray-500'">Impostazioni</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded-full">
                        <div :class="activeTab === 'billing' ? 'w-full' : 'w-0'" 
                             class="h-1 bg-rose-600 rounded-full transition-all duration-300"></div>
                    </div>
                    <div class="flex items-center">
                        <div :class="activeTab === 'billing' ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">4</div>
                        <span class="ml-2 text-sm" :class="activeTab === 'billing' ? 'text-rose-600 font-medium' : 'text-gray-500'">Fatturazione</span>
                    </div>
                </div>
            </div>

            <!-- Form Sections -->
            <div class="bg-white rounded-lg shadow">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6">
                        <button type="button" @click="activeTab = 'basic'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'basic', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'basic' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Informazioni Base
                        </button>
                        <button type="button" @click="activeTab = 'contact'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'contact', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'contact' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Contatti
                        </button>
                        <button type="button" @click="activeTab = 'settings'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'settings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'settings' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Impostazioni
                        </button>
                        <button type="button" @click="activeTab = 'billing'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'billing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'billing' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Fatturazione
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Basic Information Tab -->
                    <div x-show="activeTab === 'basic'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Logo Upload -->
                            <div class="lg:col-span-1">
                                <div class="text-center">
                                    <div class="mb-4">
                                        <div x-show="!logoPreview" class="mx-auto h-32 w-32 bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-3xl">
                                            {{ strtoupper(substr($school->name, 0, 2)) }}
                                        </div>
                                        <img x-show="logoPreview" x-bind:src="logoPreview" class="mx-auto h-32 w-32 rounded-2xl object-cover">
                                    </div>
                                    <div>
                                        <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            Carica Logo
                                            <input type="file" name="logo" accept="image/*" class="sr-only" 
                                                   @change="if ($event.target.files[0]) { 
                                                       const reader = new FileReader();
                                                       reader.onload = (e) => logoPreview = e.target.result;
                                                       reader.readAsDataURL($event.target.files[0]);
                                                   }">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">PNG, JPG fino a 2MB<br>Dimensioni consigliate: 200x200px</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Info Form -->
                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome Scuola <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" id="name" value="{{ old('name', $school->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="Nome della scuola di danza" required>
                                    </div>
                                    <div>
                                        <label for="active" class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                                        <select name="active" id="active" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                            <option value="1" {{ old('active', $school->active) == 1 ? 'selected' : '' }}>Attiva</option>
                                            <option value="0" {{ old('active', $school->active) == 0 ? 'selected' : '' }}>Sospesa</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                                    <textarea name="notes" id="notes" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="Note interne sulla scuola...">{{ old('notes', $school->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Tab -->
                    <div x-show="activeTab === 'contact'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Address Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                                    Indirizzo Sede Principale
                                </h3>

                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Indirizzo <span class="text-red-500">*</span></label>
                                    <input type="text" name="address" id="address" value="{{ old('address', $school->address) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="Via, numero civico" required>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Città <span class="text-red-500">*</span></label>
                                        <input type="text" name="city" id="city" value="{{ old('city', $school->city) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="Città" required>
                                    </div>
                                    <div>
                                        <label for="province" class="block text-sm font-medium text-gray-700 mb-2">Provincia <span class="text-red-500">*</span></label>
                                        <input type="text" name="province" id="province" value="{{ old('province', $school->province) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="Sigla provincia (es. MI)" maxlength="2" required>
                                    </div>
                                </div>

                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">CAP <span class="text-red-500">*</span></label>
                                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $school->postal_code) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="CAP" maxlength="5" required>
                                </div>
                            </div>

                            <!-- Contact Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                                    Informazioni di Contatto
                                </h3>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $school->email) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="email@scuola.it" required>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefono</label>
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $school->phone) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="+39 123 4567890">
                                </div>

                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Sito Web</label>
                                    <input type="url" name="website" id="website" value="{{ old('website', $school->website) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="https://www.scuola.it">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div x-show="activeTab === 'settings'" class="space-y-6">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Impostazioni aggiuntive</h3>
                            <p class="mt-1 text-sm text-gray-500">Sezione in sviluppo</p>
                        </div>
                    </div>

                    <!-- Billing Tab -->
                    <div x-show="activeTab === 'billing'" class="space-y-6">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Fatturazione</h3>
                            <p class="mt-1 text-sm text-gray-500">Sezione in sviluppo</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between bg-white rounded-lg shadow p-6">
                <div class="flex items-center space-x-4">
                    <button type="button" @click="if (activeTab === 'contact') activeTab = 'basic';
                                                  else if (activeTab === 'settings') activeTab = 'contact';
                                                  else if (activeTab === 'billing') activeTab = 'settings';"
                            :disabled="activeTab === 'basic'"
                            :class="activeTab === 'basic' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Indietro
                    </button>

                    <button type="button" @click="if (activeTab === 'basic') activeTab = 'contact';
                                                  else if (activeTab === 'contact') activeTab = 'settings';
                                                  else if (activeTab === 'settings') activeTab = 'billing';"
                            x-show="activeTab !== 'billing'"
                            class="px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all duration-200">
                        Avanti
                        <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('super-admin.schools.show', $school) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                        Annulla
                    </a>
                    <button type="submit"
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salva Modifiche
                    </button>
                </div>
            </div>
        </form>
            </div>
        </div>
    </div>
</x-app-layout>
