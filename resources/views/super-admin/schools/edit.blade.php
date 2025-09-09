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
                <a href="{{ route('super-admin.schools.show', $school ?? 1) }}" 
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
            <a href="{{ route('super-admin.schools.show', $school ?? 1) }}" class="text-gray-500 hover:text-gray-700">Accademia Balletto Milano</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Modifica</li>
    </x-slot>

    <div class="space-y-6">
        <form action="{{ route('super-admin.schools.update', $school ?? 1) }}" method="POST" enctype="multipart/form-data" 
              x-data="{ activeTab: 'basic', logoPreview: null }">
            @csrf
            @method('PUT')

            <!-- Progress Indicator -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
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
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
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
                                        <div x-show="!logoPreview" class="mx-auto h-32 w-32 bg-gradient-to-r from-rose-400 to-purple-500 rounded-2xl flex items-center justify-center text-white font-bold text-3xl">
                                            AB
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
                                        <x-form-input 
                                            label="Nome Scuola *"
                                            name="name"
                                            type="text"
                                            value="Accademia Balletto Milano"
                                            placeholder="Nome della scuola di danza"
                                            required />
                                    </div>
                                    <div>
                                        <x-form-input 
                                            label="Codice Identificativo"
                                            name="code"
                                            type="text"
                                            value="SCH001"
                                            placeholder="Codice univoco"
                                            readonly />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione</label>
                                    <textarea name="description" rows="4" 
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                              placeholder="Descrizione della scuola, specializzazioni, filosofia...">La più prestigiosa accademia di balletto di Milano, fondata nel 2020. Offriamo corsi per tutti i livelli, dalla danza classica a quella moderna, con istruttori qualificati e spazi all'avanguardia.</textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Anno Fondazione</label>
                                        <select name="founded_year" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona anno</option>
                                            @for ($year = date('Y'); $year >= 1900; $year--)
                                                <option value="{{ $year }}" {{ $year == 2020 ? 'selected' : '' }}>{{ $year }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                                        <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="active">Attiva</option>
                                            <option value="suspended">Sospesa</option>
                                            <option value="pending">In Revisione</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Scuola</label>
                                        <select name="school_type" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="accademia">Accademia</option>
                                            <option value="scuola">Scuola</option>
                                            <option value="centro">Centro Danza</option>
                                            <option value="studio">Studio</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Specializzazioni</label>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        @php
                                            $specializations = [
                                                'balletto_classico' => 'Balletto Classico',
                                                'danza_moderna' => 'Danza Moderna',
                                                'danza_contemporanea' => 'Danza Contemporanea',
                                                'hip_hop' => 'Hip Hop',
                                                'latin' => 'Danze Latine',
                                                'jazz' => 'Jazz Dance'
                                            ];
                                        @endphp
                                        @foreach ($specializations as $key => $label)
                                            <div class="flex items-center">
                                                <input type="checkbox" id="{{ $key }}" name="specializations[]" value="{{ $key }}"
                                                       class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded"
                                                       {{ in_array($key, ['balletto_classico', 'danza_moderna', 'hip_hop']) ? 'checked' : '' }}>
                                                <label for="{{ $key }}" class="ml-2 text-sm text-gray-900">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
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
                                    <x-form-input 
                                        label="Indirizzo *"
                                        name="address"
                                        type="text"
                                        value="Via della Danza, 123"
                                        placeholder="Via, numero civico"
                                        required />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-form-input 
                                            label="Città *"
                                            name="city"
                                            type="text"
                                            value="Milano"
                                            placeholder="Milano"
                                            required />
                                    </div>
                                    <div>
                                        <x-form-input 
                                            label="Provincia *"
                                            name="province"
                                            type="text"
                                            value="MI"
                                            placeholder="MI"
                                            required />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-form-input 
                                            label="CAP *"
                                            name="postal_code"
                                            type="text"
                                            value="20121"
                                            placeholder="20121"
                                            required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Regione *</label>
                                        <select name="region" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona regione</option>
                                            <option value="lombardia" selected>Lombardia</option>
                                            <option value="lazio">Lazio</option>
                                            <option value="campania">Campania</option>
                                            <option value="toscana">Toscana</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                                    Informazioni di Contatto
                                </h3>
                                
                                <div>
                                    <x-form-input 
                                        label="Email Principale *"
                                        name="email"
                                        type="email"
                                        value="info@accademiaballo.mi.it"
                                        placeholder="email@scuola.it"
                                        required />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="Email Amministrativa"
                                        name="admin_email"
                                        type="email"
                                        value="admin@accademiaballo.mi.it"
                                        placeholder="admin@scuola.it" />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="Telefono Principale *"
                                        name="phone"
                                        type="tel"
                                        value="+39 02 1234567"
                                        placeholder="+39 02 1234567"
                                        required />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="Cellulare/WhatsApp"
                                        name="mobile"
                                        type="tel"
                                        value="+39 333 1234567"
                                        placeholder="+39 333 1234567" />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="Sito Web"
                                        name="website"
                                        type="url"
                                        value="https://www.accademiaballo.mi.it"
                                        placeholder="https://www.scuola.it" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Social Media</label>
                                    <div class="space-y-3">
                                        <x-form-input 
                                            label="Facebook"
                                            name="facebook"
                                            type="url"
                                            value="https://facebook.com/accademiaballo"
                                            placeholder="https://facebook.com/pagina" />
                                        <x-form-input 
                                            label="Instagram"
                                            name="instagram"
                                            type="url"
                                            value="https://instagram.com/accademiaballo"
                                            placeholder="https://instagram.com/profilo" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div x-show="activeTab === 'settings'" class="space-y-8">
                        <!-- Operational Settings -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">
                                Impostazioni Operative
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Orario Apertura</label>
                                    <input type="time" name="opening_time" value="08:00" 
                                           class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Orario Chiusura</label>
                                    <input type="time" name="closing_time" value="22:00"
                                           class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Giorni di Apertura</label>
                                <div class="grid grid-cols-7 gap-2">
                                    @php
                                        $days = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
                                        $dayValues = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                        $openDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                                    @endphp
                                    @foreach ($days as $index => $day)
                                        <div class="text-center">
                                            <label class="block text-xs text-gray-600 mb-1">{{ $day }}</label>
                                            <input type="checkbox" name="opening_days[]" value="{{ $dayValues[$index] }}"
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded"
                                                   {{ in_array($dayValues[$index], $openDays) ? 'checked' : '' }}>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">
                                Impostazioni Sistema
                            </h3>
                            <div class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Iscrizioni Online</label>
                                        <p class="text-sm text-gray-500">Permetti agli studenti di iscriversi direttamente online</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="online_enrollment" value="1" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Pagamenti Online</label>
                                        <p class="text-sm text-gray-500">Abilita i pagamenti tramite carta di credito e PayPal</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="online_payments" value="1" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Notifiche Email</label>
                                        <p class="text-sm text-gray-500">Invia email automatiche per iscrizioni e pagamenti</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="email_notifications" value="1" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">App Mobile</label>
                                        <p class="text-sm text-gray-500">Abilita l'accesso tramite app mobile per studenti</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="mobile_app" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Tab -->
                    <div x-show="activeTab === 'billing'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Billing Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                                    Informazioni Fatturazione
                                </h3>
                                
                                <div>
                                    <x-form-input 
                                        label="Ragione Sociale *"
                                        name="billing_company"
                                        type="text"
                                        value="Accademia Balletto Milano S.R.L."
                                        placeholder="Nome completo dell'azienda"
                                        required />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="Partita IVA *"
                                        name="vat_number"
                                        type="text"
                                        value="IT12345678901"
                                        placeholder="IT12345678901"
                                        required />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="Codice Fiscale"
                                        name="tax_code"
                                        type="text"
                                        value="RSSMRA80A01F205X"
                                        placeholder="Codice fiscale" />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="PEC"
                                        name="pec_email"
                                        type="email"
                                        value="accademia@pec.it"
                                        placeholder="indirizzo@pec.it" />
                                </div>

                                <div>
                                    <x-form-input 
                                        label="Codice SDI"
                                        name="sdi_code"
                                        type="text"
                                        value="ABCD123"
                                        placeholder="Codice per fatturazione elettronica" />
                                </div>
                            </div>

                            <!-- Payment Settings -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                                    Impostazioni Pagamento
                                </h3>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Piano Subscription</label>
                                    <select name="subscription_plan" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        <option value="basic">Basic - €29/mese</option>
                                        <option value="professional" selected>Professional - €59/mese</option>
                                        <option value="premium">Premium - €99/mese</option>
                                        <option value="enterprise">Enterprise - €199/mese</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Modalità Fatturazione</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" id="monthly" name="billing_cycle" value="monthly" 
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                                            <label for="monthly" class="ml-2 text-sm text-gray-900">Mensile</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="yearly" name="billing_cycle" value="yearly" checked
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                                            <label for="yearly" class="ml-2 text-sm text-gray-900">Annuale (sconto 15%)</label>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prossimo Pagamento</label>
                                    <div class="p-4 bg-green-50 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-green-800">15 Gennaio 2025</p>
                                                <p class="text-sm text-green-600">€501.30 (IVA inclusa)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Metodo di Pagamento</label>
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center text-white text-xs font-bold mr-3">
                                                    VISA
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">**** **** **** 4532</p>
                                                    <p class="text-sm text-gray-500">Scade 12/26</p>
                                                </div>
                                            </div>
                                            <button type="button" class="text-sm text-rose-600 hover:text-rose-700">
                                                Cambia
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
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
                    <a href="{{ route('super-admin.schools.show', $school ?? 1) }}" 
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
</x-app-layout>