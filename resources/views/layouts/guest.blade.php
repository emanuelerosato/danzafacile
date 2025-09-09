<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'Scuola di Danza'))</title>
    <meta name="description" content="@yield('description', 'Accedi al sistema di gestione per scuole di danza')">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex">
        <!-- Left Side - Decorative -->
        <div class="hidden lg:flex lg:flex-1 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-400 via-pink-500 to-purple-600"></div>
            <div class="absolute inset-0 bg-black/20"></div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-10 left-10 w-32 h-32 rounded-full bg-white/10 backdrop-blur-sm"></div>
            <div class="absolute bottom-20 right-20 w-24 h-24 rounded-full bg-white/10 backdrop-blur-sm"></div>
            <div class="absolute top-1/2 left-1/4 w-16 h-16 rounded-full bg-white/10 backdrop-blur-sm"></div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-start p-12 text-white">
                <div class="mb-8">
                    <h1 class="text-4xl font-bold mb-4">Benvenuti nella Scuola di Danza</h1>
                    <p class="text-xl text-white/90 leading-relaxed">
                        Un sistema completo per la gestione di corsi, iscrizioni e tutto quello che serve per la tua scuola di danza.
                    </p>
                </div>
                
                <div class="space-y-4 text-white/80">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Gestione corsi e orari</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Iscrizioni e pagamenti online</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Dashboard personalizzate</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Comunicazione diretta</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Form -->
        <div class="flex-1 flex flex-col justify-center px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50">
            <div class="w-full max-w-md mx-auto">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <a href="/" class="inline-flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-xl mr-3">
                            SD
                        </div>
                        <div class="text-left">
                            <h2 class="text-2xl font-bold text-gray-900">Scuola di Danza</h2>
                            <p class="text-sm text-gray-600">Gestione e Amministrazione</p>
                        </div>
                    </a>
                </div>
                
                <!-- Form Card -->
                <div class="bg-white/80 backdrop-blur-md shadow-xl rounded-2xl border border-white/20 p-8">
                    {{ $slot }}
                </div>
                
                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600">
                        &copy; {{ date('Y') }} Scuola di Danza. Tutti i diritti riservati.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
