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




<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gestione Staff</h1>
                    <p class="text-sm text-gray-600 mt-1">Gestisci il personale della scuola e le assegnazioni ai corsi</p>
                </div>
                <a href="{{ route('admin.staff.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Nuovo Staff
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600">Staff Totale</div>
                </div>
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
                    <div class="text-sm text-gray-600">Attivi</div>
                </div>
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-bold text-purple-600">{{ $stats['instructors'] }}</div>
                    <div class="text-sm text-gray-600">Istruttori</div>
                </div>
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-bold text-orange-600">{{ $stats['on_leave'] }}</div>
                    <div class="text-sm text-gray-600">In Congedo</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200 mb-6">
        <div class="px-6 py-4">
            <form method="GET" class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cerca per nome, email o ID dipendente..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tutti i ruoli</option>
                        @foreach(\App\Models\Staff::getAvailableRoles() as $key => $label)
                            <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="department" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tutti i dipartimenti</option>
                        @foreach(\App\Models\Staff::getAvailableDepartments() as $key => $label)
                            <option value="{{ $key }}" {{ request('department') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="employment_type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tutti i tipi</option>
                        @foreach(\App\Models\Staff::getAvailableEmploymentTypes() as $key => $label)
                            <option value="{{ $key }}" {{ request('employment_type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tutti gli status</option>
                        @foreach(\App\Models\Staff::getAvailableStatuses() as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-search"></i> Filtra
                </button>
                @if(request()->hasAny(['search', 'role', 'department', 'employment_type', 'status']))
                    <a href="{{ route('admin.staff.index') }}"
                       class="text-gray-500 hover:text-gray-700 px-3 py-2">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($staff->count() > 0)
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200 mb-6">
            <div class="px-6 py-4">
                <form id="bulkActionForm" method="POST" action="{{ route('admin.staff.bulk-action') }}">
                    @csrf
                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="selectAll" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="selectAll" class="ml-2 text-sm text-gray-700">Seleziona tutti</label>
                        </div>
                        <div class="flex gap-2">
                            <select name="action" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Azioni multiple</option>
                                <option value="activate">Attiva selezionati</option>
                                <option value="deactivate">Disattiva selezionati</option>
                                <option value="delete">Elimina selezionati</option>
                            </select>
                            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                Esegui
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Staff Grid -->
    @if($staff->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @foreach($staff as $member)
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="staff_ids[]" value="{{ $member->id }}" form="bulkActionForm"
                                       class="staff-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
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

        <!-- Pagination -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200 px-6 py-4">
            {{ $staff->withQueryString()->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200 py-12">
            <div class="text-center">
                <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Nessun staff trovato</h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->hasAny(['search', 'role', 'department', 'employment_type', 'status']))
                        Modifica i filtri per trovare altri membri dello staff o aggiungi un nuovo staff member.
                    @else
                        Inizia aggiungendo il primo membro dello staff della tua scuola.
                    @endif
                </p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('admin.staff.create') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                        <i class="fas fa-plus"></i> Aggiungi Primo Staff
                    </a>
                    @if(request()->hasAny(['search', 'role', 'department', 'employment_type', 'status']))
                        <a href="{{ route('admin.staff.index') }}"
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times"></i> Reset Filtri
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const staffCheckboxes = document.querySelectorAll('.staff-checkbox');

    selectAllCheckbox?.addEventListener('change', function() {
        staffCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update select all when individual checkboxes change
    staffCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(staffCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(staffCheckboxes).some(cb => cb.checked);

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        });
    });

    // Bulk action form submission
    const bulkActionForm = document.getElementById('bulkActionForm');
    bulkActionForm?.addEventListener('submit', function(e) {
        const selectedCheckboxes = document.querySelectorAll('.staff-checkbox:checked');
        const actionSelect = document.querySelector('select[name="action"]');

        if (selectedCheckboxes.length === 0) {
            e.preventDefault();
            alert('Seleziona almeno un staff member');
            return;
        }

        if (!actionSelect.value) {
            e.preventDefault();
            alert('Seleziona un\'azione da eseguire');
            return;
        }

        if (actionSelect.value === 'delete') {
            if (!confirm(`Sei sicuro di voler eliminare ${selectedCheckboxes.length} staff members?`)) {
                e.preventDefault();
                return;
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
</x-app-layout>
