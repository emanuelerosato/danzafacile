<!-- Accessible Navigation Component -->
<nav role="navigation" aria-label="Navigazione principale" class="bg-white shadow-sm">
    <!-- Skip to main content link -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 
              bg-blue-600 text-white px-4 py-2 rounded-md z-50">
        Salta al contenuto principale
    </a>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" 
                   aria-label="Vai alla dashboard - {{ config('app.name') }}"
                   class="flex items-center">
                    <span class="text-2xl" role="img" aria-label="Logo danza">🩰</span>
                    <span class="ml-2 text-xl font-bold text-pink-600">{{ config('app.name') }}</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                @auth
                    @php
                        $menuItems = app(App\Services\CacheService::class)->getMenuData(auth()->user());
                    @endphp
                    
                    <ul role="menubar" class="flex items-center space-x-6">
                        @foreach($menuItems as $key => $item)
                            <li role="none">
                                <a href="{{ $item['url'] }}" 
                                   role="menuitem"
                                   class="text-gray-600 hover:text-pink-600 px-3 py-2 rounded-md text-sm font-medium
                                          focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2
                                          transition-colors duration-200
                                          {{ request()->is(trim(str_replace(url(''), '', $item['url']), '/')) ? 'text-pink-600 bg-pink-50' : '' }}"
                                   aria-current="{{ request()->is(trim(str_replace(url(''), '', $item['url']), '/')) ? 'page' : 'false' }}">
                                    <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                                    <span class="ml-2">{{ ucfirst(str_replace('-', ' ', $key)) }}</span>
                                </a>
                            </li>
                        @endforeach
                        
                        <!-- User menu -->
                        <li role="none" x-data="{ open: false }" class="relative">
                            <button @click="open = !open" 
                                    role="menuitem"
                                    aria-haspopup="true"
                                    :aria-expanded="open"
                                    aria-label="Menu utente: {{ auth()->user()->full_name }}"
                                    class="flex items-center text-gray-600 hover:text-pink-600 px-3 py-2 rounded-md text-sm font-medium
                                           focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                                <span>{{ auth()->user()->first_name ?? 'Utente' }}</span>
                                <i class="fas fa-chevron-down ml-2" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
                            </button>
                            
                            <div x-show="open" 
                                 x-cloak
                                 @click.away="open = false"
                                 role="menu"
                                 aria-label="Menu utente"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                <a href="{{ route('profile.edit') }}" 
                                   role="menuitem"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600
                                          focus:outline-none focus:bg-pink-50 focus:text-pink-600">
                                    <i class="fas fa-user mr-2" aria-hidden="true"></i>
                                    Profilo
                                </a>
                                
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" 
                                            role="menuitem"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600
                                                   focus:outline-none focus:bg-pink-50 focus:text-pink-600">
                                        <i class="fas fa-sign-out-alt mr-2" aria-hidden="true"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" 
                           class="text-gray-600 hover:text-pink-600 px-3 py-2 rounded-md text-sm font-medium
                                  focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                            Accedi
                        </a>
                        <a href="{{ route('register') }}" 
                           class="bg-pink-600 text-white px-4 py-2 rounded-md text-sm font-medium
                                  hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                            Registrati
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button type="button" 
                        x-data="{ open: false }"
                        @click="open = !open"
                        :aria-expanded="open"
                        aria-controls="mobile-menu"
                        aria-label="Apri menu di navigazione"
                        class="text-gray-600 hover:text-pink-600 p-2 rounded-md
                               focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                    <i class="fas fa-bars" x-show="!open" aria-hidden="true"></i>
                    <i class="fas fa-times" x-show="open" x-cloak aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div id="mobile-menu" 
         x-data="{ open: false }"
         x-show="open"
         x-cloak
         class="md:hidden bg-white border-t border-gray-200">
        @auth
            @php
                $menuItems = app(App\Services\CacheService::class)->getMenuData(auth()->user());
            @endphp
            
            <div class="px-2 pt-2 pb-3 space-y-1">
                @foreach($menuItems as $key => $item)
                    <a href="{{ $item['url'] }}" 
                       class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-pink-600 hover:bg-pink-50
                              focus:outline-none focus:ring-2 focus:ring-pink-500 rounded-md
                              {{ request()->is(trim(str_replace(url(''), '', $item['url']), '/')) ? 'text-pink-600 bg-pink-50' : '' }}"
                       aria-current="{{ request()->is(trim(str_replace(url(''), '', $item['url']), '/')) ? 'page' : 'false' }}">
                        <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                        <span class="ml-2">{{ ucfirst(str_replace('-', ' ', $key)) }}</span>
                    </a>
                @endforeach
                
                <hr class="my-4 border-gray-200">
                
                <a href="{{ route('profile.edit') }}" 
                   class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-pink-600 hover:bg-pink-50
                          focus:outline-none focus:ring-2 focus:ring-pink-500 rounded-md">
                    <i class="fas fa-user mr-2" aria-hidden="true"></i>
                    Profilo
                </a>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full text-left block px-3 py-2 text-base font-medium text-gray-600 hover:text-pink-600 hover:bg-pink-50
                                   focus:outline-none focus:ring-2 focus:ring-pink-500 rounded-md">
                        <i class="fas fa-sign-out-alt mr-2" aria-hidden="true"></i>
                        Logout
                    </button>
                </form>
            </div>
        @else
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('login') }}" 
                   class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-pink-600 hover:bg-pink-50
                          focus:outline-none focus:ring-2 focus:ring-pink-500 rounded-md">
                    Accedi
                </a>
                <a href="{{ route('register') }}" 
                   class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-pink-600 hover:bg-pink-50
                          focus:outline-none focus:ring-2 focus:ring-pink-500 rounded-md">
                    Registrati
                </a>
            </div>
        @endauth
    </div>
</nav>