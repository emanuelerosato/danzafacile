<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Personalizza Landing Page - {{ $event->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Personalizza l'aspetto e il contenuto della landing page pubblica
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('public.events.show', $event->slug ?? $event->id) }}" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Anteprima
                </a>
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
        <li class="flex items-center">
            <a href="{{ route('admin.events.index') }}" class="text-gray-500 hover:text-gray-700">Eventi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.events.show', $event) }}" class="text-gray-500 hover:text-gray-700">{{ $event->name }}</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Personalizza Landing</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                @php
                    $customization = $event->additional_info['landing_customization'] ?? [];
                @endphp

                <form method="POST" action="{{ route('admin.events.update-landing', $event) }}">
                    @csrf

                    <!-- Contenuto Personalizzato -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contenuto Personalizzato</h3>

                        <div class="space-y-6">
                            <!-- Custom Description -->
                            <div>
                                <label for="custom_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrizione Personalizzata
                                </label>
                                <textarea name="custom_description" id="custom_description" rows="6"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Inserisci una descrizione dettagliata per la landing page...">{{ old('custom_description', $customization['custom_description'] ?? '') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Se vuoto, verrà usata la descrizione standard dell'evento</p>
                                @error('custom_description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Custom Image URL -->
                            <div>
                                <label for="custom_image_url" class="block text-sm font-medium text-gray-700 mb-2">
                                    URL Immagine Personalizzata
                                </label>
                                <input type="url" name="custom_image_url" id="custom_image_url"
                                       value="{{ old('custom_image_url', $customization['custom_image_url'] ?? '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                       placeholder="https://esempio.com/immagine.jpg">
                                <p class="text-xs text-gray-500 mt-1">URL completo dell'immagine da mostrare nella landing page</p>
                                @error('custom_image_url')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Custom CTA Text -->
                            <div>
                                <label for="custom_cta_text" class="block text-sm font-medium text-gray-700 mb-2">
                                    Testo Bottone Iscrizione
                                </label>
                                <input type="text" name="custom_cta_text" id="custom_cta_text"
                                       value="{{ old('custom_cta_text', $customization['custom_cta_text'] ?? '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                       placeholder="Iscriviti Ora">
                                <p class="text-xs text-gray-500 mt-1">Testo personalizzato per il bottone di call-to-action</p>
                                @error('custom_cta_text')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Opzioni Visualizzazione -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Opzioni Visualizzazione</h3>

                        <div class="space-y-4">
                            <!-- Show Location Map -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="show_location_map" id="show_location_map" value="1"
                                           {{ old('show_location_map', $customization['show_location_map'] ?? false) ? 'checked' : '' }}
                                           class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                                </div>
                                <div class="ml-3">
                                    <label for="show_location_map" class="text-sm font-medium text-gray-700">
                                        Mostra Mappa Location
                                    </label>
                                    <p class="text-xs text-gray-500">Visualizza una mappa interattiva della location dell'evento</p>
                                </div>
                            </div>

                            <!-- Show Instructors -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="show_instructors" id="show_instructors" value="1"
                                           {{ old('show_instructors', $customization['show_instructors'] ?? false) ? 'checked' : '' }}
                                           class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                                </div>
                                <div class="ml-3">
                                    <label for="show_instructors" class="text-sm font-medium text-gray-700">
                                        Mostra Instructors
                                    </label>
                                    <p class="text-xs text-gray-500">Visualizza la lista degli instructors dell'evento</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Meta Tags -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO & Meta Tags</h3>

                        <div class="space-y-6">
                            <!-- Meta Title -->
                            <div>
                                <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Meta Title
                                </label>
                                <input type="text" name="meta_title" id="meta_title"
                                       value="{{ old('meta_title', $customization['meta_title'] ?? '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                       placeholder="{{ $event->name }}">
                                <p class="text-xs text-gray-500 mt-1">Titolo per i motori di ricerca (max 60 caratteri)</p>
                                @error('meta_title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meta Description -->
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Meta Description
                                </label>
                                <textarea name="meta_description" id="meta_description" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Descrizione breve per i motori di ricerca...">{{ old('meta_description', $customization['meta_description'] ?? '') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Descrizione per i risultati di ricerca (max 160 caratteri)</p>
                                @error('meta_description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.events.show', $event) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Annulla
                        </a>

                        <div class="flex items-center space-x-3">
                            <a href="{{ route('public.events.show', $event->slug ?? $event->id) }}" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Anteprima Landing
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
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
    </div>
</x-app-layout>
