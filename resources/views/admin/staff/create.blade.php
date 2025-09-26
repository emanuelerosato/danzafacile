<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuovo Staff
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione nuovo staff della tua scuola
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
        <li class="text-gray-900 font-medium">Nuovo Staff</li>
    </x-slot>




<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900">Dashboard</a></li>
            <li><i class="fas fa-chevron-right text-gray-400 mx-2"></i></li>
            <li><a href="{{ route('admin.staff.index') }}" class="hover:text-gray-900">Staff</a></li>
            <li><i class="fas fa-chevron-right text-gray-400 mx-2"></i></li>
            <li class="text-gray-900">Nuovo Staff</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Aggiungi Nuovo Staff</h1>
                    <p class="text-sm text-gray-600 mt-1">Crea un nuovo membro del personale</p>
                </div>
                <a href="{{ route('admin.staff.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i> Torna alla Lista
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form id="staff-form" method="POST" action="{{ route('admin.staff.store') }}" class="p-6 space-y-8" enctype="multipart/form-data">
            @csrf

            <!-- User Account Information -->
            <div class="border-b border-gray-200 pb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Account</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Es. Mario Rossi">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="mario.rossi@example.com">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Conferma Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Staff Information -->
            <div class="border-b border-gray-200 pb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Lavorative</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            Ruolo <span class="text-red-500">*</span>
                        </label>
                        <select id="role"
                                name="role"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror">
                            <option value="">Seleziona ruolo</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                            Dipartimento
                        </label>
                        <select id="department"
                                name="department"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department') border-red-500 @enderror">
                            <option value="">Seleziona dipartimento</option>
                            @foreach($departments as $key => $label)
                                <option value="{{ $key }}" {{ old('department') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo di Impiego <span class="text-red-500">*</span>
                        </label>
                        <select id="employment_type"
                                name="employment_type"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('employment_type') border-red-500 @enderror">
                            <option value="">Seleziona tipo</option>
                            @foreach($employmentTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('employment_type') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('employment_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status"
                                name="status"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'active') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Data di Assunzione
                        </label>
                        <input type="date"
                               id="hire_date"
                               name="hire_date"
                               value="{{ old('hire_date', now()->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hire_date') border-red-500 @enderror">
                        @error('hire_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="border-b border-gray-200 pb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Personali</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Titolo
                        </label>
                        <select id="title"
                                name="title"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror">
                            <option value="">Seleziona titolo</option>
                            <option value="Sig." {{ old('title') === 'Sig.' ? 'selected' : '' }}>Sig.</option>
                            <option value="Sig.ra" {{ old('title') === 'Sig.ra' ? 'selected' : '' }}>Sig.ra</option>
                            <option value="Dott." {{ old('title') === 'Dott.' ? 'selected' : '' }}>Dott.</option>
                            <option value="Dott.ssa" {{ old('title') === 'Dott.ssa' ? 'selected' : '' }}>Dott.ssa</option>
                            <option value="Prof." {{ old('title') === 'Prof.' ? 'selected' : '' }}>Prof.</option>
                            <option value="Prof.ssa" {{ old('title') === 'Prof.ssa' ? 'selected' : '' }}>Prof.ssa</option>
                        </select>
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                            Data di Nascita
                        </label>
                        <input type="date"
                               id="date_of_birth"
                               name="date_of_birth"
                               value="{{ old('date_of_birth') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_of_birth') border-red-500 @enderror">
                        @error('date_of_birth')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefono
                        </label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               value="{{ old('phone') }}"
                               maxlength="20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                               placeholder="+39 123 456 7890">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Contatto di Emergenza
                        </label>
                        <input type="text"
                               id="emergency_contact_name"
                               name="emergency_contact_name"
                               value="{{ old('emergency_contact_name') }}"
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_name') border-red-500 @enderror"
                               placeholder="Nome del contatto">
                        @error('emergency_contact_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefono Emergenza
                        </label>
                        <input type="tel"
                               id="emergency_contact_phone"
                               name="emergency_contact_phone"
                               value="{{ old('emergency_contact_phone') }}"
                               maxlength="20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_phone') border-red-500 @enderror"
                               placeholder="+39 123 456 7890">
                        @error('emergency_contact_phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Indirizzo
                        </label>
                        <textarea id="address"
                                  name="address"
                                  rows="3"
                                  maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                                  placeholder="Via, Città, CAP">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="border-b border-gray-200 pb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Professionali</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="years_experience" class="block text-sm font-medium text-gray-700 mb-2">
                            Anni di Esperienza
                        </label>
                        <input type="number"
                               id="years_experience"
                               name="years_experience"
                               value="{{ old('years_experience') }}"
                               min="0"
                               max="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('years_experience') border-red-500 @enderror">
                        @error('years_experience')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="specializations" class="block text-sm font-medium text-gray-700 mb-2">
                            Specializzazioni
                        </label>
                        <input type="text"
                               id="specializations"
                               name="specializations"
                               value="{{ old('specializations') }}"
                               maxlength="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('specializations') border-red-500 @enderror"
                               placeholder="Es. Ballet, Jazz, Hip Hop">
                        @error('specializations')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="qualifications" class="block text-sm font-medium text-gray-700 mb-2">
                            Qualifiche
                        </label>
                        <textarea id="qualifications"
                                  name="qualifications"
                                  rows="3"
                                  maxlength="1000"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qualifications') border-red-500 @enderror"
                                  placeholder="Elencare lauree, diplomi, etc.">{{ old('qualifications') }}</textarea>
                        @error('qualifications')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="certifications" class="block text-sm font-medium text-gray-700 mb-2">
                            Certificazioni
                        </label>
                        <textarea id="certifications"
                                  name="certifications"
                                  rows="3"
                                  maxlength="1000"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('certifications') border-red-500 @enderror"
                                  placeholder="Certificazioni professionali, corsi di formazione">{{ old('certifications') }}</textarea>
                        @error('certifications')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="border-b border-gray-200 pb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Economiche</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-2">
                            Tariffa Oraria (€)
                        </label>
                        <input type="number"
                               id="hourly_rate"
                               name="hourly_rate"
                               value="{{ old('hourly_rate') }}"
                               min="0"
                               max="999.99"
                               step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hourly_rate') border-red-500 @enderror">
                        @error('hourly_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="monthly_salary" class="block text-sm font-medium text-gray-700 mb-2">
                            Stipendio Mensile (€)
                        </label>
                        <input type="number"
                               id="monthly_salary"
                               name="monthly_salary"
                               value="{{ old('monthly_salary') }}"
                               min="0"
                               max="99999.99"
                               step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('monthly_salary') border-red-500 @enderror">
                        @error('monthly_salary')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                            Metodo di Pagamento
                        </label>
                        <select id="payment_method"
                                name="payment_method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('payment_method') border-red-500 @enderror">
                            <option value="bank_transfer" {{ old('payment_method', 'bank_transfer') === 'bank_transfer' ? 'selected' : '' }}>Bonifico Bancario</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Contanti</option>
                            <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Assegno</option>
                        </select>
                        @error('payment_method')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Codice Fiscale
                        </label>
                        <input type="text"
                               id="tax_id"
                               name="tax_id"
                               value="{{ old('tax_id') }}"
                               maxlength="20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tax_id') border-red-500 @enderror">
                        @error('tax_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Availability & Settings -->
            <div class="border-b border-gray-200 pb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Disponibilità & Impostazioni</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="max_hours_per_week" class="block text-sm font-medium text-gray-700 mb-2">
                            Ore Massime Settimanali
                        </label>
                        <input type="number"
                               id="max_hours_per_week"
                               name="max_hours_per_week"
                               value="{{ old('max_hours_per_week') }}"
                               min="1"
                               max="80"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('max_hours_per_week') border-red-500 @enderror">
                        @error('max_hours_per_week')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                        <input type="hidden" name="can_substitute" value="0">
                        <input type="checkbox"
                               id="can_substitute"
                               name="can_substitute"
                               value="1"
                               {{ old('can_substitute') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="can_substitute" class="text-sm text-gray-700">
                            Disponibile per sostituzioni
                        </label>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Giorni Disponibili
                        </label>
                        <div class="availability-container">
                            <div class="grid grid-cols-7 gap-2">
                                @foreach(['monday' => 'Lun', 'tuesday' => 'Mar', 'wednesday' => 'Mer', 'thursday' => 'Gio', 'friday' => 'Ven', 'saturday' => 'Sab', 'sunday' => 'Dom'] as $day => $label)
                                    <label class="flex items-center justify-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox"
                                               name="availability[]"
                                               value="{{ $day }}"
                                               {{ is_array(old('availability')) && in_array($day, old('availability')) ? 'checked' : '' }}
                                               class="availability-day hidden">
                                        <span class="text-sm">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <!-- Visual calendar will be inserted here by FormManager -->
                            <div class="availability-visual"></div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Note
                        </label>
                        <textarea id="notes"
                                  name="notes"
                                  rows="3"
                                  maxlength="1000"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="Note aggiuntive sullo staff member">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <a href="{{ route('admin.staff.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-times"></i> Annulla
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-save"></i> Crea Staff Member
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
@vite('resources/js/admin/staff/staff-manager.js')
<script>
    // Mark this as a staff page for the JavaScript system
    document.addEventListener('DOMContentLoaded', function() {
        document.body.setAttribute('data-page', 'staff');
        document.body.classList.add('staff-form-page');
    });
</script>
@endpush

@push('styles')
<style>
.availability-day {
    transition: all 0.2s ease;
}

.availability-day.selected {
    background-color: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}
</style>
@endpush
</x-app-layout>
