@props([
    'headers' => [],
    'searchable' => true,
    'searchPlaceholder' => 'Cerca...',
    'emptyMessage' => 'Nessun dato disponibile'
])

<div x-data="dataTable()" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
    <!-- Table Header -->
    @if($searchable || isset($actions))
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <div class="flex items-center justify-between">
                @if($searchable)
                    <div class="relative max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input x-model="search" 
                               type="text" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                               placeholder="{{ $searchPlaceholder }}">
                    </div>
                @endif
                
                @if(isset($actions))
                    <div class="flex items-center space-x-2">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ isset($header['sortable']) && $header['sortable'] ? 'cursor-pointer hover:bg-gray-100' : '' }}"
                            @if(isset($header['sortable']) && $header['sortable'])
                                @click="sortBy('{{ $header['key'] ?? strtolower($header['label'] ?? $header) }}')"
                            @endif>
                            <div class="flex items-center space-x-1">
                                <span>{{ $header['label'] ?? $header }}</span>
                                @if(isset($header['sortable']) && $header['sortable'])
                                    <svg class="w-4 h-4 text-gray-400" 
                                         :class="{ 
                                            'text-rose-600': sortField === '{{ $header['key'] ?? strtolower($header['label'] ?? $header) }}',
                                            'transform rotate-180': sortField === '{{ $header['key'] ?? strtolower($header['label'] ?? $header) }}' && sortDirection === 'desc'
                                         }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(item, index) in filteredItems" :key="index">
                    {{ $slot }}
                </template>
            </tbody>
        </table>
        
        <!-- Empty State -->
        <div x-show="filteredItems.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $emptyMessage }}</h3>
        </div>
    </div>
</div>

<script nonce="@cspNonce">
    function dataTable() {
        return {
            search: '',
            sortField: '',
            sortDirection: 'asc',
            items: @json($items ?? []),
            
            get filteredItems() {
                let filtered = this.items.filter(item => {
                    if (!this.search) return true;
                    return Object.values(item).some(value => 
                        String(value).toLowerCase().includes(this.search.toLowerCase())
                    );
                });
                
                if (this.sortField) {
                    filtered.sort((a, b) => {
                        let aVal = a[this.sortField];
                        let bVal = b[this.sortField];
                        
                        if (this.sortDirection === 'asc') {
                            return aVal > bVal ? 1 : -1;
                        } else {
                            return aVal < bVal ? 1 : -1;
                        }
                    });
                }
                
                return filtered;
            },
            
            sortBy(field) {
                if (this.sortField === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortField = field;
                    this.sortDirection = 'asc';
                }
            }
        }
    }
</script>