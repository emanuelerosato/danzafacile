<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Storage
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestisci lo spazio disponibile per le tue gallerie
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
        <li class="text-gray-900 font-medium">Storage</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Current Usage Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Utilizzo Corrente</h3>

                    @if($storageInfo['unlimited'])
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-purple-500 mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L11 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c-.25.78.74 1.43 1.403.926l.07-.07a1.99 1.99 0 012.83 0l.07.07c.662.504 1.652-.145 1.403-.926l-.818-2.552a1.99 1.99 0 00-1.13-1.13l-2.552-.818a1 1 0 00-.926 1.403l.07.07a1.99 1.99 0 000 2.83l-.07.07a1 1 0 00-.926 1.403z"/>
                            </svg>
                            <p class="text-xl font-semibold text-gray-900">Storage Illimitato Attivo</p>
                            <p class="text-sm text-gray-600 mt-2">{{ $storageInfo['used_formatted'] }} utilizzati</p>
                        </div>
                    @else
                        <div class="grid md:grid-cols-3 gap-6 mb-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Utilizzato</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $storageInfo['used_formatted'] }}</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Quota Totale</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $storageInfo['quota_gb'] }} GB</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Disponibile</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $storageInfo['remaining_formatted'] }}</p>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-gray-600">Utilizzo Storage</span>
                                <span class="font-semibold text-gray-900">{{ $storageInfo['usage_percent'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="h-4 rounded-full transition-all duration-300
                                    @if($storageInfo['is_full']) bg-red-600
                                    @elseif($storageInfo['is_warning']) bg-yellow-500
                                    @else bg-green-500
                                    @endif"
                                     style="width: {{ min($storageInfo['usage_percent'], 100) }}%">
                                </div>
                            </div>
                        </div>

                        @if($storageInfo['expires_at'])
                            <p class="text-sm text-gray-600 mt-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Quota aggiuntiva scade il <strong>{{ $storageInfo['expires_at']->format('d/m/Y') }}</strong>
                            </p>
                        @endif
                    @endif
                </div>

                {{-- Pricing Plans --}}
                @unless($storageInfo['unlimited'])
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Piani Disponibili</h3>

                        <div class="grid md:grid-cols-4 gap-6">
                            @foreach($plans as $plan)
                                <div class="border rounded-lg p-6
                                    @if($plan['type'] === 'base') bg-gray-50
                                    @elseif($plan['name'] === 'Piano Pro') border-purple-500 border-2 relative
                                    @endif">

                                    @if($plan['name'] === 'Piano Pro')
                                        <span class="absolute top-0 right-0 bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-lg">
                                            Popolare
                                        </span>
                                    @endif

                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $plan['name'] }}</h4>

                                    <div class="mb-4">
                                        @if($plan['gb'])
                                            <p class="text-3xl font-bold text-gray-900">{{ $plan['gb'] }} GB</p>
                                        @else
                                            <p class="text-3xl font-bold text-purple-600">Illimitato</p>
                                        @endif

                                        <p class="text-sm text-gray-600 mt-1">
                                            @if($plan['price'] > 0)
                                                €{{ $plan['price'] }}/mese
                                            @else
                                                Gratis
                                            @endif
                                        </p>
                                    </div>

                                    <ul class="space-y-2 mb-6">
                                        @foreach($plan['features'] as $feature)
                                            <li class="flex items-start text-sm text-gray-700">
                                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if($plan['type'] === 'base')
                                        <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed">
                                            Piano Corrente
                                        </button>
                                    @else
                                        <form action="{{ route('admin.billing.purchase-storage') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="plan_type" value="{{ strtolower(str_replace('Piano ', '', $plan['name'])) }}">
                                            <input type="hidden" name="payment_method" value="paypal">

                                            <button type="submit"
                                                    class="w-full px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105">
                                                Acquista Ora
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endunless

            </div>
        </div>
    </div>
</x-app-layout>
