<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Pagamento #{{ $payment->id }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Modifica i dettagli del pagamento per {{ $payment->user->name }}
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
        <li class="flex items-center">
            <a href="{{ route('admin.payments.index') }}" class="text-gray-500 hover:text-gray-700">Pagamenti</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Modifica Pagamento</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Payment Form Card -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Dettagli Pagamento</h3>
                        <p class="text-sm text-gray-600 mt-1">Modifica i campi necessari per aggiornare il pagamento</p>
                    </div>

                    <form action="{{ route('admin.payments.update', $payment) }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Payment Type -->
                        <div>
                            <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo Pagamento <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_type" id="payment_type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                <option value="">Seleziona tipo pagamento</option>
                                @foreach(\App\Models\Payment::getAvailableTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ old('payment_type', $payment->payment_type) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_type')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Student Selection -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Studente <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                <option value="">Seleziona studente</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('user_id', $payment->user_id) == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Course Selection -->
                            <div id="course_section">
                                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Corso
                                </label>
                                <select name="course_id" id="course_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">Seleziona corso (opzionale)</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $payment->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }} - €{{ number_format($course->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Event Selection -->
                            <div id="event_section">
                                <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Evento
                                </label>
                                <select name="event_id" id="event_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">Seleziona evento (opzionale)</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ old('event_id', $payment->event_id) == $event->id ? 'selected' : '' }}>
                                            {{ $event->name }} - €{{ number_format($event->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('event_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Importo (€) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="amount" id="amount" step="0.01" min="0" required
                                       value="{{ old('amount', $payment->amount) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('amount')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data Pagamento
                                </label>
                                <input type="date" name="payment_date" id="payment_date"
                                       value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('payment_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Scadenza
                                </label>
                                <input type="date" name="due_date" id="due_date"
                                       value="{{ old('due_date', $payment->due_date?->format('Y-m-d')) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('due_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Payment Method -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                    Metodo Pagamento <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method" id="payment_method" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">Seleziona metodo</option>
                                    @foreach(\App\Models\Payment::getAvailablePaymentMethods() as $key => $label)
                                        <option value="{{ $key }}" {{ old('payment_method', $payment->payment_method) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stato <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    @foreach(\App\Models\Payment::getAvailableStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $payment->status) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Transaction ID -->
                            <div>
                                <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    ID Transazione
                                </label>
                                <input type="text" name="transaction_id" id="transaction_id"
                                       value="{{ old('transaction_id', $payment->transaction_id) }}"
                                       placeholder="Es. TXN_123456789"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('transaction_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reference Number -->
                            <div>
                                <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Numero Riferimento
                                </label>
                                <input type="text" name="reference_number" id="reference_number"
                                       value="{{ old('reference_number', $payment->reference_number) }}"
                                       placeholder="Es. numero bonifico, numero transazione..."
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('reference_number')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Tax Amount -->
                            <div>
                                <label for="tax_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Importo Tasse (€)
                                </label>
                                <input type="number" name="tax_amount" id="tax_amount" step="0.01" min="0"
                                       value="{{ old('tax_amount', $payment->tax_amount) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('tax_amount')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Discount Amount -->
                            <div>
                                <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sconto (€)
                                </label>
                                <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                                       value="{{ old('discount_amount', $payment->discount_amount) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('discount_amount')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Gateway Fee -->
                            <div>
                                <label for="payment_gateway_fee" class="block text-sm font-medium text-gray-700 mb-2">
                                    Commissioni Gateway (€)
                                </label>
                                <input type="number" name="payment_gateway_fee" id="payment_gateway_fee" step="0.01" min="0"
                                       value="{{ old('payment_gateway_fee', $payment->payment_gateway_fee) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('payment_gateway_fee')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Note
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                      placeholder="Note aggiuntive del pagamento (opzionale)">{{ old('notes', $payment->notes) }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($payment->status === 'refunded' && $payment->refund_reason)
                        <!-- Refund Reason (Read-only) -->
                        <div>
                            <label for="refund_reason_display" class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo Rimborso
                            </label>
                            <textarea id="refund_reason_display" rows="2" readonly
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                                      >{{ $payment->refund_reason }}</textarea>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.payments.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Annulla
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Aggiorna Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script nonce="@cspNonce">
        document.addEventListener('DOMContentLoaded', function() {
            const paymentTypeSelect = document.getElementById('payment_type');
            const courseSection = document.getElementById('course_section');
            const eventSection = document.getElementById('event_section');
            const courseSelect = document.getElementById('course_id');
            const eventSelect = document.getElementById('event_id');
            const amountInput = document.getElementById('amount');

            // Toggle sections based on payment type
            function toggleSections() {
                const type = paymentTypeSelect.value;

                if (type === 'course_enrollment') {
                    courseSection.style.display = 'block';
                    eventSection.style.display = 'none';
                } else if (type === 'event_registration') {
                    courseSection.style.display = 'none';
                    eventSection.style.display = 'block';
                } else {
                    courseSection.style.display = 'block';
                    eventSection.style.display = 'block';
                }
            }

            // Auto-fill amount based on course/event selection
            function updateAmount() {
                // Don't auto-update amount in edit mode unless user explicitly changes selection
                const paymentType = paymentTypeSelect.value;

                if (paymentType === 'course_enrollment') {
                    const courseOption = courseSelect.options[courseSelect.selectedIndex];
                    if (courseOption && courseOption.value) {
                        const coursePrice = courseOption.text.match(/€([\d,]+\.?\d*)/);
                        if (coursePrice && !amountInput.dataset.userModified) {
                            amountInput.value = coursePrice[1].replace(',', '');
                        }
                    }
                } else if (paymentType === 'event_registration') {
                    const eventOption = eventSelect.options[eventSelect.selectedIndex];
                    if (eventOption && eventOption.value) {
                        const eventPrice = eventOption.text.match(/€([\d,]+\.?\d*)/);
                        if (eventPrice && !amountInput.dataset.userModified) {
                            amountInput.value = eventPrice[1].replace(',', '');
                        }
                    }
                }
            }

            // Track user modifications to amount
            amountInput.addEventListener('input', function() {
                amountInput.dataset.userModified = 'true';
            });

            // Event listeners
            paymentTypeSelect.addEventListener('change', function() {
                toggleSections();
                updateAmount();
            });
            courseSelect.addEventListener('change', updateAmount);
            eventSelect.addEventListener('change', updateAmount);

            // Initialize on page load
            toggleSections();
        });
    </script>
</x-app-layout>