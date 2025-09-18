@props([
    'rules' => [],
    'messages' => []
])

@php
// Standard validation rules mapping
$validationRules = [
    'required' => 'required',
    'email' => 'email',
    'min' => 'minlength',
    'max' => 'maxlength',
    'integer' => 'number',
    'numeric' => 'number',
    'date' => 'date',
    'url' => 'url'
];

// Standard error messages in Italian
$standardMessages = [
    'required' => 'Questo campo è obbligatorio',
    'email' => 'Inserisci un indirizzo email valido',
    'min' => 'Il valore deve essere almeno {value}',
    'max' => 'Il valore non può superare {value}',
    'minlength' => 'Il testo deve essere di almeno {value} caratteri',
    'maxlength' => 'Il testo non può superare {value} caratteri',
    'number' => 'Inserisci un numero valido',
    'date' => 'Inserisci una data valida',
    'url' => 'Inserisci un URL valido',
    'after' => 'La data deve essere successiva a {value}',
    'before' => 'La data deve essere precedente a {value}'
];

$clientRules = [];
$errorMessages = array_merge($standardMessages, $messages);

// Convert Laravel validation rules to HTML5/JavaScript validation
foreach ($rules as $field => $ruleString) {
    $fieldRules = [];
    $ruleArray = explode('|', $ruleString);

    foreach ($ruleArray as $rule) {
        if (str_contains($rule, ':')) {
            [$ruleName, $ruleValue] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }

        switch ($ruleName) {
            case 'required':
                $fieldRules['required'] = true;
                break;
            case 'email':
                $fieldRules['type'] = 'email';
                break;
            case 'min':
                $fieldRules['min'] = $ruleValue;
                break;
            case 'max':
                $fieldRules['max'] = $ruleValue;
                break;
            case 'integer':
            case 'numeric':
                $fieldRules['type'] = 'number';
                break;
            case 'date':
                $fieldRules['type'] = 'date';
                break;
            case 'url':
                $fieldRules['type'] = 'url';
                break;
        }
    }

    $clientRules[$field] = $fieldRules;
}
@endphp

<script>
// Enhanced Form Validation System
window.FormValidator = {
    rules: @json($clientRules),
    messages: @json($errorMessages),

    // Initialize validation for a form
    init(formSelector) {
        const form = document.querySelector(formSelector);
        if (!form) return;

        // Add novalidate to prevent browser default validation
        form.setAttribute('novalidate', '');

        // Apply validation rules to form fields
        Object.keys(this.rules).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.applyRulesToField(field, this.rules[fieldName]);
                this.addValidationListeners(field);
            }
        });

        // Add form submit validation
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                this.focusFirstError(form);
            }
        });
    },

    // Apply validation rules to a specific field
    applyRulesToField(field, rules) {
        Object.keys(rules).forEach(rule => {
            const value = rules[rule];

            switch (rule) {
                case 'required':
                    if (value) field.setAttribute('required', '');
                    break;
                case 'type':
                    field.setAttribute('type', value);
                    break;
                case 'min':
                    field.setAttribute('min', value);
                    break;
                case 'max':
                    field.setAttribute('max', value);
                    break;
                case 'minlength':
                    field.setAttribute('minlength', value);
                    break;
                case 'maxlength':
                    field.setAttribute('maxlength', value);
                    break;
            }
        });
    },

    // Add real-time validation listeners
    addValidationListeners(field) {
        // Validate on blur (when user leaves field)
        field.addEventListener('blur', () => {
            this.validateField(field);
        });

        // Clear errors on input (when user starts typing)
        field.addEventListener('input', () => {
            this.clearFieldError(field);
        });
    },

    // Validate a single field
    validateField(field) {
        const fieldName = field.getAttribute('name');
        const fieldRules = this.rules[fieldName];
        const value = field.value.trim();

        if (!fieldRules) return true;

        // Check required
        if (fieldRules.required && !value) {
            this.showFieldError(field, this.messages.required);
            return false;
        }

        // Skip other validations if field is empty and not required
        if (!value && !fieldRules.required) {
            this.clearFieldError(field);
            return true;
        }

        // Check email
        if (fieldRules.type === 'email' && value) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(value)) {
                this.showFieldError(field, this.messages.email);
                return false;
            }
        }

        // Check min/max for numbers
        if (field.type === 'number' && value) {
            const numValue = parseFloat(value);
            if (fieldRules.min !== undefined && numValue < fieldRules.min) {
                this.showFieldError(field, this.messages.min.replace('{value}', fieldRules.min));
                return false;
            }
            if (fieldRules.max !== undefined && numValue > fieldRules.max) {
                this.showFieldError(field, this.messages.max.replace('{value}', fieldRules.max));
                return false;
            }
        }

        // Check minlength/maxlength for text
        if (['text', 'textarea', 'email', 'url'].includes(field.type) && value) {
            if (fieldRules.minlength !== undefined && value.length < fieldRules.minlength) {
                this.showFieldError(field, this.messages.minlength.replace('{value}', fieldRules.minlength));
                return false;
            }
            if (fieldRules.maxlength !== undefined && value.length > fieldRules.maxlength) {
                this.showFieldError(field, this.messages.maxlength.replace('{value}', fieldRules.maxlength));
                return false;
            }
        }

        // Check date
        if (field.type === 'date' && value) {
            const dateValue = new Date(value);
            if (isNaN(dateValue.getTime())) {
                this.showFieldError(field, this.messages.date);
                return false;
            }
        }

        // If we get here, validation passed
        this.clearFieldError(field);
        return true;
    },

    // Validate entire form
    validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    },

    // Show validation error for a field
    showFieldError(field, message) {
        this.clearFieldError(field);

        // Add error class to field
        field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.remove('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');

        // Create and show error message
        const errorElement = document.createElement('p');
        errorElement.className = 'mt-1 text-sm text-red-600 validation-error';
        errorElement.textContent = message;

        // Insert error message after the field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    },

    // Clear validation error for a field
    clearFieldError(field) {
        // Remove error classes
        field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.add('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');

        // Remove error message
        const errorElement = field.parentNode.querySelector('.validation-error');
        if (errorElement) {
            errorElement.remove();
        }
    },

    // Focus first field with error
    focusFirstError(form) {
        const firstErrorField = form.querySelector('.border-red-500');
        if (firstErrorField) {
            firstErrorField.focus();
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
};
</script>