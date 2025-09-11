@extends('layouts.app')

@section('title', 'Nuovo Utente - Super Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50" x-data="userForm()">
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
                        <h1 class="text-2xl font-bold text-gray-900">👤 Nuovo Utente</h1>
                        <p class="text-sm text-gray-600">Crea un nuovo utente nel sistema</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('super-admin.users.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Personal Information -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-rose-50 to-pink-50">
                    <h3 class="text-lg font-semibold text-gray-900">📋 Informazioni Personali</h3>
                    <p class="text-sm text-gray-600">Inserisci i dati personali dell'utente</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                            <input type="text" 
                                   name="first_name" 
                                   id="first_name"
                                   value="{{ old('first_name') }}"
                                   required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('first_name') border-red-300 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Cognome *</label>
                            <input type="text" 
                                   name="last_name" 
                                   id="last_name"
                                   value="{{ old('last_name') }}"
                                   required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('last_name') border-red-300 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   value="{{ old('email') }}"
                                   required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefono</label>
                            <input type="tel" 
                                   name="phone" 
                                   id="phone"
                                   value="{{ old('phone') }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('phone') border-red-300 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Data di Nascita</label>
                            <input type="date" 
                                   name="date_of_birth" 
                                   id="date_of_birth"
                                   value="{{ old('date_of_birth') }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('date_of_birth') border-red-300 @enderror">
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role and Permissions -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                    <h3 class="text-lg font-semibold text-gray-900">🎭 Ruolo e Permessi</h3>
                    <p class="text-sm text-gray-600">Configura il ruolo e i permessi dell'utente</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Role Selection -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Ruolo *</label>
                        <select name="role" 
                                id="role"
                                x-model="selectedRole"
                                @change="updateSchoolVisibility()"
                                required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('role') border-red-300 @enderror">
                            <option value="">Seleziona un ruolo</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👨‍💼 Admin Scuola</option>
                            <option value="instructor" {{ old('role') == 'instructor' ? 'selected' : '' }}>🎭 Istruttore</option>
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>🎓 Studente</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <!-- Role Descriptions -->
                        <div class="mt-3 space-y-2">
                            <div x-show="selectedRole === 'admin'" x-transition class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800">
                                    <strong>Admin Scuola:</strong> Gestisce corsi, studenti e istruttori della propria scuola. Ha accesso completo ai dati della scuola assegnata.
                                </p>
                            </div>
                            <div x-show="selectedRole === 'instructor'" x-transition class="p-3 bg-green-50 rounded-lg border border-green-200">
                                <p class="text-sm text-green-800">
                                    <strong>Istruttore:</strong> Può gestire i propri corsi, visualizzare studenti iscritti e aggiornare i progressi.
                                </p>
                            </div>
                            <div x-show="selectedRole === 'student'" x-transition class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-800">
                                    <strong>Studente:</strong> Può iscriversi ai corsi, visualizzare il proprio programma e gestire il profilo personale.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- School Assignment (for admin/instructor/student) -->
                    <div x-show="selectedRole !== '' && selectedRole !== 'super_admin'" x-transition>
                        <label for="school_id" class="block text-sm font-medium text-gray-700 mb-2">Scuola di Appartenenza *</label>
                        <select name="school_id" 
                                id="school_id"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('school_id') border-red-300 @enderror">
                            <option value="">Seleziona una scuola</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                    @if(!$school->active)
                                        (Inattiva)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('school_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="active" 
                                   value="1"
                                   {{ old('active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Utente attivo</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Gli utenti inattivi non possono accedere al sistema</p>
                    </div>
                </div>
            </div>

            <!-- Account Credentials -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-lg font-semibold text-gray-900">🔐 Credenziali Account</h3>
                    <p class="text-sm text-gray-600">Configura le credenziali di accesso</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   required
                                   minlength="8"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('password') border-red-300 @enderror">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Minimo 8 caratteri</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Conferma Password *</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation"
                                   required
                                   minlength="8"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                        </div>
                    </div>

                    <!-- Send Welcome Email -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="send_welcome_email" 
                                   value="1"
                                   {{ old('send_welcome_email', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Invia email di benvenuto</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">L'utente riceverà una email con le credenziali di accesso</p>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                    <h3 class="text-lg font-semibold text-gray-900">📝 Informazioni Aggiuntive</h3>
                    <p class="text-sm text-gray-600">Dettagli opzionali per il profilo utente</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Indirizzo</label>
                        <textarea name="address" 
                                  id="address"
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('address') border-red-300 @enderror">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Note Interne</label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="3"
                                  placeholder="Note visibili solo agli amministratori..."
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('super-admin.users.index') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors">
                    Annulla
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Crea Utente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Alpine.js User Form -->
<script>
function userForm() {
    return {
        selectedRole: '{{ old('role') }}',
        
        updateSchoolVisibility() {
            const schoolField = document.getElementById('school_id');
            if (this.selectedRole === 'super_admin') {
                schoolField.removeAttribute('required');
            } else {
                schoolField.setAttribute('required', 'required');
            }
        },
        
        init() {
            this.updateSchoolVisibility();
        }
    }
}
</script>

@endsection