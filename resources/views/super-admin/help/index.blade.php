<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Guida Super Admin
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Documentazione completa del sistema
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- System Health Badge -->
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-400 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-500">Sistema: {{ $systemStats['system_health'] }}</span>
                </div>
                <!-- Version -->
                <div class="bg-rose-100 text-rose-800 px-3 py-1.5 rounded-lg text-sm font-medium">
                    {{ $systemStats['version'] }}
                </div>
                <!-- Helpdesk Link -->
                <a href="{{ route('super-admin.helpdesk.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Support Helpdesk
                </a>
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
        <li class="text-gray-900 font-medium">Guida</li>
    </x-slot>

    <div x-data="helpSystem()" class="space-y-6">
        
        <!-- Search and Quick Actions -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <!-- Search -->
                <div class="flex-1 max-w-lg">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               @input="filterSections()"
                               class="block w-full pl-10 pr-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg leading-5 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition-colors duration-200"
                               placeholder="Cerca nella guida... (es. 'utenti', 'sicurezza', 'troubleshooting')">
                    </div>
                    <!-- Search Results -->
                    <div x-show="searchResults.length > 0" x-transition class="absolute z-20 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 w-full">
                        <div class="p-2 max-h-64 overflow-y-auto">
                            <template x-for="result in searchResults" :key="result.key">
                                <button @click="scrollToSection(result.key)" 
                                        class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-rose-50 rounded-lg flex items-center transition-colors duration-150">
                                    <span x-text="result.icon" class="text-lg mr-3"></span>
                                    <div>
                                        <div class="font-medium" x-text="result.title"></div>
                                        <div class="text-xs text-gray-500" x-text="result.description"></div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-6">
                    <div class="flex items-center px-3 py-2 bg-rose-50 rounded-lg">
                        <svg class="h-5 w-5 text-rose-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="text-sm font-medium text-rose-700">{{ $systemStats['total_schools'] }} Scuole</span>
                    </div>
                    <div class="flex items-center px-3 py-2 bg-blue-50 rounded-lg">
                        <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-blue-700">{{ $systemStats['total_users'] }} Utenti</span>
                    </div>
                    @if($systemStats['open_tickets'] > 0)
                    <div class="flex items-center px-3 py-2 bg-red-50 rounded-lg">
                        <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700">{{ $systemStats['open_tickets'] }} Ticket Aperti</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-rose-500 to-purple-600 rounded-2xl shadow-lg text-white p-8 mb-8">
            <div class="max-w-4xl">
                <h2 class="text-3xl font-bold mb-4">👋 Benvenuto nella Guida Super Admin</h2>
                <p class="text-rose-100 text-lg mb-6">
                    Questa guida completa ti aiuterà a padroneggiare tutte le funzionalità della dashboard Super Admin. 
                    Ogni sezione include istruzioni dettagliate, best practices e consigli di sicurezza.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                        <div class="text-3xl mb-2">🎯</div>
                        <div class="font-medium">{{ count($helpSections) }} Sezioni</div>
                        <div class="text-sm text-rose-100">Documentate</div>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                        <div class="text-3xl mb-2">🔍</div>
                        <div class="font-medium">Ricerca</div>
                        <div class="text-sm text-rose-100">Real-time</div>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                        <div class="text-3xl mb-2">📱</div>
                        <div class="font-medium">Responsive</div>
                        <div class="text-sm text-rose-100">Design</div>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                        <div class="text-3xl mb-2">🚀</div>
                        <div class="font-medium">Always Updated</div>
                        <div class="text-sm text-rose-100">Content</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Sections -->
        <div class="space-y-6">
            @foreach($helpSections as $key => $section)
            <div id="section-{{ $key }}" 
                 class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden transition-all duration-300 hover:shadow-xl"
                 x-data="{ expanded: false }">
                
                <!-- Section Header -->
                <div class="p-6 cursor-pointer hover:bg-white/50 transition-colors duration-200"
                     @click="expanded = !expanded">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="text-4xl mr-4 p-3 rounded-xl bg-gradient-to-r from-rose-100 to-purple-100">
                                {{ Str::before($section['title'], ' ') }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">{{ Str::after($section['title'], ' ') }}</h3>
                                <p class="text-gray-600 mt-1">{{ $section['description'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="bg-rose-100 text-rose-800 px-3 py-1.5 rounded-full text-sm font-medium">
                                Priorità {{ $section['priority'] }}
                            </div>
                            <svg class="h-6 w-6 text-gray-400 transition-transform duration-200" 
                                 :class="{ 'rotate-180': expanded }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Section Content -->
                <div x-show="expanded" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="border-t border-gray-100 bg-white/60">
                    <div class="p-6 space-y-6">
                        
                        <!-- Intro -->
                        @if(isset($section['content']['intro']))
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-xl">
                            <p class="text-blue-800 leading-relaxed">{{ $section['content']['intro'] }}</p>
                        </div>
                        @endif

                        <!-- Dynamic Content Rendering -->
                        @include('super-admin.help.partials.section-content', [
                            'sectionKey' => $key, 
                            'content' => $section['content']
                        ])

                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer Help -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-8 mt-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">❓ Serve Altro Aiuto?</h3>
                <p class="text-gray-600 mb-6 text-lg">
                    Non hai trovato quello che cercavi? Contatta il supporto tecnico o consulta i log di sistema.
                </p>
                <div class="flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('super-admin.helpdesk.index') }}" 
                       class="inline-flex items-center px-6 py-3 text-white bg-gradient-to-r from-rose-500 to-pink-600 rounded-xl hover:from-rose-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Apri Ticket Support
                    </a>
                    <a href="{{ route('super-admin.logs') }}" 
                       class="inline-flex items-center px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Visualizza Log Sistema
                    </a>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script nonce="@cspNonce">
    function helpSystem() {
        return {
            searchQuery: '',
            searchResults: [],
            
            init() {
                // Auto-expand first section on load
                this.$nextTick(() => {
                    const firstSection = document.querySelector('[x-data*="expanded"]');
                    if (firstSection) {
                        firstSection.__x.$data.expanded = true;
                    }
                });
            },
            
            filterSections() {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    return;
                }
                
                const sections = @json($helpSections);
                const query = this.searchQuery.toLowerCase();
                this.searchResults = [];
                
                Object.keys(sections).forEach(key => {
                    const section = sections[key];
                    const searchableText = (
                        section.title + ' ' + 
                        section.description + ' ' + 
                        JSON.stringify(section.content)
                    ).toLowerCase();
                    
                    if (searchableText.includes(query)) {
                        this.searchResults.push({
                            key: key,
                            title: section.title.split(' ').slice(1).join(' '),
                            icon: section.title.split(' ')[0],
                            description: section.description.substring(0, 80) + '...'
                        });
                    }
                });
            },
            
            scrollToSection(sectionKey) {
                const element = document.getElementById('section-' + sectionKey);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Auto-expand the section
                    const expandable = element.querySelector('[x-data*="expanded"]');
                    if (expandable && expandable.__x) {
                        expandable.__x.$data.expanded = true;
                    }
                }
                this.searchQuery = '';
                this.searchResults = [];
            }
        }
    }

    // Smooth scrolling for anchor links
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a[href^="#"]');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    });
    </script>
    @endpush
</x-app-layout>