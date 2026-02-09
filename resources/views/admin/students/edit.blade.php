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
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('admin.students.show', $student) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-rose-600 md:ml-2">
                        {{ $student->name }}
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Modifica</span>
                </div>
            </li>
        </ol>
    </nav>

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
                        <div x-show="errors.first_name" class="mt-1 text-sm text-red-600" x-text="errors.first_name"></div>
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
                        <div x-show="errors.last_name" class="mt-1 text-sm text-red-600" x-text="errors.last_name"></div>
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
                        <div x-show="errors.name" class="mt-1 text-sm text-red-600" x-text="errors.name"></div>
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
                        <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></div>
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
                        <div x-show="errors.phone" class="mt-1 text-sm text-red-600" x-text="errors.phone"></div>
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
                        <div x-show="errors.codice_fiscale" class="mt-1 text-sm text-red-600" x-text="errors.codice_fiscale"></div>
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
                        <div x-show="errors.date_of_birth" class="mt-1 text-sm text-red-600" x-text="errors.date_of_birth"></div>
                    </div>

                    <!-- SENIOR FIX: Task #4 - Is Minor Checkbox -->
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_minor"
                                   id="is_minor"
                                   x-model="form.is_minor"
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
                        <div x-show="errors.guardian_first_name" class="mt-1 text-sm text-red-600" x-text="errors.guardian_first_name"></div>
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
                        <div x-show="errors.guardian_last_name" class="mt-1 text-sm text-red-600" x-text="errors.guardian_last_name"></div>
                    </div>

                    <!-- Guardian Fiscal Code -->
                    <div>
                        <label for="guardian_fiscal_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Codice Fiscale Genitore <span class="text-red-500" x-show="form.is_minor">*</span>
                        </label>
                        <input type="text"
                               name="guardian_fiscal_code"
                               id="guardian_fiscal_code"
                               x-model="form.guardian_fiscal_code"
                               :required="form.is_minor"
                               maxlength="16"
                               style="text-transform: uppercase"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors uppercase"
                               placeholder="RSSMRA80A01H501U">
                        <div x-show="errors.guardian_fiscal_code" class="mt-1 text-sm text-red-600" x-text="errors.guardian_fiscal_code"></div>
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
                        <div x-show="errors.guardian_email" class="mt-1 text-sm text-red-600" x-text="errors.guardian_email"></div>
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
                        <div x-show="errors.guardian_phone" class="mt-1 text-sm text-red-600" x-text="errors.guardian_phone"></div>
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
                        <div x-show="errors.address" class="mt-1 text-sm text-red-600" x-text="errors.address"></div>
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
                    <div x-show="errors.emergency_contact" class="mt-1 text-sm text-red-600" x-text="errors.emergency_contact"></div>
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
                    <div x-show="errors.medical_notes" class="mt-1 text-sm text-red-600" x-text="errors.medical_notes"></div>
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

                    <!-- Danger Zone -->
                    <button type="button"
                            @click="if(confirm('Sei sicuro di voler eliminare questo studente?')) { deleteStudent() }"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Elimina
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

        <div class="p-6">
            @if($student->enrollments->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun Corso</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Lo studente non è iscritto a nessun corso al momento.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.enrollments.create', ['user_id' => $student->id]) }}"
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Aggiungi Corso
                        </a>
                    </div>
                </div>
            @else
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
                            @foreach($student->enrollments as $enrollment)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Corso -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold">
                                                {{ strtoupper(substr($enrollment->course->name, 0, 2)) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $enrollment->course->name }}
                                                </div>
                                                @if($enrollment->course->description)
                                                    <div class="text-sm text-gray-500">
                                                        {{ Str::limit($enrollment->course->description, 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Data Iscrizione -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $enrollment->enrollment_date->format('d/m/Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $enrollment->enrollment_date->diffForHumans() }}
                                        </div>
                                    </td>

                                    <!-- Status Enrollment -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'active' => 'bg-green-100 text-green-800 border-green-200',
                                                'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                                                'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'suspended' => 'bg-gray-100 text-gray-800 border-gray-200',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'In Attesa',
                                                'active' => 'Attiva',
                                                'cancelled' => 'Annullata',
                                                'completed' => 'Completata',
                                                'suspended' => 'Sospesa',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusColors[$enrollment->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                            {{ $statusLabels[$enrollment->status] ?? ucfirst($enrollment->status) }}
                                        </span>
                                    </td>

                                    <!-- Payment Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $paymentColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'paid' => 'bg-green-100 text-green-800',
                                                'refunded' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $paymentLabels = [
                                                'pending' => 'In Attesa',
                                                'paid' => 'Pagato',
                                                'refunded' => 'Rimborsato',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $paymentColors[$enrollment->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $paymentLabels[$enrollment->payment_status] ?? ucfirst($enrollment->payment_status) }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                                               class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded transition-colors"
                                               title="Visualizza dettagli">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.enrollments.edit', $enrollment) }}"
                                               class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-50 rounded transition-colors"
                                               title="Modifica iscrizione">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button @click="deleteEnrollment({{ $enrollment->id }}, '{{ $enrollment->course->name }}')"
                                                    class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded transition-colors"
                                                    title="Rimuovi iscrizione">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Action Button (if has enrollments) -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.enrollments.create', ['user_id' => $student->id]) }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Aggiungi Altro Corso
                    </a>
                </div>
            @endif
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
        errors: {},
        // Student data properly encoded with special character escaping
        form: {!! $studentFormData !!},

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

                this.form.is_minor = age < 18;
            }
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

                    // Redirect to students list
                    setTimeout(() => {
                        window.location.href = '/admin/students';
                    }, 1000);
                } else {
                    const event = new CustomEvent('show-toast', {
                        detail: { message: data.message || 'Errore durante l\'eliminazione dello studente', type: 'error' }
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
        },

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
                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: data.message || 'Iscrizione rimossa con successo',
                            type: 'success'
                        }
                    });
                    window.dispatchEvent(event);

                    // Ricarica la pagina per aggiornare la lista
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
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
