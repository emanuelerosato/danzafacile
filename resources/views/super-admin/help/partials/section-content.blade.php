{{-- Dynamic Section Content Renderer --}}
{{-- This partial handles the complex rendering of different content types --}}

@php
    $renderContent = function($data, $level = 0) use (&$renderContent) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    // Handle nested arrays
                    if (isset($value['title'])) {
                        // This is a structured content block
                        echo '<div class="mb-6">';
                        echo '<h' . min(6, 4 + $level) . ' class="text-lg font-semibold text-gray-900 mb-3 flex items-center">';
                        
                        // Add appropriate icon based on content type
                        $icons = [
                            'steps' => '📝',
                            'features' => '⚡',
                            'tips' => '💡',
                            'permissions' => '🔐',
                            'operations' => '⚙️',
                            'workflow' => '🔄',
                            'metrics' => '📊',
                            'security' => '🛡️',
                            'troubleshooting' => '🔧'
                        ];
                        
                        $icon = $icons[$key] ?? '▶️';
                        echo '<span class="mr-2">' . $icon . '</span>';
                        echo e($value['title']);
                        echo '</h' . min(6, 4 + $level) . '>';
                        
                        // Render description if exists
                        if (isset($value['description'])) {
                            echo '<p class="text-gray-600 mb-4">' . e($value['description']) . '</p>';
                        }
                        
                        // Recursively render nested content
                        unset($value['title'], $value['description']);
                        $renderContent($value, $level + 1);
                        echo '</div>';
                    } else {
                        // This is a simple array, render as list
                        echo '<div class="mb-4">';
                        echo '<h' . min(6, 4 + $level) . ' class="font-medium text-gray-800 mb-2 capitalize">' . str_replace('_', ' ', $key) . '</h' . min(6, 4 + $level) . '>';
                        echo '<ul class="space-y-2 ml-4">';
                        foreach ($value as $item) {
                            echo '<li class="flex items-start">';
                            echo '<span class="text-rose-500 mr-2">•</span>';
                            if (is_string($item)) {
                                echo '<span class="text-gray-700">' . e($item) . '</span>';
                            } else {
                                $renderContent([$item], $level + 1);
                            }
                            echo '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                } else {
                    // Simple key-value pair
                    if (is_string($value) && !empty($value)) {
                        echo '<div class="mb-4">';
                        echo '<h' . min(6, 5 + $level) . ' class="font-medium text-gray-800 mb-2 capitalize">' . str_replace('_', ' ', $key) . '</h' . min(6, 5 + $level) . '>';
                        echo '<p class="text-gray-700 leading-relaxed">' . e($value) . '</p>';
                        echo '</div>';
                    }
                }
            }
        }
    };
@endphp

{{-- Special handling for specific section types --}}

