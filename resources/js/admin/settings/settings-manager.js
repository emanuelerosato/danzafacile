/**
 * settings-manager.js
 *
 * Alpine.js component per gestire la pagina impostazioni admin
 * Gestisce: toggle PayPal settings, alert dismissal, loading states
 *
 * @version 1.0.0
 */

// Alpine.js data function per gestire la pagina settings
window.settingsManager = function() {
    return {
        // State
        showSuccessAlert: true,
        isSubmitting: false,
        paypalEnabled: false,

        // Inizializzazione
        init() {
            console.log('[SettingsManager] Component initialized');

            // Leggi stato iniziale checkbox PayPal
            const paypalCheckbox = document.getElementById('paypal_enabled');
            if (paypalCheckbox) {
                this.paypalEnabled = paypalCheckbox.checked;
                console.log('[SettingsManager] PayPal enabled:', this.paypalEnabled);
            }
        },

        // Dismiss success alert
        dismissAlert() {
            this.showSuccessAlert = false;
        },

        // Handle form submission
        handleSubmit() {
            this.isSubmitting = true;
            console.log('[SettingsManager] Form submitting...');
            // Il form verrà inviato normalmente, questo serve solo per il loading state
        },

        // Computed: mostra settings PayPal
        get showPayPalSettings() {
            return this.paypalEnabled;
        }
    };
};

console.log('[SettingsManager] Script loaded');