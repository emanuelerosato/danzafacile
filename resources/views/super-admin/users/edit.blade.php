@extends('layouts.app')

@section('title', 'Modifica Utente - Super Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50" x-data="userEditForm()">
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
                        <h1 class="text-2xl font-bold text-gray-900">✏️ Modifica Utente</h1>
                        <p class="text-sm text-gray-600">Aggiorna i dati di {{ $user->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('super-admin.users.show', $user) }}" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Visualizza
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- User Info Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-r from-rose-400 to-pink-500 flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                        <div class="flex items-center space-x-3 mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->role === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role === 'instructor' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $user->role === 'student' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->active ? 'Attivo' : 'Inattivo' }}
                            </span>
                            @if($user->school)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $user->school->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('super-admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Personal Information -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-rose-50 to-pink-50">
                    <h3 class="text-lg font-semibold text-gray-900">📋 Informazioni Personali</h3>
                    <p class="text-sm text-gray-600">Aggiorna i dati personali dell'utente</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                            <input type="text" 
                                   name="first_name" 
                                   id="first_name"
                                   value="{{ old('first_name', $user->first_name) }}"
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
                                   value="{{ old('last_name', $user->last_name) }}"
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
                                   value="{{ old('email', $user->email) }}"
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
                                   value="{{ old('phone', $user->phone) }}"
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
                                   value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('date_of_birth') border-red-300 @enderror">
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role and Permissions -->
            @if($user->role !== 'super_admin' || auth()->user()->role === 'super_admin')
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">🎭 Ruolo e Permessi</h3>
                            <p class="text-sm text-gray-600">Gestisci il ruolo e i permessi dell'utente</p>
                        </div>
                        @if($user->role === 'super_admin')
                            <div class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                ⚠️ Super Admin
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Role Selection -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Ruolo *</label>
                        @if($user->role === 'super_admin' && auth()->user()->id === $user->id)
                            <input type="hidden" name="role" value="super_admin">
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <strong>🔒 Non puoi modificare il tuo stesso ruolo di Super Admin</strong>
                                </p>
                            </div>
                        @else
                            <select name="role" 
                                    id="role"
                                    x-model="selectedRole"
                                    @change="updateSchoolVisibility()"
                                    required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('role') border-red-300 @enderror">
                                @if($user->role === 'super_admin')
                                    <option value="super_admin" selected>🔱 Super Admin</option>
                                @endif
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>👨‍💼 Admin Scuola</option>
                                <option value="instructor" {{ old('role', $user->role) == 'instructor' ? 'selected' : '' }}>🎭 Istruttore</option>
                                <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>🎓 Studente</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                        
                        <!-- Role Change Warning -->
                        <div x-show="selectedRole !== '{{ $user->role }}'" x-transition class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                            <p class="text-sm text-orange-800">
                                <strong>⚠️ Attenzione:</strong> Modificare il ruolo potrebbe cambiare i permessi di accesso dell'utente. Procedi con cautela.
                            </p>
                        </div>
                    </div>

                    <!-- School Assignment -->
                    <div x-show="selectedRole !== 'super_admin'" x-transition>
                        <label for="school_id" class="block text-sm font-medium text-gray-700 mb-2">Scuola di Appartenenza *</label>
                        <select name="school_id" 
                                id="school_id"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('school_id') border-red-300 @enderror">
                            <option value="">Seleziona una scuola</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>
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
                        @if($user->id === auth()->user()->id)
                            <input type="hidden" name="active" value="1">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <strong>ℹ️ Non puoi disattivare il tuo stesso account</strong>
                                </p>
                            </div>
                        @else
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="active" 
                                       value="1"
                                       {{ old('active', $user->active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Utente attivo</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">Gli utenti inattivi non possono accedere al sistema</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Password Update -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-lg font-semibold text-gray-900">🔐 Aggiorna Password</h3>
                    <p class="text-sm text-gray-600">Lascia vuoto per mantenere la password corrente</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Nuova Password</label>
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   minlength="8"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('password') border-red-300 @enderror">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Minimo 8 caratteri (lascia vuoto per non modificare)</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Conferma Nuova Password</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation"
                                   minlength="8"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                        </div>
                    </div>

                    <!-- Send Password Reset Email -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="send_password_email" 
                                   value="1"
                                   class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Invia email con nuova password</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">L'utente riceverà una email con la nuova password (solo se modificata)</p>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                    <h3 class="text-lg font-semibold text-gray-900">📝 Informazioni Aggiuntive</h3>
                    <p class="text-sm text-gray-600">Aggiorna dettagli e note</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Indirizzo</label>
                        <textarea name="address" 
                                  id="address"
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('address') border-red-300 @enderror">{{ old('address', $user->address) }}</textarea>
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
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50 @error('notes') border-red-300 @enderror">{{ old('notes', $user->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Last Activity Info -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-slate-50">
                    <h3 class="text-lg font-semibold text-gray-900">📊 Informazioni Account</h3>
                    <p class="text-sm text-gray-600">Statistiche e attività dell'utente</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $user->created_at->format('d/m/Y') }}</div>
                            <div class="text-sm text-gray-500">Data registrazione</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $user->updated_at->format('d/m/Y') }}</div>
                            <div class="text-sm text-gray-500">Ultimo aggiornamento</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $user->email_verified_at ? '✅' : '❌' }}
                            </div>
                            <div class="text-sm text-gray-500">Email verificata</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex items-center space-x-3">
                    @if($user->role !== 'super_admin' && $user->id !== auth()->id())
                        <form action="{{ route('super-admin.users.destroy', $user) }}" method="POST" 
                              onsubmit="return confirm('Sei sicuro di voler eliminare questo utente? Questa azione non può essere annullata.')" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Elimina Utente
                            </button>
                        </form>
                    @endif
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('super-admin.users.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors">
                        Annulla
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salva Modifiche
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Alpine.js User Edit Form -->
<script>
function userEditForm() {
    return {
        selectedRole: '{{ old('role', $user->role) }}',
        
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