<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuovo Studente
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione nuovo studente della tua scuola
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
        <li class="text-gray-900 font-medium">Nuovo Studente</li>
    </x-slot>




<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.students.index') }}"
               class="inline-flex items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Nuovo Studente</h1>
                <p class="text-gray-600">Aggiungi un nuovo studente alla tua scuola</p>
            </div>
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
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Nuovo Studente</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200" x-data="studentForm">
        <form @submit.prevent="submitForm" class="p-6 space-y-8">
            @csrf

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

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                            Data di Nascita <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               name="date_of_birth"
                               id="date_of_birth"
                               x-model="form.date_of_birth"
                               required
                               :max="maxDate"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors">
                        <div x-show="errors.date_of_birth" class="mt-1 text-sm text-red-600" x-text="errors.date_of_birth"></div>
                    </div>

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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Emergency Contact Name -->
                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Contatto
                        </label>
                        <input type="text"
                               name="emergency_contact_name"
                               id="emergency_contact_name"
                               x-model="form.emergency_contact_name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="Nome e cognome">
                        <div x-show="errors.emergency_contact_name" class="mt-1 text-sm text-red-600" x-text="errors.emergency_contact_name"></div>
                    </div>

                    <!-- Emergency Contact Phone -->
                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefono Contatto
                        </label>
                        <input type="tel"
                               name="emergency_contact_phone"
                               id="emergency_contact_phone"
                               x-model="form.emergency_contact_phone"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors"
                               placeholder="+39 123 456 7890">
                        <div x-show="errors.emergency_contact_phone" class="mt-1 text-sm text-red-600" x-text="errors.emergency_contact_phone"></div>
                    </div>
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
                    <label for="medical_conditions" class="block text-sm font-medium text-gray-700 mb-2">
                        Condizioni Mediche
                    </label>
                    <textarea name="medical_conditions"
                              id="medical_conditions"
                              x-model="form.medical_conditions"
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors resize-none"
                              placeholder="Allergie, condizioni mediche particolari, farmaci assunti, ecc."></textarea>
                    <div x-show="errors.medical_conditions" class="mt-1 text-sm text-red-600" x-text="errors.medical_conditions"></div>
                </div>
            </div>

            <!-- Settings -->
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Impostazioni Account
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Configurazioni per l'account dello studente</p>
                </div>

                <div class="space-y-4">
                    <!-- Active Status -->
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

                    <!-- Send Welcome Email -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="send_welcome_email" class="text-sm font-medium text-gray-700">
                                Email di Benvenuto
                            </label>
                            <p class="text-sm text-gray-500">Invia email di benvenuto con credenziali di accesso</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="send_welcome_email"
                                   id="send_welcome_email"
                                   x-model="form.send_welcome_email"
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('admin.students.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annulla
                </a>

                <button type="submit"
                        :disabled="loading"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span x-text="loading ? 'Creazione in corso...' : 'Crea Studente'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentForm', () => ({
        loading: false,
        errors: {},
        form: {
            first_name: '',
            last_name: '',
            name: '',
            email: '',
            phone: '',
            date_of_birth: '',
            address: '',
            emergency_contact_name: '',
            emergency_contact_phone: '',
            medical_conditions: '',
            active: true,
            send_welcome_email: true
        },

        get maxDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },

        updateFullName() {
            if (this.form.first_name && this.form.last_name) {
                this.form.name = `${this.form.first_name} ${this.form.last_name}`;
            }
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('/admin/students', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message with password if provided
                    let message = data.message;
                    if (data.data && data.data.password) {
                        message += `\n\nPassword generata: ${data.data.password}\n\nAssicurati di comunicarla allo studente!`;
                    }

                    const event = new CustomEvent('show-toast', {
                        detail: {
                            message: message,
                            type: 'success'
                        }
                    });
                    window.dispatchEvent(event);

                    // Redirect to student details or list
                    if (data.data && data.data.student) {
                        setTimeout(() => {
                            window.location.href = `/admin/students/${data.data.student.id}`;
                        }, 2000);
                    } else {
                        setTimeout(() => {
                            window.location.href = '/admin/students';
                        }, 1500);
                    }
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        const event = new CustomEvent('show-toast', {
                            detail: {
                                message: data.message || 'Errore durante la creazione dello studente',
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
        }
    }));
});
</script>
</x-app-layout>
