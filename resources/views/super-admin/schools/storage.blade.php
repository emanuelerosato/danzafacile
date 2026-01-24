<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Storage Scuole
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Overview e gestione quote storage per tutte le scuole
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('super-admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Storage Scuole</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Tabella Scuole --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Scuole ({{ $schools->total() }})</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scuola</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizzo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scadenza</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($schools as $school)
                                    @php
                                        $info = $school->storage_info;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $school->name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $school->id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $info['used_formatted'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($info['unlimited'])
                                                <span class="text-sm font-medium text-purple-600">Illimitato</span>
                                            @else
                                                <span class="text-sm text-gray-900">{{ $info['quota_gb'] }} GB</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(!$info['unlimited'])
                                                <div class="w-32">
                                                    <div class="flex items-center justify-between text-xs mb-1">
                                                        <span>{{ $info['usage_percent'] }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="h-2 rounded-full
                                                            @if($info['is_full']) bg-red-600
                                                            @elseif($info['is_warning']) bg-yellow-500
                                                            @else bg-green-500
                                                            @endif"
                                                             style="width: {{ min($info['usage_percent'], 100) }}%">
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($info['unlimited'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    Illimitato
                                                </span>
                                            @elseif($info['is_full'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Pieno
                                                </span>
                                            @elseif($info['is_warning'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Attenzione
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    OK
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($info['expires_at'])
                                                {{ $info['expires_at']->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                onclick="openManageModal({{ $school->id }}, '{{ $school->name }}', {{ $info['quota_gb'] }}, {{ $info['used_gb'] }}, {{ $info['unlimited'] ? 'true' : 'false' }}, '{{ $info['expires_at'] ? $info['expires_at']->format('Y-m-d') : '' }}')"
                                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700">
                                                Gestisci
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            Nessuna scuola trovata
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($schools->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $schools->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Modal Gestione Storage --}}
    <div id="manageStorageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" id="modalTitle">Gestisci Storage - Scuola</h3>

                {{-- Info Corrente --}}
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Quota Attuale:</span>
                            <span class="font-semibold text-gray-900 ml-2" id="currentQuota">-</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Utilizzo:</span>
                            <span class="font-semibold text-gray-900 ml-2" id="currentUsage">-</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Status:</span>
                            <span class="font-semibold text-gray-900 ml-2" id="currentStatus">-</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Scadenza:</span>
                            <span class="font-semibold text-gray-900 ml-2" id="currentExpiry">-</span>
                        </div>
                    </div>
                </div>

                {{-- Form Gestione --}}
                <form id="manageForm" method="POST">
                    @csrf

                    <div class="space-y-4">
                        {{-- Opzione 1: Aggiungi GB --}}
                        <div class="border rounded-lg p-4">
                            <label class="flex items-center">
                                <input type="radio" name="action" value="add_quota" class="mr-3" checked onchange="toggleFormFields()">
                                <span class="font-medium text-gray-900">Aggiungi GB aggiuntivi</span>
                            </label>

                            <div id="addQuotaFields" class="mt-3 ml-6 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">GB da aggiungere</label>
                                    <input type="number" name="additional_gb" min="1" max="1000" value="10"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Durata</label>
                                    <select name="duration" onchange="toggleCustomDate(this)"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                                        <option value="permanent">Permanente</option>
                                        <option value="1_year">1 anno</option>
                                        <option value="6_months">6 mesi</option>
                                        <option value="3_months">3 mesi</option>
                                        <option value="custom">Data personalizzata</option>
                                    </select>
                                </div>

                                <div id="customDateField" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data scadenza</label>
                                    <input type="date" name="custom_expiry_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                                </div>
                            </div>
                        </div>

                        {{-- Opzione 2: Storage Illimitato --}}
                        <div class="border rounded-lg p-4">
                            <label class="flex items-center">
                                <input type="radio" name="action" value="set_unlimited" class="mr-3" onchange="toggleFormFields()">
                                <span class="font-medium text-gray-900">Abilita Storage Illimitato</span>
                            </label>
                        </div>

                        {{-- Opzione 3: Reset a Base --}}
                        <div class="border rounded-lg p-4">
                            <label class="flex items-center">
                                <input type="radio" name="action" value="reset_to_base" class="mr-3" onchange="toggleFormFields()">
                                <span class="font-medium text-gray-900">Reset a Quota Base (5GB)</span>
                            </label>
                        </div>

                        {{-- Note Admin --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Note (opzionale)</label>
                            <textarea name="admin_note" rows="2" maxlength="500"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500"
                                      placeholder="Es: Upgrade per cliente VIP, Test periodo trial, etc."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeManageModal()"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Annulla
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-lg hover:from-rose-600 hover:to-purple-700">
                            Salva Modifiche
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script nonce="@cspNonce">
        function openManageModal(schoolId, schoolName, quotaGb, usedGb, unlimited, expiryDate) {
            document.getElementById('modalTitle').textContent = 'Gestisci Storage - ' + schoolName;
            document.getElementById('currentQuota').textContent = unlimited ? 'Illimitato' : quotaGb + ' GB';
            document.getElementById('currentUsage').textContent = usedGb + ' GB (' + Math.round((usedGb / quotaGb) * 100) + '%)';
            document.getElementById('currentStatus').textContent = unlimited ? 'Unlimited' : 'Normale';
            document.getElementById('currentExpiry').textContent = expiryDate || '-';

            document.getElementById('manageForm').action = '/super-admin/schools/' + schoolId + '/storage/update';
            document.getElementById('manageStorageModal').classList.remove('hidden');
        }

        function closeManageModal() {
            document.getElementById('manageStorageModal').classList.add('hidden');
        }

        function toggleFormFields() {
            const addQuotaChecked = document.querySelector('input[name="action"][value="add_quota"]').checked;
            document.getElementById('addQuotaFields').style.display = addQuotaChecked ? 'block' : 'none';
        }

        function toggleCustomDate(select) {
            const customField = document.getElementById('customDateField');
            customField.style.display = select.value === 'custom' ? 'block' : 'none';
        }

        // Close modal on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeManageModal();
        });
    </script>
</x-app-layout>
