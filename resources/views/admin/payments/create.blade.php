<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuovo Pagamento
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Crea un nuovo pagamento per studente, corso o evento
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
        <li class="text-gray-900 font-medium">Nuovo Pagamento</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Payment Form Card -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Dettagli Pagamento</h3>
                        <p class="text-sm text-gray-600 mt-1">Compila tutti i campi richiesti per creare il pagamento</p>
                    </div>

                    <form action="{{ route('admin.payments.store') }}" method="POST" class="p-6 space-y-6">
                        @csrf

                        <!-- Payment Type -->
                        <div>
                            <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo Pagamento <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_type" id="payment_type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                <option value="">Seleziona tipo pagamento</option>
                                <option value="course_enrollment" {{ $preselected['payment_type'] === 'course_enrollment' ? 'selected' : '' }}>Iscrizione Corso</option>
                                <option value="event_registration" {{ $preselected['payment_type'] === 'event_registration' ? 'selected' : '' }}>Registrazione Evento</option>
                                <option value="private_lesson" {{ $preselected['payment_type'] === 'private_lesson' ? 'selected' : '' }}>Lezione Privata</option>
                                <option value="equipment" {{ $preselected['payment_type'] === 'equipment' ? 'selected' : '' }}>Attrezzatura</option>
                                <option value="other" {{ $preselected['payment_type'] === 'other' ? 'selected' : '' }}>Altro</option>
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
                                    <option value="{{ $student->id }}" {{ $preselected['user_id'] == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Course Selection (conditional) -->
                            <div id="course_section" class="course-related">
                                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Corso
                                </label>
                                <select name="course_id" id="course_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">Seleziona corso (opzionale)</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ $preselected['course_id'] == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }} - €{{ number_format($course->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Event Selection (conditional) -->
                            <div id="event_section" class="event-related">
                                <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Evento
                                </label>
                                <select name="event_id" id="event_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">Seleziona evento (opzionale)</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ $preselected['event_id'] == $event->id ? 'selected' : '' }}>
                                            {{ $event->title }} - €{{ number_format($event->price, 2) }}
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
                                       value="{{ $preselected['amount'] ?? '' }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('amount')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Scadenza
                                </label>
                                <input type="date" name="due_date" id="due_date"
                                       value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('due_date')
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
                                    <option value="pending" selected>In Attesa</option>
                                    <option value="completed">Completato</option>
                                    <option value="cancelled">Annullato</option>
                                </select>
                                @error('status')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrizione
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                      placeholder="Descrizione aggiuntiva del pagamento (opzionale)">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method (if status is completed) -->
                        <div id="payment_method_section" style="display: none;">
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                Metodo Pagamento
                            </label>
                            <select name="payment_method" id="payment_method"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                <option value="">Seleziona metodo</option>
                                <option value="cash">Contanti</option>
                                <option value="bank_transfer">Bonifico Bancario</option>
                                <option value="card">Carta di Credito/Debito</option>
                                <option value="paypal">PayPal</option>
                                <option value="other">Altro</option>
                            </select>
                            @error('payment_method')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Crea Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentTypeSelect = document.getElementById('payment_type');
            const statusSelect = document.getElementById('status');
            const courseSection = document.getElementById('course_section');
            const eventSection = document.getElementById('event_section');
            const paymentMethodSection = document.getElementById('payment_method_section');
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

            // Toggle payment method section based on status
            function togglePaymentMethod() {
                if (statusSelect.value === 'completed') {
                    paymentMethodSection.style.display = 'block';
                } else {
                    paymentMethodSection.style.display = 'none';
                }
            }

            // Auto-fill amount based on course/event selection
            function updateAmount() {
                // Clear amount first
                amountInput.value = '';

                // Check which section is visible and update accordingly
                const paymentType = paymentTypeSelect.value;

                if (paymentType === 'course_enrollment') {
                    // Only check course selection for course enrollments
                    const courseOption = courseSelect.options[courseSelect.selectedIndex];
                    if (courseOption && courseOption.value) {
                        const coursePrice = courseOption.text.match(/€([\d,]+\.?\d*)/);
                        if (coursePrice) {
                            amountInput.value = coursePrice[1].replace(',', '');
                        }
                    }
                } else if (paymentType === 'event_registration') {
                    // Only check event selection for event registrations
                    const eventOption = eventSelect.options[eventSelect.selectedIndex];
                    if (eventOption && eventOption.value) {
                        const eventPrice = eventOption.text.match(/€([\d,]+\.?\d*)/);
                        if (eventPrice) {
                            amountInput.value = eventPrice[1].replace(',', '');
                        }
                    }
                } else {
                    // For other types, check both but prioritize based on which has a selection
                    const courseOption = courseSelect.options[courseSelect.selectedIndex];
                    const eventOption = eventSelect.options[eventSelect.selectedIndex];

                    if (courseOption && courseOption.value) {
                        const coursePrice = courseOption.text.match(/€([\d,]+\.?\d*)/);
                        if (coursePrice) {
                            amountInput.value = coursePrice[1].replace(',', '');
                        }
                    } else if (eventOption && eventOption.value) {
                        const eventPrice = eventOption.text.match(/€([\d,]+\.?\d*)/);
                        if (eventPrice) {
                            amountInput.value = eventPrice[1].replace(',', '');
                        }
                    }
                }
            }

            // Event listeners
            paymentTypeSelect.addEventListener('change', function() {
                toggleSections();
                updateAmount(); // Update amount when payment type changes
            });
            statusSelect.addEventListener('change', togglePaymentMethod);
            courseSelect.addEventListener('change', updateAmount);
            eventSelect.addEventListener('change', updateAmount);

            // Initialize on page load
            toggleSections();
            togglePaymentMethod();
        });
    </script>
</x-app-layout>