<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Email Funnel Marketing
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestisci i template delle email automatiche per i lead
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('super-admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Email Funnel</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Alert successo --}}
                @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif

                {{-- Statistiche --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Totale Email</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Attive</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-gray-500 to-gray-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Inattive</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info Box --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-blue-900 mb-2">Come funziona il Funnel Email</h3>
                            <p class="text-sm text-blue-800 mb-2">
                                Quando un nuovo lead si iscrive, vengono automaticamente schedulate tutte le email attive del funnel.
                            </p>
                            <ul class="text-sm text-blue-800 space-y-1 ml-4">
                                <li>• Ogni email ha un <strong>delay_days</strong> che determina quando verrà inviata</li>
                                <li>• I placeholder <code class="bg-blue-100 px-1 rounded">@{{ '{{' }}Nome@{{ '}}' }}</code>, <code class="bg-blue-100 px-1 rounded">@{{ '{{' }}Email@{{ '}}' }}</code>, <code class="bg-blue-100 px-1 rounded">@{{ '{{' }}Telefono@{{ '}}' }}</code> vengono sostituiti automaticamente</li>
                                <li>• Il sistema invia le email ogni ora tramite scheduler Laravel</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Lista Template Email --}}
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Template Email del Funnel</h3>
                        <p class="text-sm text-gray-600 mt-1">Sequenza automatica di 5 email in stile marketing diretto</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sequenza
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nome Template
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Oggetto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Delay (giorni)
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stato
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Azioni
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($templates as $template)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 text-white text-sm font-bold">
                                            {{ $template->sequence_order }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $template->name }}</p>
                                        @if($template->notes)
                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($template->notes, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ Str::limit($template->subject, 50) }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $template->delay_days }} {{ $template->delay_days === 0 ? 'Immediata' : ($template->delay_days === 1 ? 'giorno' : 'giorni') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($template->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Attiva
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Inattiva
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('super-admin.email-funnel.edit', $template) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-xs font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Modifica
                                        </a>

                                        <form action="{{ route('super-admin.email-funnel.toggle-active', $template) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 {{ $template->is_active ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-xs font-medium rounded-lg transition-all duration-200">
                                                @if($template->is_active)
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                    Disattiva
                                                @else
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Attiva
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun template trovato</h3>
                                        <p class="mt-1 text-sm text-gray-500">Esegui il seeder per creare i template iniziali</p>
                                        <div class="mt-4">
                                            <code class="px-3 py-2 bg-gray-100 rounded text-xs">php artisan db:seed --class=EmailTemplateSeeder</code>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Comandi Utili --}}
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">🛠️ Comandi Utili</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Processa email schedulate manualmente:</p>
                            <code class="block px-3 py-2 bg-gray-800 text-green-400 rounded text-xs">php artisan emails:process-scheduled</code>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Limita numero di email da processare:</p>
                            <code class="block px-3 py-2 bg-gray-800 text-green-400 rounded text-xs">php artisan emails:process-scheduled --limit=100</code>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Verifica scheduler attivo (cron):</p>
                            <code class="block px-3 py-2 bg-gray-800 text-green-400 rounded text-xs">crontab -l | grep schedule:run</code>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
