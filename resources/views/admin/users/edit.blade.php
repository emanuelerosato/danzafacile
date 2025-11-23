<x-app-layout>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Studente
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Aggiorna le informazioni di {{ $user->name }}
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <a href="{{ route('admin.users.show', $user) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Visualizza
                </a>
                <a href="{{ route('admin.users.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Torna alla Lista
                </a>
            </div>
        </div>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">Studenti</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.users.show', $user) }}" class="text-gray-500 hover:text-gray-700">{{ $user->name }}</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Modifica</li>

    <div x-data="userEditForm()" class="space-y-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" @submit="onSubmit">
            @csrf
            @method('PATCH')
            
            <!-- Profile Image Section -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Foto Profilo</h3>
                
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <div class="relative">
                            <div id="preview-container" class="w-20 h-20 rounded-full overflow-hidden ring-4 ring-white shadow-lg">
                                @if($user->profile_image)
                                    <img id="image-preview" 
                                         class="w-full h-full object-cover" 
                                         src="{{ Storage::url($user->profile_image) }}" 
                                         alt="{{ $user->name }}">
                                @else
                                    <div id="image-preview" class="w-full h-full bg-gradient-to-r from-rose-400 to-purple-500 flex items-center justify-center text-white font-bold text-xl">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <button type="button" 
                                    onclick="document.getElementById('profile_image').click()"
                                    class="absolute -bottom-1 -right-1 bg-rose-500 rounded-full p-2 text-white shadow-lg hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <input type="file" 
                               id="profile_image" 
                               name="profile_image" 
                               accept="image/*"
                               class="hidden"
                               onchange="previewImage(this)">
                        
                        <div>
                            <button type="button" 
                                    onclick="document.getElementById('profile_image').click()"
                                    class="inline-flex items-center px-4 py-2 bg-rose-500 text-white text-sm font-medium rounded-lg hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Carica Nuova Foto
                            </button>
                            @if($user->profile_image)
                                <button type="button" 
                                        onclick="removeImage()"
                                        class="ml-3 inline-flex items-center px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Rimuovi
                                </button>
                                <input type="hidden" name="remove_profile_image" id="remove_profile_image" value="0">
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            JPG, PNG fino a 2MB. Dimensioni consigliate: 400x400px.
                        </p>
                        @error('profile_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informazioni Personali</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo *</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $user->name) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('name') border-red-300 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $user->email) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('email') border-red-300 @enderror"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Telefono</label>
                        <input type="text" 
                               name="phone" 
                               id="phone" 
                               value="{{ old('phone', $user->phone) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('phone') border-red-300 @enderror"
                               placeholder="+39 123 456 7890">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Data di Nascita</label>
                        <input type="date" 
                               name="date_of_birth" 
                               id="date_of_birth" 
                               value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('date_of_birth') border-red-300 @enderror">
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="tax_code" class="block text-sm font-medium text-gray-700">Codice Fiscale</label>
                        <input type="text" 
                               name="tax_code" 
                               id="tax_code" 
                               value="{{ old('tax_code', $user->tax_code) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('tax_code') border-red-300 @enderror"
                               placeholder="RSSMRA80A01H501X"
                               maxlength="16"
                               style="text-transform: uppercase;">
                        @error('tax_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Stato Account *</label>
                        <select name="status" 
                                id="status" 
                                class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('status') border-red-300 @enderror">
                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Attivo</option>
                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Non attivo</option>
                            <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Sospeso</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Indirizzo</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">Via/Piazza</label>
                        <input type="text" 
                               name="address" 
                               id="address" 
                               value="{{ old('address', $user->address) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('address') border-red-300 @enderror"
                               placeholder="Via Roma, 123">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">Città</label>
                        <input type="text" 
                               name="city" 
                               id="city" 
                               value="{{ old('city', $user->city) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('city') border-red-300 @enderror"
                               placeholder="Milano">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700">CAP</label>
                        <input type="text" 
                               name="postal_code" 
                               id="postal_code" 
                               value="{{ old('postal_code', $user->postal_code) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('postal_code') border-red-300 @enderror"
                               placeholder="20100"
                               maxlength="5">
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contatto di Emergenza</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="emergency_contact" class="block text-sm font-medium text-gray-700">Nome Contatto</label>
                        <input type="text" 
                               name="emergency_contact" 
                               id="emergency_contact" 
                               value="{{ old('emergency_contact', $user->emergency_contact) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('emergency_contact') border-red-300 @enderror"
                               placeholder="Mario Rossi (Padre)">
                        @error('emergency_contact')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="emergency_phone" class="block text-sm font-medium text-gray-700">Telefono Emergenza</label>
                        <input type="text" 
                               name="emergency_phone" 
                               id="emergency_phone" 
                               value="{{ old('emergency_phone', $user->emergency_phone) }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('emergency_phone') border-red-300 @enderror"
                               placeholder="+39 123 456 7890">
                        @error('emergency_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informazioni Mediche (Opzionali)</h3>
                
                <div class="space-y-6">
                    <div>
                        <label for="medical_info" class="block text-sm font-medium text-gray-700">Condizioni Mediche</label>
                        <textarea name="medical_info" 
                                  id="medical_info" 
                                  rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('medical_info') border-red-300 @enderror"
                                  placeholder="Eventuali condizioni mediche di cui tenere conto durante le lezioni...">{{ old('medical_info', $user->medical_info) }}</textarea>
                        @error('medical_info')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="allergies" class="block text-sm font-medium text-gray-700">Allergie</label>
                        <textarea name="allergies" 
                                  id="allergies" 
                                  rows="2"
                                  class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('allergies') border-red-300 @enderror"
                                  placeholder="Eventuali allergie note...">{{ old('allergies', $user->allergies) }}</textarea>
                        @error('allergies')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="medications" class="block text-sm font-medium text-gray-700">Farmaci in Uso</label>
                        <textarea name="medications" 
                                  id="medications" 
                                  rows="2"
                                  class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('medications') border-red-300 @enderror"
                                  placeholder="Farmaci attualmente in uso...">{{ old('medications', $user->medications) }}</textarea>
                        @error('medications')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Note Aggiuntive</h3>
                
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">Note/Biografia</label>
                    <textarea name="bio" 
                              id="bio" 
                              rows="4"
                              class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 @error('bio') border-red-300 @enderror"
                              placeholder="Note aggiuntive, obiettivi, preferenze dello studente...">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500">* Campi obbligatori</span>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                        <a href="{{ route('admin.users.show', $user) }}" 
                           class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                            Annulla
                        </a>
                        
                        <button type="submit" 
                                :disabled="saving"
                                class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 border border-transparent rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                :class="{ 'opacity-50 cursor-not-allowed': saving }">
                            <span x-show="!saving">Salva Modifiche</span>
                            <span x-show="saving" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Salvando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script nonce="@cspNonce">
        function userEditForm() {
            return {
                saving: false,
                
                onSubmit(e) {
                    this.saving = true;
                    // Form will submit naturally
                }
            }
        }
        
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const preview = document.getElementById('image-preview');
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img class="w-full h-full object-cover" src="${e.target.result}" alt="Preview">`;
                    // Reset remove flag if new image is uploaded
                    document.getElementById('remove_profile_image').value = '0';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeImage() {
            if (confirm('Sei sicuro di voler rimuovere la foto profilo?')) {
                const preview = document.getElementById('image-preview');
                const userName = '{{ $user->name }}';
                const initial = userName.charAt(0).toUpperCase();
                
                preview.innerHTML = `<div class="w-full h-full bg-gradient-to-r from-rose-400 to-purple-500 flex items-center justify-center text-white font-bold text-xl">${initial}</div>`;
                
                // Set remove flag
                document.getElementById('remove_profile_image').value = '1';
                
                // Clear file input
                document.getElementById('profile_image').value = '';
            }
        }
        
        // Auto uppercase tax code
        document.getElementById('tax_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            
            requiredFields.forEach(field => {
                field.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        this.classList.add('border-red-300');
                    } else {
                        this.classList.remove('border-red-300');
                    }
                });
            });
            
            // Email validation
            const emailField = document.getElementById('email');
            emailField.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('border-red-300');
                    this.setCustomValidity('Inserisci un indirizzo email valido');
                } else {
                    this.classList.remove('border-red-300');
                    this.setCustomValidity('');
                }
            });
            
            // Phone validation
            const phoneField = document.getElementById('phone');
            phoneField.addEventListener('input', function() {
                // Remove any non-numeric characters except +, spaces, and dashes
                this.value = this.value.replace(/[^\d\s\+\-]/g, '');
            });
            
            // Tax code validation
            const taxCodeField = document.getElementById('tax_code');
            taxCodeField.addEventListener('blur', function() {
                if (this.value && this.value.length !== 16) {
                    this.classList.add('border-red-300');
                    this.setCustomValidity('Il codice fiscale deve essere di 16 caratteri');
                } else {
                    this.classList.remove('border-red-300');
                    this.setCustomValidity('');
                }
            });
            
            // Postal code validation
            const postalCodeField = document.getElementById('postal_code');
            postalCodeField.addEventListener('input', function() {
                // Allow only numbers
                this.value = this.value.replace(/\D/g, '');
            });
        });
    </script>
    @endpush
</x-app-layout>
