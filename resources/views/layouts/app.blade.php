<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <title>@yield('title', config('app.name', 'Scuola di Danza'))</title>
    <meta name="description" content="@yield('description', 'Sistema di gestione per scuole di danza - Corsi, iscrizioni e molto altro')">
    <meta name="keywords" content="@yield('keywords', 'scuola danza, corsi danza, iscrizioni danza, gestione scuola, ballet, hip hop, danza moderna')">
    <meta name="author" content="@yield('author', 'Scuola di Danza')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('og_title', config('app.name', 'Scuola di Danza'))">
    <meta property="og:description" content="@yield('og_description', 'Sistema di gestione per scuole di danza - Corsi, iscrizioni e molto altro')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:site_name" content="{{ config('app.name', 'Scuola di Danza') }}">
    <meta property="og:locale" content="it_IT">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', config('app.name', 'Scuola di Danza'))">
    <meta name="twitter:description" content="@yield('twitter_description', 'Sistema di gestione per scuole di danza - Corsi, iscrizioni e molto altro')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/og-default.jpg'))">
    
    <!-- Additional SEO -->
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="theme-color" content="#e91e63">
    
    <!-- Structured Data -->
    @stack('structured-data')
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <x-sidebar />
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Navigation -->
            <header class="bg-white/80 backdrop-blur-md border-b border-rose-100 shadow-sm sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="lg:hidden p-2 rounded-md text-rose-600 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        
                        <!-- Page Title -->
                        @isset($header)
                            <div class="flex-1">
                                <h1 class="text-2xl font-bold text-gray-900">{{ $header }}</h1>
                            </div>
                        @endisset
                        
                        <!-- Right side -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" 
                                        class="p-2 text-rose-600 hover:bg-rose-100 rounded-full focus:outline-none focus:ring-2 focus:ring-rose-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5M4 7h16l-2 9H6L4 7zm0 0L2 3"/>
                                    </svg>
                                    <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-transition
                                     class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-rose-100 z-50">
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-900 mb-3">Notifiche</h3>
                                        <div class="space-y-2">
                                            <div class="p-3 bg-rose-50 rounded-lg">
                                                <p class="text-sm text-gray-800">Nuova iscrizione al corso di Danza Moderna</p>
                                                <p class="text-xs text-gray-500 mt-1">2 ore fa</p>
                                            </div>
                                            <div class="p-3 bg-purple-50 rounded-lg">
                                                <p class="text-sm text-gray-800">Pagamento ricevuto da Maria Rossi</p>
                                                <p class="text-xs text-gray-500 mt-1">5 ore fa</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Menu -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" 
                                        class="flex items-center space-x-3 p-2 rounded-lg hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="hidden md:block text-left">
                                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ Auth::user()->role ?? 'User' }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-transition
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-rose-100 z-50">
                                    <div class="py-1">
                                        <a href="{{ route('profile.edit') }}" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-rose-50">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Profilo
                                        </a>
                                        <a href="#" 
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-rose-50">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Impostazioni
                                        </a>
                                        <div class="border-t border-gray-100"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-rose-50">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Breadcrumb -->
            @if(isset($breadcrumb))
                <nav class="px-4 sm:px-6 lg:px-8 py-3 bg-white/50 border-b border-rose-100">
                    <ol class="flex items-center space-x-2 text-sm">
                        {{ $breadcrumb }}
                    </ol>
                </nav>
            @endif
            
            <!-- Flash Messages -->
            <x-alert />
            
            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                @if(isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden">
    </div>
    
    @stack('scripts')
    
    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>
</body>
</html>