@if($sectionKey === 'overview')
    {{-- Dashboard Overview with special cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @if(isset($content['key_features']))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                    <span class="mr-2">⭐</span>Funzionalità Chiave
                </h4>
                <ul class="space-y-2">
                    @foreach($content['key_features'] as $feature)
                    <li class="flex items-start text-green-700">
                        <span class="text-green-500 mr-2">✓</span>
                        <span class="text-sm">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if(isset($content['permissions']))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="font-semibold text-red-800 mb-3 flex items-center">
                    <span class="mr-2">⚠️</span>Avviso Importante
                </h4>
                <p class="text-red-700 text-sm">{{ $content['permissions'] }}</p>
            </div>
        @endif
    </div>
    
    @if(isset($content['getting_started']))
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg mb-6">
            <h4 class="font-semibold text-blue-800 mb-2">🚀 Come Iniziare</h4>
            <p class="text-blue-700">{{ $content['getting_started'] }}</p>
        </div>
    @endif

@elseif($sectionKey === 'users')
    {{-- User management with role cards --}}
    @if(isset($content['user_roles']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @foreach($content['user_roles'] as $roleKey => $role)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                <h4 class="font-bold text-gray-900 mb-2">{{ $role['name'] }}</h4>
                <p class="text-gray-600 text-sm mb-3">{{ $role['description'] }}</p>
                @if(isset($role['permissions']))
                    <div class="mb-3">
                        <h5 class="font-medium text-gray-800 text-xs uppercase tracking-wide mb-2">Permessi</h5>
                        <ul class="space-y-1">
                            @foreach($role['permissions'] as $permission)
                            <li class="flex items-center text-xs text-gray-600">
                                <span class="text-green-500 mr-2">✓</span>
                                {{ $permission }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(isset($role['creation']))
                    <div class="bg-gray-50 rounded p-2">
                        <p class="text-xs text-gray-600">
                            <strong>Nota:</strong> {{ $role['creation'] }}
                        </p>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif

@elseif($sectionKey === 'security')
    {{-- Security section with threat cards --}}
    @if(isset($content['common_threats']))
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <h4 class="font-bold text-red-800 mb-4 flex items-center">
                <span class="mr-2">🚨</span>Minacce Comuni
            </h4>
            <div class="space-y-3">
                @foreach($content['common_threats'] as $threat => $description)
                <div class="bg-white rounded p-3 border border-red-100">
                    <h5 class="font-medium text-red-700 capitalize mb-1">{{ str_replace('_', ' ', $threat) }}</h5>
                    <p class="text-red-600 text-sm">{{ $description }}</p>
                </div>
                @endforeach
            </div>
        </div>
    @endif
    
    @if(isset($content['incident_response']))
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 mb-6">
            <h4 class="font-bold text-orange-800 mb-4 flex items-center">
                <span class="mr-2">🆘</span>Incident Response
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($content['incident_response'] as $step => $action)
                <div class="bg-white rounded p-3 border border-orange-100">
                    <h5 class="font-medium text-orange-700 capitalize mb-1">{{ ucfirst($step) }}</h5>
                    <p class="text-orange-600 text-sm">{{ $action }}</p>
                </div>
                @endforeach
            </div>
        </div>
    @endif

@elseif($sectionKey === 'troubleshooting')
    {{-- Troubleshooting with problem-solution cards --}}
    @if(isset($content['common_issues']))
        <div class="space-y-4 mb-6">
            @foreach($content['common_issues'] as $issueKey => $issue)
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h4 class="font-semibold text-gray-900">{{ $issue['title'] }}</h4>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if(isset($issue['symptoms']))
                        <div>
                            <h5 class="font-medium text-red-700 mb-2 flex items-center">
                                <span class="mr-2">🔍</span>Sintomi
                            </h5>
                            <ul class="space-y-1">
                                @foreach($issue['symptoms'] as $symptom)
                                <li class="text-sm text-gray-600 flex items-start">
                                    <span class="text-red-500 mr-2">•</span>{{ $symptom }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        @if(isset($issue['solutions']))
                        <div>
                            <h5 class="font-medium text-green-700 mb-2 flex items-center">
                                <span class="mr-2">💡</span>Soluzioni
                            </h5>
                            <ul class="space-y-1">
                                @foreach($issue['solutions'] as $solution)
                                <li class="text-sm text-gray-600 flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>{{ $solution }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

@elseif($sectionKey === 'future_features')
    {{-- Future features with status badges --}}
    @if(isset($content['planned_features']))
        <div class="space-y-4 mb-6">
            @foreach($content['planned_features'] as $featureKey => $feature)
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-start mb-3">
                    <h4 class="font-bold text-gray-900">{{ $feature['title'] }}</h4>
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        @if($feature['status'] === 'In Sviluppo') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $feature['status'] }}
                    </span>
                </div>
                <p class="text-gray-600 mb-4">{{ $feature['description'] }}</p>
                
                @if(isset($feature['features']))
                <div class="mb-4">
                    <h5 class="font-medium text-gray-800 mb-2">Features Previste</h5>
                    <ul class="space-y-1 ml-4">
                        @foreach($feature['features'] as $subFeature)
                        <li class="text-sm text-gray-600 flex items-start">
                            <span class="text-purple-500 mr-2">•</span>{{ $subFeature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                @if(isset($feature['timeline']))
                <div class="bg-purple-50 rounded p-3 border border-purple-100">
                    <p class="text-sm text-purple-700">
                        <strong>Timeline:</strong> {{ $feature['timeline'] }}
                    </p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif
@endif

{{-- Render remaining content using the recursive function --}}
@php
    // Filter out content that was already specially rendered
    $remainingContent = $content;
    
    if ($sectionKey === 'overview') {
        unset($remainingContent['key_features'], $remainingContent['permissions'], $remainingContent['getting_started']);
    } elseif ($sectionKey === 'users') {
        unset($remainingContent['user_roles']);
    } elseif ($sectionKey === 'security') {
        unset($remainingContent['common_threats'], $remainingContent['incident_response']);
    } elseif ($sectionKey === 'troubleshooting') {
        unset($remainingContent['common_issues']);
    } elseif ($sectionKey === 'future_features') {
        unset($remainingContent['planned_features']);
    }
    
    // Remove intro as it's handled in the main template
    unset($remainingContent['intro']);
@endphp

@if(!empty($remainingContent))
    <div class="space-y-6">
        {!! $renderContent($remainingContent) !!}
    </div>
@endif