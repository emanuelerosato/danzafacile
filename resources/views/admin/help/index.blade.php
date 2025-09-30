<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Guida Admin
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Documentazione completa per la gestione della scuola
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- School Badge -->
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-400 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-500">Scuola: {{ $school->name }}</span>
                </div>
                <!-- Version -->
                <div class="bg-rose-100 text-rose-800 px-3 py-1.5 rounded-lg text-sm font-medium">
                    {{ $schoolStats['version'] }}
                </div>
                <!-- Ticket Link -->
                <a href="{{ route('admin.tickets.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Supporto Ticket
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
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Admin</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Guida</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-data="helpSystem()" class="space-y-6">

                <!-- Search and Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
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
                                       class="block w-full pl-10 pr-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg leading-5 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                       placeholder="Cerca nella guida... (es. 'corsi', 'pagamenti', 'ticket')">
                            </div>
                            <!-- Search Results -->
                            <div x-show="searchResults.length > 0" x-transition class="absolute z-20 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 w-full max-w-lg">
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
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="flex items-center px-3 py-2 bg-blue-50 rounded-lg">
                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-blue-700">{{ $schoolStats['total_students'] }} Studenti</span>
                            </div>
                            <div class="flex items-center px-3 py-2 bg-green-50 rounded-lg">
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span class="text-sm font-medium text-green-700">{{ $schoolStats['active_courses'] }} Corsi</span>
                            </div>
                            <div class="flex items-center px-3 py-2 bg-purple-50 rounded-lg">
                                <svg class="h-5 w-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-purple-700">{{ $schoolStats['total_staff'] }} Staff</span>
                            </div>
                            @if($schoolStats['open_tickets'] > 0)
                            <div class="flex items-center px-3 py-2 bg-red-50 rounded-lg">
                                <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <span class="text-sm font-medium text-red-700">{{ $schoolStats['open_tickets'] }} Ticket</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Welcome Card -->
                <div class="bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg shadow text-white p-8 mb-8">
                    <div class="max-w-4xl">
                        <h2 class="text-3xl font-bold mb-4">👋 Benvenuto nella Guida Admin</h2>
                        <p class="text-rose-100 text-lg mb-6">
                            Questa guida completa ti aiuterà a gestire al meglio la tua scuola.
                            Ogni sezione include istruzioni dettagliate, best practices e consigli pratici.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                                <div class="text-3xl mb-2">🎯</div>
                                <div class="font-medium">{{ count($helpSections) }} Sezioni</div>
                                <div class="text-sm text-rose-100">Documentate</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                                <div class="text-3xl mb-2">🔍</div>
                                <div class="font-medium">Ricerca</div>
                                <div class="text-sm text-rose-100">Real-time</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                                <div class="text-3xl mb-2">📱</div>
                                <div class="font-medium">Responsive</div>
                                <div class="text-sm text-rose-100">Design</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
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
                         class="bg-white rounded-lg shadow overflow-hidden transition-all duration-300 hover:shadow-lg"
                         x-data="{ expanded: false }">

                        <!-- Section Header -->
                        <div class="p-6 cursor-pointer hover:bg-gray-50 transition-colors duration-200"
                             @click="expanded = !expanded">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="text-4xl mr-4 p-3 rounded-lg bg-gradient-to-r from-rose-100 to-purple-100">
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
                             class="border-t border-gray-100 bg-gray-50">
                            <div class="p-6 space-y-6">

                                <!-- Intro -->
                                @if(isset($section['content']['intro']))
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                                    <p class="text-blue-800 leading-relaxed">{{ $section['content']['intro'] }}</p>
                                </div>
                                @endif

                                <!-- Key Features -->
                                @if(isset($section['content']['key_features']))
                                <div>
                                    <h4 class="font-semibold text-lg text-gray-900 mb-3">⭐ Funzionalità Principali</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($section['content']['key_features'] as $feature)
                                        <div class="flex items-start space-x-2 bg-white p-3 rounded-lg">
                                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-gray-700">{{ $feature }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Operations (Nested Structure) -->
                                @if(isset($section['content']['operations']))
                                <div class="space-y-6">
                                    @foreach($section['content']['operations'] as $opKey => $operation)
                                    <div class="bg-white p-5 rounded-lg border border-gray-200">
                                        <h4 class="font-semibold text-lg text-gray-900 mb-4">{{ $operation['title'] }}</h4>

                                        @if(isset($operation['steps']))
                                        <div class="space-y-3 mb-4">
                                            @foreach($operation['steps'] as $index => $step)
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                                    {{ $index + 1 }}
                                                </div>
                                                <p class="text-gray-700 pt-1">{{ $step }}</p>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif

                                        @if(isset($operation['features']))
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($operation['features'] as $feature)
                                            <div class="flex items-start space-x-2">
                                                <svg class="w-4 h-4 text-green-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-gray-600 text-sm">{{ $feature }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif

                                        @if(isset($operation['tips']))
                                        <div class="mt-4 bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-400">
                                            <div class="flex items-start space-x-2">
                                                <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                <span class="text-yellow-800 text-sm">{{ $operation['tips'] }}</span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                <!-- Workflow (if present) -->
                                @if(isset($section['content']['workflow']))
                                <div>
                                    <h4 class="font-semibold text-lg text-gray-900 mb-3">🔄 Workflow</h4>
                                    <div class="space-y-3">
                                        @foreach($section['content']['workflow'] as $index => $step)
                                        <div class="flex items-start space-x-3 bg-white p-4 rounded-lg">
                                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-full flex items-center justify-center font-bold">
                                                {{ $index + 1 }}
                                            </div>
                                            <p class="text-gray-700 pt-1">{{ $step }}</p>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Best Practices -->
                                @if(isset($section['content']['best_practices']))
                                <div>
                                    <h4 class="font-semibold text-lg text-gray-900 mb-3">✅ Best Practices</h4>
                                    <div class="space-y-2">
                                        @foreach($section['content']['best_practices'] as $practice)
                                        <div class="flex items-start space-x-2 bg-green-50 p-3 rounded-lg border-l-4 border-green-400">
                                            <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-green-800">{{ $practice }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Security Tips -->
                                @if(isset($section['content']['security_tips']))
                                <div>
                                    <h4 class="font-semibold text-lg text-gray-900 mb-3">🔒 Sicurezza</h4>
                                    <div class="space-y-2">
                                        @foreach($section['content']['security_tips'] as $tip)
                                        <div class="flex items-start space-x-2 bg-red-50 p-3 rounded-lg border-l-4 border-red-400">
                                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-red-800">{{ $tip }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Common Issues -->
                                @if(isset($section['content']['common_issues']))
                                <div>
                                    <h4 class="font-semibold text-lg text-gray-900 mb-3">⚠️ Problemi Comuni</h4>
                                    <div class="space-y-4">
                                        @foreach($section['content']['common_issues'] as $issue)
                                        <div class="bg-orange-50 p-4 rounded-lg border-l-4 border-orange-400">
                                            <p class="font-medium text-orange-900 mb-2">{{ $issue['problem'] }}</p>
                                            <p class="text-orange-800">{{ $issue['solution'] }}</p>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Footer Help -->
                <div class="bg-white rounded-lg shadow p-8 mt-8">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">❓ Serve Altro Aiuto?</h3>
                        <p class="text-gray-600 mb-6 text-lg">
                            Non hai trovato quello che cercavi? Apri un ticket di supporto per assistenza diretta.
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="{{ route('admin.tickets.index') }}"
                               class="inline-flex items-center px-6 py-3 text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Apri Ticket Support
                            </a>
                            <a href="{{ route('admin.dashboard') }}"
                               class="inline-flex items-center px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Torna alla Dashboard
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
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
                    setTimeout(() => {
                        const expandable = element;
                        if (expandable && expandable.__x) {
                            expandable.__x.$data.expanded = true;
                        }
                    }, 300);
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
