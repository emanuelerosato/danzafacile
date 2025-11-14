<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Template Email
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $emailTemplate->name }}
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
        <li class="flex items-center">
            <a href="{{ route('super-admin.email-funnel.index') }}" class="text-gray-500 hover:text-gray-700">Email Funnel</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Modifica Template</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Alert errori --}}
                @if($errors->any())
                <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <p class="font-semibold mb-2">Errori di validazione:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('super-admin.email-funnel.update', $emailTemplate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        {{-- Colonna sinistra: Form --}}
                        <div class="lg:col-span-2 space-y-6">

                            {{-- Info Base --}}
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informazioni Base</h3>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                                Nome Template <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="name" id="name" value="{{ old('name', $emailTemplate->name) }}"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                                   required>
                                        </div>

                                        <div>
                                            <label for="delay_days" class="block text-sm font-medium text-gray-700 mb-2">
                                                Ritardo (giorni) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" name="delay_days" id="delay_days" min="0" value="{{ old('delay_days', $emailTemplate->delay_days) }}"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                                   required>
                                            <p class="text-xs text-gray-500 mt-1">0 = invio immediato</p>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                            Oggetto Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="subject" id="subject" value="{{ old('subject', $emailTemplate->subject) }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                               placeholder="Es: {{'{{'}}Nome{{'}}'}}, hai visto questa opportunità?"
                                               required>
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                            Note (opzionale)
                                        </label>
                                        <input type="text" name="notes" id="notes" value="{{ old('notes', $emailTemplate->notes) }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                               placeholder="Es: Email di benvenuto con identificazione problema">
                                    </div>

                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}
                                                   class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                                            <span class="ml-2 text-sm text-gray-700">Template attivo</span>
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">Solo i template attivi vengono inclusi nel funnel automatico</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Corpo Email --}}
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Corpo Email (HTML)</h3>

                                <div>
                                    <textarea name="body" id="body" rows="20"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent font-mono text-sm"
                                              required>{{ old('body', $emailTemplate->body) }}</textarea>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Puoi usare HTML completo con stili inline. Ricorda di testare su più client email.
                                    </p>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-between">
                                <a href="{{ route('super-admin.email-funnel.index') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Annulla
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Salva Modifiche
                                </button>
                            </div>

                        </div>

                        {{-- Colonna destra: Helper --}}
                        <div class="space-y-6">

                            {{-- Placeholder --}}
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    Placeholder Disponibili
                                </h3>
                                <div class="space-y-2 text-xs">
                                    <div class="p-2 bg-purple-50 rounded cursor-pointer hover:bg-purple-100" onclick="copyToClipboard('@{{ '{{' }}Nome@{{ '}}' }}')">
                                        <code class="text-purple-700 font-mono">@{{ '{{' }}Nome@{{ '}}' }}</code>
                                        <p class="text-gray-600 mt-0.5">Nome del lead</p>
                                    </div>
                                    <div class="p-2 bg-purple-50 rounded cursor-pointer hover:bg-purple-100" onclick="copyToClipboard('@{{ '{{' }}Email@{{ '}}' }}')">
                                        <code class="text-purple-700 font-mono">@{{ '{{' }}Email@{{ '}}' }}</code>
                                        <p class="text-gray-600 mt-0.5">Email del lead</p>
                                    </div>
                                    <div class="p-2 bg-purple-50 rounded cursor-pointer hover:bg-purple-100" onclick="copyToClipboard('@{{ '{{' }}Telefono@{{ '}}' }}')">
                                        <code class="text-purple-700 font-mono">@{{ '{{' }}Telefono@{{ '}}' }}</code>
                                        <p class="text-gray-600 mt-0.5">Telefono del lead</p>
                                    </div>
                                    <div class="p-2 bg-purple-50 rounded cursor-pointer hover:bg-purple-100" onclick="copyToClipboard('@{{ '{{' }}Scuola@{{ '}}' }}')">
                                        <code class="text-purple-700 font-mono">@{{ '{{' }}Scuola@{{ '}}' }}</code>
                                        <p class="text-gray-600 mt-0.5">Nome scuola</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-3">
                                    Clicca per copiare. I placeholder vengono sostituiti automaticamente.
                                </p>
                            </div>

                            {{-- Best Practices --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <h3 class="text-sm font-semibold text-blue-900 mb-3">💡 Best Practices</h3>
                                <ul class="text-xs text-blue-800 space-y-2">
                                    <li>✓ Usa HTML con CSS inline</li>
                                    <li>✓ Testa su Gmail, Outlook, Apple Mail</li>
                                    <li>✓ Oggetto max 50 caratteri</li>
                                    <li>✓ Personalizza con placeholder</li>
                                    <li>✓ CTA chiara e visibile</li>
                                    <li>✓ Design responsive</li>
                                </ul>
                            </div>

                            {{-- Info Sequenza --}}
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">📊 Info Sequenza</h3>
                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Posizione:</span>
                                        <span class="font-semibold text-gray-900">#{{ $emailTemplate->sequence_order }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Slug:</span>
                                        <code class="text-gray-900 font-mono">{{ $emailTemplate->slug }}</code>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Stato:</span>
                                        <span class="font-semibold {{ $emailTemplate->is_active ? 'text-green-600' : 'text-gray-600' }}">
                                            {{ $emailTemplate->is_active ? 'Attiva' : 'Inattiva' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Visual feedback
                const tooltip = document.createElement('div');
                tooltip.textContent = 'Copiato!';
                tooltip.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg text-sm';
                document.body.appendChild(tooltip);
                setTimeout(() => tooltip.remove(), 2000);
            });
        }
    </script>
    @endpush
</x-app-layout>
