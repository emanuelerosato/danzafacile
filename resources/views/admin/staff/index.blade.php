<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Staff
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione staff della tua scuola
                </p>
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
        <li class="text-gray-900 font-medium">Staff</li>
    </x-slot>




    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Header con Stats -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Gestione Staff</h1>
                            <p class="text-sm text-gray-600 mt-1">Gestisci il personale della scuola e le assegnazioni ai corsi</p>
                        </div>
                        <a href="{{ route('admin.staff.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Nuovo Staff
                        </a>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Staff Totale</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Attivi</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Istruttori</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $stats['instructors'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">In Congedo</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $stats['on_leave'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="GET" class="flex flex-wrap gap-4 items-center">
                        <div class="flex-1 min-w-64">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cerca per nome, email o ID dipendente..."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                        </div>
                        <div>
                            <select name="role" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Tutti i ruoli</option>
                                @foreach(\App\Models\Staff::getAvailableRoles() as $key => $label)
                                    <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="department" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Tutti i dipartimenti</option>
                                @foreach(\App\Models\Staff::getAvailableDepartments() as $key => $label)
                                    <option value="{{ $key }}" {{ request('department') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="employment_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Tutti i tipi</option>
                                @foreach(\App\Models\Staff::getAvailableEmploymentTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ request('employment_type') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Tutti gli status</option>
                                @foreach(\App\Models\Staff::getAvailableStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filtra
                        </button>
                        @if(request()->hasAny(['search', 'role', 'department', 'employment_type', 'status']))
                            <a href="{{ route('admin.staff.index') }}"
                               class="inline-flex items-center px-4 py-2 text-gray-500 hover:text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Bulk Actions -->
                @if($staff->count() > 0)
                    <div class="bg-white rounded-lg shadow p-6">
                        <form id="bulkActionForm" method="POST" action="{{ route('admin.staff.bulk-action') }}">
                            @csrf
                            <div class="flex items-center gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="select-all-staff" class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                    <label for="select-all-staff" class="ml-2 text-sm text-gray-700">Seleziona tutti</label>
                                </div>
                                <div class="flex gap-2">
                                    <select name="action" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                        <option value="">Azioni multiple</option>
                                        <option value="activate">Attiva selezionati</option>
                                        <option value="deactivate">Disattiva selezionati</option>
                                        <option value="delete">Elimina selezionati</option>
                                    </select>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Esegui
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
                <!-- Staff Grid -->
                @if($staff->count() > 0)
                    <div class="staff-table grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-loading>
                        @foreach($staff as $member)
                            <div class="staff-row bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow duration-200" data-staff-id="{{ $member->id }}">
                                <!-- Header -->
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="staff_ids[]" value="{{ $member->id }}" form="bulkActionForm"
                                                   class="staff-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                <div>
                                    <h3 class="font-semibold text-gray-900 line-clamp-1">{{ $member->user->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $member->employee_id }}</p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <span class="text-xs px-2 py-1 rounded-full {{ $member->status_badge }}">
                                    {{ \App\Models\Staff::getAvailableStatuses()[$member->status] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-4">
                        <div class="space-y-3">
                            <!-- Role & Department -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs px-2 py-1 rounded-full {{ $member->role_badge }}">
                                    {{ \App\Models\Staff::getAvailableRoles()[$member->role] }}
                                </span>
                                @if($member->department)
                                    <span class="text-xs text-gray-600">
                                        {{ \App\Models\Staff::getAvailableDepartments()[$member->department] }}
                                    </span>
                                @endif
                            </div>

                            <!-- Contact Info -->
                            <div class="text-sm text-gray-600">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                    {{ $member->user->email }}
                                </div>
                                @if($member->phone)
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-phone text-gray-400"></i>
                                        {{ $member->phone }}
                                    </div>
                                @endif
                            </div>

                            <!-- Employment Info -->
                            <div class="text-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ \App\Models\Staff::getAvailableEmploymentTypes()[$member->employment_type] }}</span>
                                </div>
                                @if($member->hourly_rate)
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-gray-600">Tariffa:</span>
                                        <span class="font-medium text-green-600">{{ $member->formatted_hourly_rate }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Corsi attivi:</span>
                                    <span class="font-medium">{{ $member->active_course_assignments_count }}</span>
                                </div>
                            </div>

                            <!-- Specializations -->
                            @if($member->specializations)
                                <div class="text-sm">
                                    <span class="text-gray-600">Specializzazioni:</span>
                                    <p class="text-gray-900 text-xs mt-1 line-clamp-2">{{ $member->specializations }}</p>
                                </div>
                            @endif

                            <!-- Availability Today -->
                            @if($member->is_available_today)
                                <div class="flex items-center gap-2 text-sm text-green-600">
                                    <i class="fas fa-check-circle"></i>
                                    Disponibile oggi
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.staff.show', $member) }}"
                               class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm px-3 py-2 rounded-lg text-center transition-colors duration-200">
                                <i class="fas fa-eye"></i> Visualizza
                            </a>
                            <a href="{{ route('admin.staff.edit', $member) }}"
                               class="bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm px-3 py-2 rounded-lg transition-colors duration-200">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.staff.toggle-status', $member) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="bg-{{ $member->status === 'active' ? 'yellow' : 'green' }}-50 hover:bg-{{ $member->status === 'active' ? 'yellow' : 'green' }}-100 text-{{ $member->status === 'active' ? 'yellow' : 'green' }}-700 text-sm px-3 py-2 rounded-lg transition-colors duration-200"
                                        title="{{ $member->status === 'active' ? 'Disattiva' : 'Attiva' }}">
                                    <i class="fas fa-{{ $member->status === 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.staff.destroy', $member) }}"
                                  class="inline"
                                  onsubmit="return confirm('Sei sicuro di voler eliminare questo staff member?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-50 hover:bg-red-100 text-red-700 text-sm px-3 py-2 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

                    </div>

                    <!-- Pagination -->
                    <div class="bg-white rounded-lg shadow p-6">
                        {{ $staff->withQueryString()->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="empty-state bg-white rounded-lg shadow p-12">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="text-xl font-medium text-gray-900 mb-2 mt-4">Nessun staff trovato</h3>
                            <p class="text-gray-600 mb-6">
                                @if(request()->hasAny(['search', 'role', 'department', 'employment_type', 'status']))
                                    Modifica i filtri per trovare altri membri dello staff o aggiungi un nuovo staff member.
                                @else
                                    Inizia aggiungendo il primo membro dello staff della tua scuola.
                                @endif
                            </p>
                            <div class="flex justify-center gap-4">
                                <a href="{{ route('admin.staff.create') }}"
                                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Aggiungi Primo Staff
                                </a>
                                @if(request()->hasAny(['search', 'role', 'department', 'employment_type', 'status']))
                                    <a href="{{ route('admin.staff.index') }}"
                                       class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Reset Filtri
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>

@push('scripts')
@vite('resources/js/admin/staff/staff-manager.js')
<script>
    // Mark this as a staff page for the JavaScript system
    document.addEventListener('DOMContentLoaded', function() {
        document.body.setAttribute('data-page', 'staff');
    });
</script>
@endpush
