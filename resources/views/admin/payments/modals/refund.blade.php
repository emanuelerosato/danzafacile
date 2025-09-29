<!-- Refund Modal - Tailwind Version -->
<div x-show="showRefundModal"
     style="display: none"
     id="refundModal"
     role="dialog"
     aria-labelledby="refund-modal-title"
     aria-describedby="refund-modal-description"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeRefundModal()"></div>

    <!-- Modal dialog -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white/90 backdrop-blur-lg border border-white/20 shadow-2xl transition-all"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200/80">
                <div class="flex items-center justify-between">
                    <h3 id="refund-modal-title" class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Elabora Rimborso
                    </h3>
                    <button @click="closeRefundModal()" type="button"
                            aria-label="Chiudi modal"
                            class="rounded-lg p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100/50 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Dual-Layer Validation Component -->
            <x-form-validation :rules="[
                'refund_reason' => 'required|string|min:10|max:500'
            ]" />

            <!-- Modal Body -->
            <form id="refundForm">
                <div class="px-6 py-4 space-y-4">
                    <!-- Reason Textarea -->
                    <div>
                        <label for="refund_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo del Rimborso <span class="text-red-500">*</span>
                        </label>
                        <x-secure-input
                            type="textarea"
                            name="refund_reason"
                            id="refund_reason"
                            placeholder="Inserisci il motivo del rimborso (min. 10 caratteri)..."
                            :required="true"
                            :max-length="500"
                            class="h-24 resize-none focus:ring-amber-500 focus:border-amber-500" />
                        <p id="refund-modal-description" class="text-xs text-gray-500 mt-1">Specifica il motivo per cui viene elaborato questo rimborso.</p>
                    </div>

                    <!-- Warning Alert -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-amber-800 mb-1">Attenzione</h4>
                                <p class="text-sm text-amber-700">Questa azione segnerà il pagamento come rimborsato. Assicurati di aver già elaborato il rimborso effettivo attraverso il metodo di pagamento originale.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200/80 flex justify-end space-x-3">
                    <button @click="closeRefundModal()" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        Annulla
                    </button>
                    <x-loading-button
                        type="submit"
                        variant="warning"
                        icon="refund"
                        loading-text="Elaborando...">
                        Elabora Rimborso
                    </x-loading-button>
                </div>
            </form>
        </div>
    </div>
</div>

