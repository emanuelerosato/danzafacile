<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuova Scuola
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Registra una nuova scuola nel sistema
                </p>
            </div>
            <a href="{{ route('super-admin.schools.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Torna alla Lista
            </a>
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
        <li class="text-gray-900 font-medium">Nuova</li>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('super-admin.schools.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <!-- Basic Information -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Informazioni Generali</h3>
                    <p class="text-sm text-gray-600 mt-1">Dati principali della scuola</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input
                        name="name"
                        label="Nome Scuola"
                        placeholder="es. Accademia Balletto Milano"
                        required="true"
                        icon="academic-cap"
                        help="Nome completo della scuola di danza"
                    />
                    
                    <x-form-input
                        name="slug"
                        label="Slug URL"
                        placeholder="es. accademia-balletto-milano"
                        icon="link"
                        help="URL personalizzato (auto-generato se vuoto)"
                    />
                    
                    <x-form-input
                        name="email"
                        type="email"
                        label="Email Principale"
                        placeholder="info@accademia.it"
                        required="true"
                        icon="mail"
                        help="Email di contatto principale"
                    />
                    
                    <x-form-input
                        name="phone"
                        type="tel"
                        label="Telefono"
                        placeholder="+39 02 1234567"
                        required="true"
                        icon="phone"
                        help="Numero di telefono principale"
                    />
                </div>
                
                <div class="mt-6">
                    <x-form-input
                        name="description"
                        type="textarea"
                        label="Descrizione"
                        placeholder="Descrizione della scuola, metodologie di insegnamento, storia..."
                        help="Breve descrizione della scuola (max 500 caratteri)"
                    />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <x-form-input
                        name="website"
                        type="url"
                        label="Sito Web"
                        placeholder="https://www.accademia.it"
                        icon="globe"
                        help="URL del sito web ufficiale"
                    />
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Scuola</label>
                        <input type="file" 
                               name="logo" 
                               accept="image/*"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF fino a 2MB. Dimensioni consigliate: 200x200px</p>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Indirizzo</h3>
                    <p class="text-sm text-gray-600 mt-1">Ubicazione della scuola</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input
                        name="address"
                        label="Via/Indirizzo"
                        placeholder="Via Roma, 123"
                        required="true"
                        icon="location"
                        wrapperClass="md:col-span-2"
                    />
                    
                    <x-form-input
                        name="city"
                        label="Città"
                        placeholder="Milano"
                        required="true"
                        icon="location"
                    />
                    
                    <x-form-input
                        name="state"
                        label="Provincia"
                        placeholder="MI"
                        required="true"
                    />
                    
                    <x-form-input
                        name="postal_code"
                        label="CAP"
                        placeholder="20100"
                        required="true"
                    />
                    
                    <x-form-input
                        name="country"
                        label="Paese"
                        value="Italia"
                        required="true"
                    />
                </div>
            </div>

            <!-- Owner Information -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Proprietario/Amministratore</h3>
                    <p class="text-sm text-gray-600 mt-1">Dati del proprietario della scuola</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input
                        name="owner_name"
                        label="Nome Completo"
                        placeholder="Maria Rossi"
                        required="true"
                        icon="user"
                    />
                    
                    <x-form-input
                        name="owner_email"
                        type="email"
                        label="Email Proprietario"
                        placeholder="maria.rossi@email.com"
                        required="true"
                        icon="mail"
                        help="Questa sarà l'email per l'accesso come admin"
                    />
                    
                    <x-form-input
                        name="owner_phone"
                        type="tel"
                        label="Telefono Proprietario"
                        placeholder="+39 333 1234567"
                        icon="phone"
                    />
                    
                    <x-form-input
                        name="tax_code"
                        label="Codice Fiscale/P.IVA"
                        placeholder="12345678901"
                        help="Codice fiscale o partita IVA"
                    />
                </div>
            </div>

            <!-- Subscription Settings -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Impostazioni Abbonamento</h3>
                    <p class="text-sm text-gray-600 mt-1">Piano tariffario e limiti</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-form-input
                        name="subscription_plan"
                        type="select"
                        label="Piano Abbonamento"
                        placeholder="Seleziona piano..."
                        required="true"
                    >
                        <option value="basic">Basic - €29/mese (50 studenti)</option>
                        <option value="professional">Professional - €49/mese (150 studenti)</option>
                        <option value="enterprise">Enterprise - €99/mese (illimitati)</option>
                        <option value="custom">Piano personalizzato</option>
                    </x-form-input>
                    
                    <x-form-input
                        name="max_students"
                        type="number"
                        label="Massimo Studenti"
                        placeholder="150"
                        min="1"
                        help="Limite massimo studenti (0 = illimitato)"
                    />
                    
                    <x-form-input
                        name="max_courses"
                        type="number"
                        label="Massimo Corsi"
                        placeholder="20"
                        min="1"
                        help="Limite massimo corsi (0 = illimitato)"
                    />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <x-form-input
                        name="trial_ends_at"
                        type="date"
                        label="Fine Periodo di Prova"
                        help="Lascia vuoto se non è in prova"
                    />
                    
                    <x-form-input
                        name="monthly_fee"
                        type="number"
                        step="0.01"
                        min="0"
                        label="Canone Mensile (€)"
                        placeholder="49.00"
                        help="Importo mensile da fatturare"
                    />
                </div>
            </div>

            <!-- Settings -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Impostazioni</h3>
                    <p class="text-sm text-gray-600 mt-1">Configurazioni iniziali</p>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="is_active" 
                                   name="is_active" 
                                   type="checkbox" 
                                   checked
                                   class="focus:ring-rose-500 h-4 w-4 text-rose-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-gray-700">Scuola Attiva</label>
                            <p class="text-gray-500">La scuola può accedere immediatamente al sistema</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="send_welcome_email" 
                                   name="send_welcome_email" 
                                   type="checkbox" 
                                   checked
                                   class="focus:ring-rose-500 h-4 w-4 text-rose-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="send_welcome_email" class="font-medium text-gray-700">Invia Email di Benvenuto</label>
                            <p class="text-gray-500">Invia email con credenziali di accesso al proprietario</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="setup_demo_data" 
                                   name="setup_demo_data" 
                                   type="checkbox"
                                   class="focus:ring-rose-500 h-4 w-4 text-rose-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="setup_demo_data" class="font-medium text-gray-700">Configura Dati Demo</label>
                            <p class="text-gray-500">Crea automaticamente corsi e studenti di esempio</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between">
                    <button type="button" 
                            onclick="window.history.back()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Annulla
                    </button>
                    
                    <div class="flex space-x-3">
                        <button type="submit" 
                                name="action" 
                                value="save_draft"
                                class="inline-flex items-center px-4 py-2 border border-rose-300 text-sm font-medium rounded-lg text-rose-700 bg-rose-50 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4a2 2 0 01-2-2h4a2 2 0 012 2v12M7 16l4 4 4-4M7 16l4-4 4 4"/>
                            </svg>
                            Salva Bozza
                        </button>
                        
                        <button type="submit" 
                                name="action" 
                                value="save_active"
                                class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Crea Scuola
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Auto-generate slug from name
        document.querySelector('input[name="name"]').addEventListener('input', function(e) {
            const name = e.target.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            
            const slugField = document.querySelector('input[name="slug"]');
            if (!slugField.dataset.userModified) {
                slugField.value = slug;
            }
        });
        
        document.querySelector('input[name="slug"]').addEventListener('input', function(e) {
            e.target.dataset.userModified = true;
        });
        
        // Auto-set owner email as main email if empty
        document.querySelector('input[name="email"]').addEventListener('blur', function(e) {
            const ownerEmail = document.querySelector('input[name="owner_email"]');
            if (!ownerEmail.value) {
                ownerEmail.value = e.target.value;
            }
        });
        
        // Update limits based on subscription plan
        document.querySelector('select[name="subscription_plan"]').addEventListener('change', function(e) {
            const plan = e.target.value;
            const maxStudents = document.querySelector('input[name="max_students"]');
            const maxCourses = document.querySelector('input[name="max_courses"]');
            const monthlyFee = document.querySelector('input[name="monthly_fee"]');
            
            switch(plan) {
                case 'basic':
                    maxStudents.value = 50;
                    maxCourses.value = 5;
                    monthlyFee.value = 29.00;
                    break;
                case 'professional':
                    maxStudents.value = 150;
                    maxCourses.value = 20;
                    monthlyFee.value = 49.00;
                    break;
                case 'enterprise':
                    maxStudents.value = 0;
                    maxCourses.value = 0;
                    monthlyFee.value = 99.00;
                    break;
                default:
                    break;
            }
        });
    </script>
    @endpush
</x-app-layout>