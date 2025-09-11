@extends('layouts.app')

@section('title', 'Impostazioni Sistema - Super Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50" x-data="settingsManager()">
    <!-- Header Section -->
    <div class="bg-white/30 backdrop-blur-sm border-b border-white/20 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('super-admin.dashboard') }}" 
                       class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Torna al Dashboard
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">⚙️ Impostazioni Sistema</h1>
                        <p class="text-sm text-gray-600">Configurazione globale e amministrazione sistema</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        Sistema operativo
                    </div>
                    <button @click="saveAllSettings()" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-pink-600 rounded-lg hover:from-rose-600 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salva Tutto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- App Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome Applicazione</label>
                            <input type="text" 
                                   x-model="settings.app_name"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Nome principale del sistema</p>
                        </div>

                        <!-- App Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione</label>
                            <input type="text" 
                                   x-model="settings.app_description"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Breve descrizione del sistema</p>
                        </div>

                        <!-- Contact Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Contatto</label>
                            <input type="email" 
                                   x-model="settings.contact_email"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Email per contatti e supporto</p>
                        </div>

                        <!-- Contact Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefono Contatto</label>
                            <input type="tel" 
                                   x-model="settings.contact_phone"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Numero di telefono per supporto</p>
                        </div>

                        <!-- Timezone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fuso Orario</label>
                            <select x-model="settings.timezone" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                                <option value="Europe/Rome">Europe/Rome (GMT+1)</option>
                                <option value="Europe/London">Europe/London (GMT+0)</option>
                                <option value="America/New_York">America/New_York (GMT-5)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (GMT-8)</option>
                                <option value="Asia/Tokyo">Asia/Tokyo (GMT+9)</option>
                            </select>
                        </div>

                        <!-- Language -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lingua Predefinita</label>
                            <select x-model="settings.default_language" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                                <option value="it">Italiano</option>
                                <option value="en">English</option>
                                <option value="es">Español</option>
                                <option value="fr">Français</option>
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
                                       x-model="settings.maintenance_mode"
                                       class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Attiva manutenzione</span>
                            </label>
                        </div>
                        <div x-show="settings.maintenance_mode" x-transition class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Messaggio Manutenzione</label>
                            <textarea x-model="settings.maintenance_message" 
                                      rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50"
                                      placeholder="Il sistema è temporaneamente in manutenzione. Riprova più tardi."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button @click="saveSystemSettings()" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            Salva Impostazioni Sistema
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings Tab -->
        <div x-show="activeTab === 'email'" x-transition class="space-y-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-lg font-semibold text-gray-900">📧 Configurazione Email</h3>
                    <p class="text-sm text-gray-600">Impostazioni SMTP e notifiche email</p>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Email Enable Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">📬 Sistema Email</h4>
                            <p class="text-sm text-gray-500">Abilita/disabilita l'invio di email dal sistema</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   x-model="settings.email_enabled"
                                   class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Email attive</span>
                        </label>
                    </div>

                    <div x-show="settings.email_enabled" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- SMTP Host -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                                <input type="text" 
                                       x-model="settings.smtp_host"
                                       placeholder="smtp.example.com"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- SMTP Port -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                                <input type="number" 
                                       x-model="settings.smtp_port"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- SMTP Username -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Username SMTP</label>
                                <input type="text" 
                                       x-model="settings.smtp_username"
                                       placeholder="noreply@scuoladanza.it"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- SMTP Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password SMTP</label>
                                <input type="password" 
                                       x-model="settings.smtp_password"
                                       placeholder="••••••••"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- From Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Mittente</label>
                                <input type="text" 
                                       x-model="settings.mail_from_name"
                                       placeholder="Scuola di Danza"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>

                            <!-- From Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Mittente</label>
                                <input type="email" 
                                       x-model="settings.mail_from_address"
                                       placeholder="noreply@scuoladanza.it"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                            </div>
                        </div>

                        <!-- Email Security -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                                <select x-model="settings.smtp_encryption" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 bg-white/50">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="">Nessuno</option>
                                </select>
                            </div>

                            <div class="flex items-center space-x-4 pt-6">
                                <button @click="testEmailConnection()" 
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-100 border border-green-300 rounded-lg hover:bg-green-200 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Test Connessione
                                </button>
                                <button @click="sendTestEmail()" 
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-300 rounded-lg hover:bg-blue-200 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Invia Test
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button @click="saveEmailSettings()" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            Salva Configurazione Email
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings Tab -->
        <div x-show="activeTab === 'security'" x-transition class="space-y-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-red-50 to-pink-50">
                    <h3 class="text-lg font-semibold text-gray-900">🔒 Impostazioni Sicurezza</h3>
                    <p class="text-sm text-gray-600">Configurazione sicurezza e accessi</p>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Session & Login Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timeout Sessione (minuti)</label>
                            <input type="number" 
                                   x-model="settings.session_timeout"
                                   min="5" max="1440"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Durata massima sessione utente</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Tentativi Login</label>
                            <input type="number" 
                                   x-model="settings.max_login_attempts"
                                   min="1" max="10"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                            <p class="mt-1 text-xs text-gray-500">Tentativi prima del blocco</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Blocco Account (minuti)</label>
                            <input type="number" 
                                   x-model="settings.lockout_duration"
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
                                       x-model="settings.password_min_length"
                                       min="6" max="20"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Scadenza Password (giorni)</label>
                                <input type="number" 
                                       x-model="settings.password_expiry_days"
                                       min="0" max="365"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white/50">
                                <p class="mt-1 text-xs text-gray-500">0 = nessuna scadenza</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       x-model="settings.require_uppercase"
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Richiedi lettere maiuscole</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       x-model="settings.require_lowercase"
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Richiedi lettere minuscole</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       x-model="settings.require_numbers"
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Richiedi numeri</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       x-model="settings.require_symbols"
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
                                       x-model="settings.enable_2fa"
                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Abilita 2FA</span>
                            </label>
                        </div>
                        <div x-show="settings.enable_2fa" x-transition class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="settings.force_2fa_admin"
                                           class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Obbligatorio per Admin</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="settings.force_2fa_superadmin"
                                           class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Obbligatorio per Super Admin</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button @click="saveSecuritySettings()" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            Salva Impostazioni Sicurezza
                        </button>
                    </div>
                </div>
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

                    <!-- Maintenance Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cache Management -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-3">🗄️ Gestione Cache</h4>
                            <div class="space-y-3">
                                <button @click="clearCache('application')" 
                                        class="w-full text-left px-3 py-2 text-sm bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                                    Pulisci Cache Applicazione
                                </button>
                                <button @click="clearCache('config')" 
                                        class="w-full text-left px-3 py-2 text-sm bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                    Pulisci Cache Configurazione
                                </button>
                                <button @click="clearCache('routes')" 
                                        class="w-full text-left px-3 py-2 text-sm bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                    Pulisci Cache Route
                                </button>
                                <button @click="clearCache('views')" 
                                        class="w-full text-left px-3 py-2 text-sm bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors">
                                    Pulisci Cache Viste
                                </button>
                            </div>
                        </div>

                        <!-- Database Operations -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-3">🗃️ Operazioni Database</h4>
                            <div class="space-y-3">
                                <button @click="backupDatabase()" 
                                        class="w-full text-left px-3 py-2 text-sm bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                    Backup Database
                                </button>
                                <button @click="optimizeDatabase()" 
                                        class="w-full text-left px-3 py-2 text-sm bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                    Ottimizza Database
                                </button>
                                <button @click="checkDatabaseIntegrity()" 
                                        class="w-full text-left px-3 py-2 text-sm bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors">
                                    Verifica Integrità
                                </button>
                                <button @click="runMigrations()" 
                                        class="w-full text-left px-3 py-2 text-sm bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                                    Esegui Migrazioni
                                </button>
                            </div>
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
                        <div class="flex items-center space-x-2">
                            <select x-model="selectedLogLevel" 
                                    class="text-sm border border-gray-300 rounded-lg px-3 py-1">
                                <option value="all">Tutti i livelli</option>
                                <option value="error">Errori</option>
                                <option value="warning">Warning</option>
                                <option value="info">Info</option>
                            </select>
                            <button @click="refreshLogs()" 
                                    class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                Aggiorna
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Log Entries -->
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div class="flex items-start space-x-3 p-3 bg-red-50 rounded-lg">
                            <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-medium text-red-600">ERROR</span>
                                    <span class="text-xs text-gray-500">{{ now()->subMinutes(5)->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Failed login attempt from IP: 192.168.1.100</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3 p-3 bg-yellow-50 rounded-lg">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-medium text-yellow-600">WARNING</span>
                                    <span class="text-xs text-gray-500">{{ now()->subMinutes(15)->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">High memory usage detected: 85%</p>
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

                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-medium text-green-600">SUCCESS</span>
                                    <span class="text-xs text-gray-500">{{ now()->subHour()->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Database backup completed successfully</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-medium text-blue-600">INFO</span>
                                    <span class="text-xs text-gray-500">{{ now()->subHours(2)->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">New user registration: studente1@example.com</p>
                            </div>
                        </div>
                    </div>

                    <!-- Log Actions -->
                    <div class="mt-6 flex items-center space-x-3">
                        <button @click="downloadLogs()" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download Logs
                        </button>
                        <button @click="clearLogs()" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-100 border border-red-300 rounded-lg hover:bg-red-200 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Pulisci Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Settings Manager -->
<script>
function settingsManager() {
    return {
        activeTab: 'system',
        selectedLogLevel: 'all',
        settings: {
            // System Settings
            app_name: 'Scuola di Danza',
            app_description: 'Sistema di gestione per scuole di danza',
            contact_email: 'info@scuoladanza.it',
            contact_phone: '+39 123 456 7890',
            timezone: 'Europe/Rome',
            default_language: 'it',
            maintenance_mode: false,
            maintenance_message: 'Il sistema è temporaneamente in manutenzione. Riprova più tardi.',
            
            // Email Settings
            email_enabled: true,
            smtp_host: 'smtp.mailtrap.io',
            smtp_port: 587,
            smtp_username: '',
            smtp_password: '',
            smtp_encryption: 'tls',
            mail_from_name: 'Scuola di Danza',
            mail_from_address: 'noreply@scuoladanza.it',
            
            // Security Settings
            session_timeout: 120,
            max_login_attempts: 5,
            lockout_duration: 15,
            password_min_length: 8,
            password_expiry_days: 90,
            require_uppercase: true,
            require_lowercase: true,
            require_numbers: true,
            require_symbols: false,
            enable_2fa: false,
            force_2fa_admin: false,
            force_2fa_superadmin: true
        },
        
        saveSystemSettings() {
            // Simulate saving
            this.showNotification('Impostazioni sistema salvate con successo!', 'success');
        },
        
        saveEmailSettings() {
            if (!this.settings.email_enabled) {
                this.showNotification('Sistema email disabilitato', 'info');
                return;
            }
            this.showNotification('Configurazione email salvata con successo!', 'success');
        },
        
        saveSecuritySettings() {
            this.showNotification('Impostazioni sicurezza salvate con successo!', 'success');
        },
        
        saveAllSettings() {
            this.saveSystemSettings();
            this.saveEmailSettings();
            this.saveSecuritySettings();
            this.showNotification('Tutte le impostazioni sono state salvate!', 'success');
        },
        
        testEmailConnection() {
            this.showNotification('Test connessione email in corso...', 'info');
            // Simulate test
            setTimeout(() => {
                this.showNotification('Connessione email testata con successo!', 'success');
            }, 2000);
        },
        
        sendTestEmail() {
            this.showNotification('Invio email di test in corso...', 'info');
            // Simulate test email
            setTimeout(() => {
                this.showNotification('Email di test inviata! Controlla la casella di posta.', 'success');
            }, 1500);
        },
        
        clearCache(type) {
            this.showNotification(`Pulizia cache ${type} in corso...`, 'info');
            // Simulate cache clear
            setTimeout(() => {
                this.showNotification(`Cache ${type} pulita con successo!`, 'success');
            }, 1000);
        },
        
        backupDatabase() {
            this.showNotification('Backup database in corso...', 'info');
            // Simulate backup
            setTimeout(() => {
                this.showNotification('Backup database completato con successo!', 'success');
            }, 3000);
        },
        
        optimizeDatabase() {
            this.showNotification('Ottimizzazione database in corso...', 'info');
            // Simulate optimization
            setTimeout(() => {
                this.showNotification('Database ottimizzato con successo!', 'success');
            }, 2000);
        },
        
        checkDatabaseIntegrity() {
            this.showNotification('Verifica integrità database in corso...', 'info');
            // Simulate integrity check
            setTimeout(() => {
                this.showNotification('Integrità database verificata - tutto OK!', 'success');
            }, 2500);
        },
        
        runMigrations() {
            this.showNotification('Esecuzione migrazioni in corso...', 'info');
            // Simulate migrations
            setTimeout(() => {
                this.showNotification('Migrazioni eseguite con successo!', 'success');
            }, 1500);
        },
        
        refreshLogs() {
            this.showNotification('Aggiornamento logs...', 'info');
            // Simulate log refresh
            setTimeout(() => {
                this.showNotification('Logs aggiornati!', 'success');
            }, 500);
        },
        
        downloadLogs() {
            this.showNotification('Download logs in corso...', 'info');
            // Simulate download
            setTimeout(() => {
                this.showNotification('Logs scaricati con successo!', 'success');
            }, 1000);
        },
        
        clearLogs() {
            if (confirm('Sei sicuro di voler eliminare tutti i logs? Questa azione non può essere annullata.')) {
                this.showNotification('Pulizia logs in corso...', 'info');
                setTimeout(() => {
                    this.showNotification('Logs eliminati con successo!', 'success');
                }, 1000);
            }
        },
        
        showNotification(message, type) {
            // Simulate notification (in real app you'd use a proper notification system)
            alert(message);
        }
    }
}
</script>

@endsection