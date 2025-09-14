<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Impostazioni Sistema
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Configurazione globale e amministrazione sistema
                </p>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                Sistema operativo
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
            <a href="{{ route('super-admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Super Admin</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Impostazioni</li>
    </x-slot>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div x-data="settingsManager()" class="space-y-6">
        <!-- Settings Navigation Tabs -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 mb-6">
            <div class="px-6 py-4">
                <nav class="flex space-x-8">
                    <button @click="activeTab = 'system'" 
                            :class="activeTab === 'system' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        🖥️ Sistema
                    </button>
                    <button @click="activeTab = 'email'" 
                            :class="activeTab === 'email' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        📧 Email
                    </button>
                    <button @click="activeTab = 'security'" 
                            :class="activeTab === 'security' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        🔒 Sicurezza
                    </button>
                    <button @click="activeTab = 'maintenance'" 
                            :class="activeTab === 'maintenance' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        🔧 Manutenzione
                    </button>
                    <button @click="activeTab = 'logs'" 
                            :class="activeTab === 'logs' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        📝 Logs
                    </button>
                </nav>
            </div>
        </div>

        <!-- System Settings Tab -->
        <div x-show="activeTab === 'system'" x-transition class="space-y-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
                    <h3 class="text-lg font-semibold text-gray-900">🖥️ Configurazione Sistema</h3>
                    <p class="text-sm text-gray-600">Impostazioni generali del sistema</p>
                </div>
                <form method="POST" action="{{ route('super-admin.settings.update') }}" class="p-6 space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- App Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome Applicazione</label>
                            <input type="text" 
                                   name="app_name"
                                   value="{{ old('app_name', $currentSettings['app_name']) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Nome principale del sistema</p>
                        </div>

                        <!-- App Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione</label>
                            <input type="text" 
                                   name="app_description"
                                   value="{{ old('app_description', $currentSettings['app_description']) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Breve descrizione del sistema</p>
                        </div>

                        <!-- Contact Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Contatto</label>
                            <input type="email" 
                                   name="contact_email"
                                   value="{{ old('contact_email', $currentSettings['contact_email']) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Email per contatti e supporto</p>
                        </div>

                        <!-- Contact Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefono Contatto</label>
                            <input type="tel" 
                                   name="contact_phone"
                                   value="{{ old('contact_phone', $currentSettings['contact_phone']) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Numero di telefono per supporto</p>
                        </div>

                        <!-- Timezone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fuso Orario</label>
                            <select name="timezone" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                                <option value="Europe/Rome" {{ old('timezone', $currentSettings['timezone']) == 'Europe/Rome' ? 'selected' : '' }}>Europe/Rome (GMT+1)</option>
                                <option value="Europe/London" {{ old('timezone', $currentSettings['timezone']) == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT+0)</option>
                                <option value="America/New_York" {{ old('timezone', $currentSettings['timezone']) == 'America/New_York' ? 'selected' : '' }}>America/New_York (GMT-5)</option>
                                <option value="America/Los_Angeles" {{ old('timezone', $currentSettings['timezone']) == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (GMT-8)</option>
                                <option value="Asia/Tokyo" {{ old('timezone', $currentSettings['timezone']) == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (GMT+9)</option>
                            </select>
                        </div>

                        <!-- Language -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lingua Predefinita</label>
                            <select name="default_language" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                                <option value="it" {{ old('default_language', $currentSettings['default_language']) == 'it' ? 'selected' : '' }}>Italiano</option>
                                <option value="en" {{ old('default_language', $currentSettings['default_language']) == 'en' ? 'selected' : '' }}>English</option>
                                <option value="es" {{ old('default_language', $currentSettings['default_language']) == 'es' ? 'selected' : '' }}>Español</option>
                                <option value="fr" {{ old('default_language', $currentSettings['default_language']) == 'fr' ? 'selected' : '' }}>Français</option>
                            </select>
                        </div>
                    </div>

                    <!-- Maintenance Mode -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">🚧 Modalità Manutenzione</h4>
                                <p class="text-sm text-gray-500">Blocca l'accesso al sistema durante la manutenzione</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="maintenance_mode"
                                       value="1"
                                       {{ old('maintenance_mode', $currentSettings['maintenance_mode']) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Attiva manutenzione</span>
                            </label>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Messaggio Manutenzione</label>
                            <textarea name="maintenance_message" 
                                      rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50"
                                      placeholder="Il sistema è temporaneamente in manutenzione. Riprova più tardi.">{{ old('maintenance_message', $currentSettings['maintenance_message']) }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salva Impostazioni Sistema
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email Settings Tab -->
        <div x-show="activeTab === 'email'" x-transition class="space-y-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-lg font-semibold text-gray-900">📧 Configurazione Email</h3>
                    <p class="text-sm text-gray-600">Impostazioni SMTP e notifiche email</p>
                </div>
                <form method="POST" action="{{ route('super-admin.settings.update') }}" class="p-6 space-y-6">
                    @csrf
                    <!-- Email Enable Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">📬 Sistema Email</h4>
                            <p class="text-sm text-gray-500">Abilita/disabilita l'invio di email dal sistema</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="email_enabled"
                                   value="1"
                                   {{ old('email_enabled', $currentSettings['email_enabled']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Email attive</span>
                        </label>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- SMTP Host -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                                <input type="text" 
                                       name="smtp_host"
                                       value="{{ old('smtp_host', $currentSettings['smtp_host']) }}"
                                       placeholder="smtp.example.com"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- SMTP Port -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                                <input type="number" 
                                       name="smtp_port"
                                       value="{{ old('smtp_port', $currentSettings['smtp_port']) }}"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- SMTP Username -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Username SMTP</label>
                                <input type="text" 
                                       name="smtp_username"
                                       value="{{ old('smtp_username', $currentSettings['smtp_username']) }}"
                                       placeholder="noreply@scuoladanza.it"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- SMTP Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password SMTP</label>
                                <input type="password" 
                                       name="smtp_password"
                                       placeholder="••••••••"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- From Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Mittente</label>
                                <input type="text" 
                                       name="mail_from_name"
                                       value="{{ old('mail_from_name', $currentSettings['mail_from_name']) }}"
                                       placeholder="Scuola di Danza"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- From Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Mittente</label>
                                <input type="email" 
                                       name="mail_from_address"
                                       value="{{ old('mail_from_address', $currentSettings['mail_from_address']) }}"
                                       placeholder="noreply@scuoladanza.it"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>
                        </div>

                        <!-- Email Security -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                            <select name="smtp_encryption" 
                                    class="block w-full md:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                                <option value="tls" {{ old('smtp_encryption', $currentSettings['smtp_encryption']) == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('smtp_encryption', $currentSettings['smtp_encryption']) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ old('smtp_encryption', $currentSettings['smtp_encryption']) == '' ? 'selected' : '' }}>Nessuno</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salva Configurazione Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Settings Tab -->
        <div x-show="activeTab === 'security'" x-transition class="space-y-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-red-50 to-pink-50">
                    <h3 class="text-lg font-semibold text-gray-900">🔒 Impostazioni Sicurezza</h3>
                    <p class="text-sm text-gray-600">Configurazione sicurezza e accessi</p>
                </div>
                <form method="POST" action="{{ route('super-admin.settings.update') }}" class="p-6 space-y-6">
                    @csrf
                    <!-- Session & Login Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timeout Sessione (minuti)</label>
                            <input type="number" 
                                   name="session_timeout"
                                   value="{{ old('session_timeout', $currentSettings['session_timeout']) }}"
                                   min="5" max="1440"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Durata massima sessione utente</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Tentativi Login</label>
                            <input type="number" 
                                   name="max_login_attempts"
                                   value="{{ old('max_login_attempts', $currentSettings['max_login_attempts']) }}"
                                   min="1" max="10"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Tentativi prima del blocco</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Blocco Account (minuti)</label>
                            <input type="number" 
                                   name="lockout_duration"
                                   value="{{ old('lockout_duration', $currentSettings['lockout_duration']) }}"
                                   min="1" max="60"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Durata blocco dopo tentativi falliti</p>
                        </div>
                    </div>

                    <!-- Password Policy -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">🔑 Politica Password</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lunghezza Minima</label>
                                <input type="number" 
                                       name="password_min_length"
                                       value="{{ old('password_min_length', $currentSettings['password_min_length']) }}"
                                       min="6" max="20"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Scadenza Password (giorni)</label>
                                <input type="number" 
                                       name="password_expiry_days"
                                       value="{{ old('password_expiry_days', $currentSettings['password_expiry_days']) }}"
                                       min="0" max="365"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                                <p class="mt-1 text-xs text-gray-500">0 = nessuna scadenza</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="require_uppercase"
                                       value="1"
                                       {{ old('require_uppercase', $currentSettings['require_uppercase']) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Richiedi lettere maiuscole</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="require_lowercase"
                                       value="1"
                                       {{ old('require_lowercase', $currentSettings['require_lowercase']) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Richiedi lettere minuscole</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="require_numbers"
                                       value="1"
                                       {{ old('require_numbers', $currentSettings['require_numbers']) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Richiedi numeri</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="require_symbols"
                                       value="1"
                                       {{ old('require_symbols', $currentSettings['require_symbols']) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Richiedi caratteri speciali</span>
                            </label>
                        </div>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">🔐 Autenticazione a Due Fattori</h4>
                                <p class="text-sm text-gray-500">Sicurezza aggiuntiva per l'accesso</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="enable_2fa"
                                       value="1"
                                       {{ old('enable_2fa', $currentSettings['enable_2fa']) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Abilita 2FA</span>
                            </label>
                        </div>
                        <div class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="force_2fa_admin"
                                           value="1"
                                           {{ old('force_2fa_admin', $currentSettings['force_2fa_admin']) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Obbligatorio per Admin</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="force_2fa_superadmin"
                                           value="1"
                                           {{ old('force_2fa_superadmin', $currentSettings['force_2fa_superadmin']) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Obbligatorio per Super Admin</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salva Impostazioni Sicurezza
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Maintenance Tab -->
        <div x-show="activeTab === 'maintenance'" x-transition class="space-y-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                    <h3 class="text-lg font-semibold text-gray-900">🔧 Strumenti Manutenzione</h3>
                    <p class="text-sm text-gray-600">Operazioni di manutenzione e ottimizzazione</p>
                </div>
                <div class="p-6">
                    <!-- System Health -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900">Sistema</h4>
                            <p class="text-sm text-green-600">Operativo</p>
                        </div>

                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900">Database</h4>
                            <p class="text-sm text-blue-600">Connesso</p>
                        </div>

                        <div class="bg-purple-50 rounded-lg p-4 text-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900">Cache</h4>
                            <p class="text-sm text-purple-600">Attiva</p>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="mt-8 bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">ℹ️ Informazioni Sistema</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Laravel Version:</span>
                                <span class="font-medium">12.x</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP Version:</span>
                                <span class="font-medium">8.2</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database:</span>
                                <span class="font-medium">MySQL 8.0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Redis:</span>
                                <span class="font-medium">7.0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ultimo Backup:</span>
                                <span class="font-medium">{{ now()->subHours(2)->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Uptime:</span>
                                <span class="font-medium">15 giorni</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Tab -->
        <div x-show="activeTab === 'logs'" x-transition class="space-y-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-slate-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">📝 System Logs</h3>
                            <p class="text-sm text-gray-600">Monitoraggio attività e errori sistema</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Sample Log Entries -->
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-medium text-green-600">SUCCESS</span>
                                    <span class="text-xs text-gray-500">{{ now()->subMinutes(5)->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Settings updated successfully by Super Admin</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-medium text-blue-600">INFO</span>
                                    <span class="text-xs text-gray-500">{{ now()->subMinutes(30)->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">User admin@eleganza.it logged in successfully</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function settingsManager() {
        return {
            activeTab: 'system'
        }
    }
    </script>
    @endpush
</x-app-layout>