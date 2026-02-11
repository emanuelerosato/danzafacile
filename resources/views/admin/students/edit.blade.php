<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Studente
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione modifica della tua scuola
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
        <li class="text-gray-900 font-medium">Modifica</li>
    </x-slot>




<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.students.show', $student) }}"
               class="inline-flex items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex items-center space-x-4">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        @php
                            // SENIOR FIX: Defensive initials extraction with fallback
                            $initials = '';
                            if ($student->first_name && $student->last_name) {
                                $initials = strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1));
                            } elseif ($student->full_name) {
                                // Fallback: extract initials from full_name
                                $nameParts = explode(' ', trim($student->full_name));
                                $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr($nameParts[1] ?? $nameParts[0] ?? '', 0, 1));
                            } else {
                                $initials = '??';
                            }
                        @endphp
                        {{ $initials }}
                    </div>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900">Modifica Studente</h1>
                    <p class="text-gray-600">{{ $student->full_name ?: $student->name }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <a href="{{ route('admin.students.show', $student) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Visualizza
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200" x-data="studentEditForm">
        <form @submit.prevent="submitForm" class="p-6 space-y-8">
            @csrf
            @method('PUT')

            <!-- Personal Information -->
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Informazioni Personali
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Dati anagrafici dello studente</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="first_name"
                               id="first_name"
                               x-model="form.first_name"
                               @input="updateFullName"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="Nome dello studente">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.first_name" class="mt-1 text-sm text-red-600" x-text="errors.first_name"></div>
                        <!-- Server-side Laravel error -->
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Cognome <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="last_name"
                               id="last_name"
                               x-model="form.last_name"
                               @input="updateFullName"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="Cognome dello studente">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.last_name" class="mt-1 text-sm text-red-600" x-text="errors.last_name"></div>
                        <!-- Server-side Laravel error -->
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Full Name (auto-generated) -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               x-model="form.name"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors bg-gray-50"
                               placeholder="Nome completo (generato automaticamente)"
                               readonly>
                        <p class="mt-1 text-sm text-gray-500">Questo campo viene generato automaticamente dal nome e cognome</p>
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.name" class="mt-1 text-sm text-red-600" x-text="errors.name"></div>
                        <!-- Server-side Laravel error -->
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               id="email"
                               x-model="form.email"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="email@esempio.com">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></div>
                        <!-- Server-side Laravel error -->
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefono
                        </label>
                        <input type="tel"
                               name="phone"
                               id="phone"
                               x-model="form.phone"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="+39 123 456 7890">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.phone" class="mt-1 text-sm text-red-600" x-text="errors.phone"></div>
                        <!-- Server-side Laravel error -->
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Codice Fiscale -->
                    <div>
                        <label for="codice_fiscale" class="block text-sm font-medium text-gray-700 mb-2">
                            Codice Fiscale <span class="text-red-500">*</span>
                        </label>
                        {{-- BUGFIX: Removed x-secure-input component to avoid Alpine.js x-data conflict --}}
                        {{-- Using direct input with @input handler for proper two-way binding --}}
                        <input
                            type="text"
                            name="codice_fiscale"
                            id="codice_fiscale"
                            :value="form.codice_fiscale"
                            @input="form.codice_fiscale = $event.target.value.toUpperCase()"
                            placeholder="RSSMRA80A01H501X"
                            required
                            maxlength="16"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 uppercase"
                            pattern="[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]"
                            title="Codice fiscale italiano (es: RSSMRA80A01H501X)" />
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.codice_fiscale" class="mt-1 text-sm text-red-600" x-text="errors.codice_fiscale"></div>
                        <!-- Server-side Laravel error -->
                        @error('codice_fiscale')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Inserisci il codice fiscale italiano (16 caratteri)</p>
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                            Data di Nascita <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               name="date_of_birth"
                               id="date_of_birth"
                               x-model="form.date_of_birth"
                               @change="checkIfMinor"
                               required
                               :max="maxDate"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.date_of_birth" class="mt-1 text-sm text-red-600" x-text="errors.date_of_birth"></div>
                        <!-- Server-side Laravel error -->
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SENIOR FIX: Task #4 - Is Minor Checkbox -->
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_minor"
                                   id="is_minor"
                                   x-model="form.is_minor"
                                   @change="checkMinorStatus()"
                                   value="1"
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="is_minor" class="ml-2 block text-sm font-medium text-gray-700">
                                È minorenne (< 18 anni)
                            </label>
                        </div>
                        <p class="ml-6 mt-1 text-xs text-gray-500">
                            Se selezionato, verranno richiesti i dati del genitore/tutore legale
                        </p>
                    </div>
                </div>
            </div>

            <!-- SENIOR FIX: Task #4 - Guardian Information (Conditional) -->
            <div x-show="form.is_minor" x-transition class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Dati Genitore/Tutore Legale
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Informazioni obbligatorie per studenti minorenni (fatturazione e comunicazioni)
                    </p>
                </div>

                <!-- Fix #7: Info box guardian fields obbligatori -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Attenzione:</strong> I seguenti campi sono obbligatori per studenti minorenni (età inferiore a 18 anni).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Guardian First Name -->
                    <div>
                        <label for="guardian_first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Genitore <span class="text-red-500" x-show="form.is_minor">*</span>
                        </label>
                        <input type="text"
                               name="guardian_first_name"
                               id="guardian_first_name"
                               x-model="form.guardian_first_name"
                               :required="form.is_minor"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="Nome del genitore/tutore">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.guardian_first_name" class="mt-1 text-sm text-red-600" x-text="errors.guardian_first_name"></div>
                        <!-- Server-side Laravel error -->
                        @error('guardian_first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Guardian Last Name -->
                    <div>
                        <label for="guardian_last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Cognome Genitore <span class="text-red-500" x-show="form.is_minor">*</span>
                        </label>
                        <input type="text"
                               name="guardian_last_name"
                               id="guardian_last_name"
                               x-model="form.guardian_last_name"
                               :required="form.is_minor"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="Cognome del genitore/tutore">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.guardian_last_name" class="mt-1 text-sm text-red-600" x-text="errors.guardian_last_name"></div>
                        <!-- Server-side Laravel error -->
                        @error('guardian_last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Guardian Fiscal Code -->
                    <div>
                        <label for="guardian_fiscal_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Codice Fiscale Genitore <span class="text-red-500" x-show="form.is_minor">*</span>
                        </label>
                        <input type="text"
                               name="guardian_fiscal_code"
                               id="guardian_fiscal_code"
                               :value="form.guardian_fiscal_code"
                               @input="form.guardian_fiscal_code = $event.target.value.toUpperCase()"
                               :required="form.is_minor"
                               maxlength="16"
                               pattern="[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]"
                               title="Codice fiscale italiano (es: RSSMRA80A01H501X)"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 uppercase"
                               placeholder="RSSMRA80A01H501X">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.guardian_fiscal_code" class="mt-1 text-sm text-red-600" x-text="errors.guardian_fiscal_code"></div>
                        <!-- Server-side Laravel error -->
                        @error('guardian_fiscal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Codice fiscale italiano (16 caratteri) - per fatturazione</p>
                    </div>

                    <!-- Guardian Email -->
                    <div>
                        <label for="guardian_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Genitore <span class="text-red-500" x-show="form.is_minor">*</span>
                        </label>
                        <input type="email"
                               name="guardian_email"
                               id="guardian_email"
                               x-model="form.guardian_email"
                               :required="form.is_minor"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="email@esempio.it">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.guardian_email" class="mt-1 text-sm text-red-600" x-text="errors.guardian_email"></div>
                        <!-- Server-side Laravel error -->
                        @error('guardian_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Email per comunicazioni e invio fatture</p>
                    </div>

                    <!-- Guardian Phone -->
                    <div class="md:col-span-2">
                        <label for="guardian_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefono Genitore <span class="text-red-500" x-show="form.is_minor">*</span>
                        </label>
                        <input type="tel"
                               name="guardian_phone"
                               id="guardian_phone"
                               x-model="form.guardian_phone"
                               :required="form.is_minor"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="+39 333 1234567">
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.guardian_phone" class="mt-1 text-sm text-red-600" x-text="errors.guardian_phone"></div>
                        <!-- Server-side Laravel error -->
                        @error('guardian_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Personal Information (continued) -->
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Indirizzo
                        </label>
                        <textarea name="address"
                                  id="address"
                                  x-model="form.address"
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors resize-none"
                                  placeholder="Via, numero civico, città, CAP"></textarea>
                        <!-- Client-side Alpine.js error -->
                        <div x-show="errors.address" class="mt-1 text-sm text-red-600" x-text="errors.address"></div>
                        <!-- Server-side Laravel error -->
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Emergency Contacts -->
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Contatti di Emergenza
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Informazioni per contatti di emergenza</p>
                </div>

                <div>
                    <label for="emergency_contact" class="block text-sm font-medium text-gray-700 mb-2">
                        Contatto di Emergenza
                    </label>
                    <textarea name="emergency_contact"
                              id="emergency_contact"
                              x-model="form.emergency_contact"
                              rows="2"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors resize-none"
                              placeholder="Nome, cognome e numero di telefono del contatto di emergenza"></textarea>
                    <!-- Client-side Alpine.js error -->
                    <div x-show="errors.emergency_contact" class="mt-1 text-sm text-red-600" x-text="errors.emergency_contact"></div>
                    <!-- Server-side Laravel error -->
                    @error('emergency_contact')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Medical Information -->
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Informazioni Mediche
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Eventuali condizioni mediche o note particolari</p>
                </div>

                <div>
                    <label for="medical_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Note Mediche
                    </label>
                    <textarea name="medical_notes"
                              id="medical_notes"
                              x-model="form.medical_notes"
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors resize-none"
                              placeholder="Allergie, condizioni mediche particolari, farmaci assunti, ecc."></textarea>
                    <!-- Client-side Alpine.js error -->
                    <div x-show="errors.medical_notes" class="mt-1 text-sm text-red-600" x-text="errors.medical_notes"></div>
                    <!-- Server-side Laravel error -->
                    @error('medical_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Account Settings -->
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Impostazioni Account
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Stato e configurazioni dell'account</p>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="active" class="text-sm font-medium text-gray-700">
                                Stato Account
                            </label>
                            <p class="text-sm text-gray-500">Attiva o disattiva l'account dello studente</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="active"
                                   id="active"
                                   x-model="form.active"
                                   role="switch"
                                   :aria-checked="form.active.toString()"
                                   aria-label="Attiva o disattiva account studente"
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                    <a href="{{ route('admin.students.show', $student) }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Annulla
                    </a>

                    <!-- Danger Zone - Fix #10: Added loading state -->
                    <button type="button"
                            @click="if(confirm('Sei sicuro di voler eliminare questo studente? Questa azione è irreversibile.')) { deleteStudent() }"
                            :disabled="deleting"
                            :class="{ 'opacity-50 cursor-not-allowed': deleting }"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <!-- Spinner icon when deleting -->
                        <svg x-show="deleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <!-- Trash icon when not deleting -->
                        <svg x-show="!deleting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span x-text="deleting ? 'Eliminazione...' : 'Elimina'"></span>
                    </button>
                </div>

                <button type="submit"
                        :disabled="loading"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="loading ? 'Salvataggio in corso...' : 'Salva Modifiche'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Corsi Iscritti Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Corsi Iscritti</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Lista dei corsi a cui lo studente è iscritto
                    </p>
                </div>
                @if($student->enrollments->count() > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $student->enrollments->count() }} {{ Str::plural('corso', $student->enrollments->count()) }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Quick Add Enrollment Form -->
        <div class="border-b border-gray-200 bg-gray-50">
            <div class="p-4">
                <button @click="showAddForm = !showAddForm"
                        type="button"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg x-show="!showAddForm" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <svg x-show="showAddForm" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span x-text="showAddForm ? 'Annulla' : 'Aggiungi Iscrizione Rapida'"></span>
                </button>

                <!-- Inline Add Form -->
                <div x-show="showAddForm"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="mt-4 bg-white border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Corso Select -->
                        <div class="md:col-span-2">
                            <label for="quick_course_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Corso <span class="text-red-500">*</span>
                            </label>
                            <select x-model="newEnrollment.course_id"
                                    id="quick_course_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Seleziona un corso...</option>
                                @foreach($availableCourses as $course)
                                    <option value="{{ $course->id }}">
                                        {{ $course->name }}
                                        @if($course->start_date)
                                            - {{ $course->start_date->format('d/m/Y') }}
                                        @endif
                                        @if($course->max_students)
                                            ({{ $course->enrollments()->where('status', 'active')->count() }}/{{ $course->max_students }} posti)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if(count($availableCourses) === 0)
                                <p class="mt-1 text-sm text-gray-500">
                                    Nessun corso disponibile. Lo studente è già iscritto a tutti i corsi attivi.
                                </p>
                            @endif
                        </div>

                        <!-- Status Select -->
                        <div>
                            <label for="quick_status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select x-model="newEnrollment.status"
                                    id="quick_status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="active">Attiva</option>
                                <option value="pending">In Attesa</option>
                                <option value="suspended">Sospesa</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4 flex justify-end">
                        <button @click="addEnrollment()"
                                type="button"
                                :disabled="addingEnrollment || !newEnrollment.course_id"
                                :class="{ 'opacity-50 cursor-not-allowed': addingEnrollment || !newEnrollment.course_id }"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <svg x-show="addingEnrollment" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="!addingEnrollment" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span x-text="addingEnrollment ? 'Aggiunta...' : 'Aggiungi Iscrizione'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Empty State -->
            <div x-show="enrollments.length === 0"
                 class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun Corso</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Lo studente non è iscritto a nessun corso al momento.
                </p>
                <p class="mt-2 text-sm text-gray-600">
                    Usa il pulsante "Aggiungi Iscrizione Rapida" qui sopra per iscrivere lo studente a un corso.
                </p>
            </div>

            <!-- Enrollments Table -->
            <div x-show="enrollments.length > 0">
                <!-- Enrollments Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Corso
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Iscrizione
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pagamento
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Azioni
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="enrollment in enrollments" :key="enrollment.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Corso -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold"
                                                 x-text="enrollment.course_name.substring(0, 2).toUpperCase()">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="enrollment.course_name"></div>
                                                <div x-show="enrollment.course_description"
                                                     class="text-sm text-gray-500"
                                                     x-text="enrollment.course_description ? (enrollment.course_description.length > 50 ? enrollment.course_description.substring(0, 50) + '...' : enrollment.course_description) : ''">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Data Iscrizione -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900" x-text="enrollment.enrollment_date"></div>
                                        <div class="text-xs text-gray-500" x-text="enrollment.enrollment_date_human"></div>
                                    </td>

                                    <!-- Status Enrollment - DROPDOWN TOGGLE -->
                                    <td class="px-6 py-4 whitespace-nowrap" x-data="{ statusOpen: false }">
                                        <div class="relative">
                                            <button @click="statusOpen = !statusOpen"
                                                    type="button"
                                                    :disabled="updatingStatus[enrollment.id]"
                                                    :class="{
                                                        'bg-yellow-100 text-yellow-800 border-yellow-200': enrollment.status === 'pending',
                                                        'bg-green-100 text-green-800 border-green-200': enrollment.status === 'active',
                                                        'bg-red-100 text-red-800 border-red-200': enrollment.status === 'cancelled',
                                                        'bg-blue-100 text-blue-800 border-blue-200': enrollment.status === 'completed',
                                                        'bg-gray-100 text-gray-800 border-gray-200': enrollment.status === 'suspended',
                                                        'opacity-50 cursor-not-allowed': updatingStatus[enrollment.id]
                                                    }"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border cursor-pointer hover:opacity-80 transition-opacity">
                                                <svg x-show="updatingStatus[enrollment.id]" class="animate-spin h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span x-text="{
                                                    'pending': 'In Attesa',
                                                    'active': 'Attiva',
                                                    'cancelled': 'Annullata',
                                                    'completed': 'Completata',
                                                    'suspended': 'Sospesa'
                                                }[enrollment.status]"></span>
                                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="statusOpen"
                                                 @click.away="statusOpen = false"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="absolute left-0 mt-2 w-40 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                                <div class="py-1">
                                                    <button @click="updateEnrollmentStatus(enrollment.id, 'active'); statusOpen = false"
                                                            type="button"
                                                            :class="{ 'bg-gray-100': enrollment.status === 'active' }"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                                        Attiva
                                                    </button>
                                                    <button @click="updateEnrollmentStatus(enrollment.id, 'pending'); statusOpen = false"
                                                            type="button"
                                                            :class="{ 'bg-gray-100': enrollment.status === 'pending' }"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <span class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></span>
                                                        In Attesa
                                                    </button>
                                                    <button @click="updateEnrollmentStatus(enrollment.id, 'suspended'); statusOpen = false"
                                                            type="button"
                                                            :class="{ 'bg-gray-100': enrollment.status === 'suspended' }"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <span class="w-2 h-2 rounded-full bg-gray-500 mr-2"></span>
                                                        Sospesa
                                                    </button>
                                                    <button @click="updateEnrollmentStatus(enrollment.id, 'completed'); statusOpen = false"
                                                            type="button"
                                                            :class="{ 'bg-gray-100': enrollment.status === 'completed' }"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                                                        Completata
                                                    </button>
                                                    <button @click="updateEnrollmentStatus(enrollment.id, 'cancelled'); statusOpen = false"
                                                            type="button"
                                                            :class="{ 'bg-gray-100': enrollment.status === 'cancelled' }"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                                                        Annullata
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Payment Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                                'bg-yellow-100 text-yellow-800': enrollment.payment_status === 'pending',
                                                'bg-green-100 text-green-800': enrollment.payment_status === 'paid',
                                                'bg-gray-100 text-gray-800': enrollment.payment_status === 'refunded'
                                              }"
                                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            <span x-text="{
                                                'pending': 'In Attesa',
                                                'paid': 'Pagato',
                                                'refunded': 'Rimborsato'
                                            }[enrollment.payment_status]"></span>
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a :href="`/admin/enrollments/${enrollment.id}`"
                                               class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded transition-colors"
                                               title="Visualizza dettagli">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a :href="`/admin/enrollments/${enrollment.id}/edit`"
                                               class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-50 rounded transition-colors"
                                               title="Modifica iscrizione">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button @click="deleteEnrollment(enrollment.id, enrollment.course_name)"
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded transition-colors"
                                                    title="Rimuovi iscrizione">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@php
// BUGFIX: Prepare student form data as JSON variable to avoid Blade @json() truncation bug
// Using php json_encode() directly prevents compilation issues with large arrays
$studentFormData = json_encode([
    'first_name' => $student->first_name,
    'last_name' => $student->last_name,
    'name' => $student->name,
    'email' => $student->email,
    'phone' => $student->phone ?? '',
    'codice_fiscale' => $student->codice_fiscale ?? '',
    'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '',
    'address' => $student->address ?? '',
    'emergency_contact' => $student->emergency_contact ?? '',
    'medical_notes' => $student->medical_notes ?? '',
    'active' => $student->active,
    'is_minor' => $student->is_minor,
    'guardian_first_name' => $student->guardian_first_name ?? '',
    'guardian_last_name' => $student->guardian_last_name ?? '',
    'guardian_fiscal_code' => $student->guardian_fiscal_code ?? '',
    'guardian_email' => $student->guardian_email ?? '',
    'guardian_phone' => $student->guardian_phone ?? ''
], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<script nonce="@cspNonce">
document.addEventListener('alpine:init', () => {
    Alpine.data('studentEditForm', () => ({
        loading: false,
        deleting: false,  // Fix #10: Loading state per delete button
        errors: {},
        // Student data properly encoded with special character escaping
        form: {!! $studentFormData !!},

        // Enrollment management
        showAddForm: false,
        addingEnrollment: false,
        newEnrollment: {
            course_id: '',
            status: 'active'
        },
        // FIX: Use pre-mapped data from controller to avoid Blade json encoder bug with closures
        enrollments: @json($enrollmentsData),
        updatingStatus: {},

        get maxDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },

        updateFullName() {
            if (this.form.first_name && this.form.last_name) {
                this.form.name = `${this.form.first_name} ${this.form.last_name}`;
            }
        },

        /**
         * SENIOR FIX: Task #4 - Auto-check if student is minor based on date of birth
         * Automatically sets is_minor flag when date_of_birth changes
         */
        checkIfMinor() {
            if (this.form.date_of_birth) {
                const birthDate = new Date(this.form.date_of_birth);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                // Adjust age if birthday hasn't occurred yet this year
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                // Fix #4: Usa costante ADULT_AGE dal backend invece di magic number
                this.form.is_minor = age < {{ \App\Models\User::ADULT_AGE }};
            }
        },

        /**
         * CONFIRMATION FIX: Conferma prima di cancellare i dati del tutore
         * Verifica se ci sono dati del tutore e chiede conferma prima di cancellarli
         */
        checkMinorStatus() {
            // Se l'utente sta ATTIVANDO la checkbox (is_minor = true), non serve conferma
            if (this.form.is_minor) {
                return; // Permetti l'attivazione senza conferma
            }

            // Se l'utente sta DISATTIVANDO la checkbox (is_minor = false)
            // Controlla se ci sono dati del tutore da perdere
            const hasGuardianData = this.form.guardian_first_name ||
                                   this.form.guardian_last_name ||
                                   this.form.guardian_fiscal_code ||
                                   this.form.guardian_email ||
                                   this.form.guardian_phone;

            // Se ci sono dati del tutore, chiedi conferma
            if (hasGuardianData) {
                const confirmDelete = confirm(
                    'Attenzione: rimuovendo lo status di minorenne verranno cancellati tutti i dati del tutore. Continuare?'
                );

                // Se l'utente annulla, ripristina la checkbox a checked
                if (!confirmDelete) {
                    this.$nextTick(() => {
                        this.form.is_minor = true; // Ripristina checkbox
                    });
                    return;
                }
            }

            // Se l'utente conferma (o non ci sono dati), cancella i campi tutore
            this.form.guardian_first_name = '';
            this.form.guardian_last_name = '';
            this.form.guardian_fiscal_code = '';
            this.form.guardian_email = '';
            this.form.guardian_phone = '';
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                // Prepare data object (not FormData - better for Laravel JSON handling)
                const data = {
                    _method: 'PUT',
                    ...this.form
                };

                // Convert booleans to 0/1 for Laravel
                data.active = this.form.active ? 1 : 0;
                data.is_minor = this.form.is_minor ? 1 : 0;

                const response = await fetch('/admin/students/{{ $student->id }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: result.message,
                            type: 'success'
                        }
                    });
                    window.dispatchEvent(event);

                    // Redirect to student details
                    setTimeout(() => {
                        window.location.href = '/admin/students/{{ $student->id }}';
                    }, 1000);
                } else {
                    if (result.errors) {
                        this.errors = result.errors;
                    } else {
                        const event = new CustomEvent('show-toast', {
                            detail: {
                                message: result.message || 'Errore durante l\'aggiornamento dello studente',
                                type: 'error'
                            }
                        });
                        window.dispatchEvent(event);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                const event = new CustomEvent('show-toast', {
                    detail: {
                        message: 'Errore di connessione. Riprova più tardi.',
                        type: 'error'
                    }
                });
                window.dispatchEvent(event);
            } finally {
                this.loading = false;
            }
        },

        async deleteStudent() {
            // Fix #10: Set loading state
            this.deleting = true;

            try {
                const response = await fetch('/admin/students/{{ $student->id }}', {
                    method: 'DELETE',
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

                    // Redirect to students list (non reset deleting perché redirect)
                    setTimeout(() => {
                        window.location.href = '/admin/students';
                    }, 1500);
                } else {
                    // Fix #10: Reset loading state on error
                    this.deleting = false;
                    const event = new CustomEvent('show-toast', {
                        detail: { message: data.message || 'Errore durante l\'eliminazione dello studente', type: 'error' }
                    });
                    window.dispatchEvent(event);
                }
            } catch (error) {
                // Fix #10: Reset loading state on error
                this.deleting = false;
                console.error('Error:', error);
                const event = new CustomEvent('show-toast', {
                    detail: { message: 'Errore di connessione', type: 'error' }
                });
                window.dispatchEvent(event);
            }
        },

        /**
         * FEATURE: Quick Add Enrollment - Inline form submission
         */
        async addEnrollment() {
            if (!this.newEnrollment.course_id) {
                const event = new CustomEvent('show-toast', {
                    detail: {
                        message: 'Seleziona un corso',
                        type: 'error'
                    }
                });
                window.dispatchEvent(event);
                return;
            }

            this.addingEnrollment = true;

            try {
                const response = await fetch('/admin/enrollments', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: {{ $student->id }},
                        course_id: this.newEnrollment.course_id,
                        status: this.newEnrollment.status,
                        enrollment_date: new Date().toISOString().split('T')[0]
                    })
                });

                const data = await response.json();

                if (data.success && data.enrollment) {
                    // Add to reactive array
                    this.enrollments.push({
                        id: data.enrollment.id,
                        course_id: data.enrollment.course_id,
                        course_name: data.enrollment.course.name,
                        course_description: data.enrollment.course.description,
                        enrollment_date: new Date(data.enrollment.enrollment_date).toLocaleDateString('it-IT'),
                        enrollment_date_human: 'Oggi',
                        status: data.enrollment.status,
                        payment_status: data.enrollment.payment_status
                    });

                    // Reset form
                    this.newEnrollment.course_id = '';
                    this.newEnrollment.status = 'active';
                    this.showAddForm = false;

                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: data.message || 'Iscrizione aggiunta con successo',
                            type: 'success'
                        }
                    });
                    window.dispatchEvent(event);
                } else {
                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: data.message || 'Errore durante l\'aggiunta dell\'iscrizione',
                            type: 'error'
                        }
                    });
                    window.dispatchEvent(event);
                }
            } catch (error) {
                console.error('Error:', error);
                const event = new CustomEvent('show-toast', {
                    detail: {
                        message: 'Errore di connessione. Riprova più tardi.',
                        type: 'error'
                    }
                });
                window.dispatchEvent(event);
            } finally {
                this.addingEnrollment = false;
            }
        },

        /**
         * FEATURE: Status Toggle - Update enrollment status inline
         */
        async updateEnrollmentStatus(enrollmentId, newStatus) {
            // Set loading state for this specific enrollment
            this.updatingStatus[enrollmentId] = true;

            try {
                const response = await fetch(`/admin/enrollments/${enrollmentId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();

                if (data.success) {
                    // Update reactive array
                    const index = this.enrollments.findIndex(e => e.id === enrollmentId);
                    if (index !== -1) {
                        this.enrollments[index].status = newStatus;
                    }

                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: data.message || 'Status aggiornato con successo',
                            type: 'success'
                        }
                    });
                    window.dispatchEvent(event);
                } else {
                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: data.message || 'Errore durante l\'aggiornamento dello status',
                            type: 'error'
                        }
                    });
                    window.dispatchEvent(event);
                }
            } catch (error) {
                console.error('Error:', error);
                const event = new CustomEvent('show-toast', {
                    detail: {
                        message: 'Errore di connessione. Riprova più tardi.',
                        type: 'error'
                    }
                });
                window.dispatchEvent(event);
            } finally {
                // Remove loading state
                delete this.updatingStatus[enrollmentId];
            }
        },

        /**
         * FEATURE: Delete Enrollment - Remove with reactive update
         */
        async deleteEnrollment(enrollmentId, courseName) {
            // Conferma eliminazione
            if (!confirm(`Sei sicuro di voler rimuovere l'iscrizione al corso "${courseName}"?\n\nQuesta azione non può essere annullata.`)) {
                return;
            }

            try {
                const response = await fetch(`/admin/enrollments/${enrollmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Remove from reactive array
                    const index = this.enrollments.findIndex(e => e.id === enrollmentId);
                    if (index !== -1) {
                        this.enrollments.splice(index, 1);
                    }

                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: data.message || 'Iscrizione rimossa con successo',
                            type: 'success'
                        }
                    });
                    window.dispatchEvent(event);
                } else {
                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: data.message || 'Errore durante la rimozione dell\'iscrizione',
                            type: 'error'
                        }
                    });
                    window.dispatchEvent(event);
                }
            } catch (error) {
                console.error('Error:', error);
                const event = new CustomEvent('show-toast', {
                    detail: {
                        message: 'Errore di connessione. Riprova più tardi.',
                        type: 'error'
                    }
                });
                window.dispatchEvent(event);
            }
        }
    }));
});
</script>
</x-app-layout>
