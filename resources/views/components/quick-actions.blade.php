@props(['actions' => []])

<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Azioni Rapide</h3>
    
    <div class="grid grid-cols-2 gap-4">
        @foreach($actions as $action)
            <a href="{{ $action['url'] }}" 
               class="group relative bg-gradient-to-br {{ $action['gradient'] ?? 'from-rose-500 to-pink-600' }} rounded-xl p-4 text-white shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                <div class="flex flex-col items-center text-center space-y-2">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <i class="{{ $action['icon'] }} text-lg"></i>
                    </div>
                    <span class="text-sm font-medium">{{ $action['label'] }}</span>
                </div>
                
                <!-- Hover effect -->
                <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>
        @endforeach
    </div>
</div>