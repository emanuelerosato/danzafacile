@props([
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'class' => '',
    'sanitize' => true,
    'allowHtml' => false,
    'maxLength' => null
])

@php
// Server-side sanitization of value
$sanitizedValue = $value;
if ($sanitize && $value) {
    // Basic XSS prevention
    $sanitizedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    // Remove potentially dangerous attributes
    $sanitizedValue = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/', '', $sanitizedValue);

    // Remove script tags
    $sanitizedValue = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $sanitizedValue);

    // If HTML is not allowed, strip all tags
    if (!$allowHtml) {
        $sanitizedValue = strip_tags($sanitizedValue);
    }
}

// Build input attributes
$inputAttributes = [
    'type' => $type,
    'name' => $name,
    'id' => $name,
    'value' => $sanitizedValue,
    'placeholder' => $placeholder,
    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ' . $class
];

if ($required) {
    $inputAttributes['required'] = '';
}

if ($maxLength) {
    $inputAttributes['maxlength'] = $maxLength;
}

// Add data attributes for client-side sanitization
$inputAttributes['data-sanitize'] = $sanitize ? 'true' : 'false';
$inputAttributes['data-allow-html'] = $allowHtml ? 'true' : 'false';
@endphp

@if($type === 'textarea')
    <textarea @foreach($inputAttributes as $attr => $val)
        @if($attr !== 'type' && $attr !== 'value')
            {{ $attr }}="{{ $val }}"
        @endif
    @endforeach
    x-data="secureInput()"
    x-init="init()"
    @input="sanitizeInput($event)"
    @paste="handlePaste($event)">{{ $sanitizedValue }}</textarea>
@else
    <input @foreach($inputAttributes as $attr => $val)
        {{ $attr }}="{{ $val }}"
    @endforeach
    x-data="secureInput()"
    x-init="init()"
    @input="sanitizeInput($event)"
    @paste="handlePaste($event)">
@endif

<script>
function secureInput() {
    return {
        init() {
            // Initialize input security
            this.setupSecurity();
        },

        setupSecurity() {
            const input = this.$el;

            // Prevent common XSS attack vectors
            input.addEventListener('keydown', (e) => {
                // Prevent dangerous key combinations
                if (e.ctrlKey && (e.key === 'v' || e.key === 'V')) {
                    // Let paste event handler deal with this
                    return;
                }
            });

            // Add input length counter if maxlength is set
            const maxLength = input.getAttribute('maxlength');
            if (maxLength) {
                this.addLengthCounter(input, parseInt(maxLength));
            }
        },

        sanitizeInput(event) {
            const input = event.target;
            const shouldSanitize = input.dataset.sanitize === 'true';
            const allowHtml = input.dataset.allowHtml === 'true';

            if (!shouldSanitize) return;

            let value = input.value;
            let cursorPosition = input.selectionStart;

            // Client-side sanitization
            const sanitized = this.sanitizeText(value, allowHtml);

            if (sanitized !== value) {
                input.value = sanitized;

                // Restore cursor position (adjust for removed characters)
                const lengthDiff = value.length - sanitized.length;
                input.setSelectionRange(
                    Math.max(0, cursorPosition - lengthDiff),
                    Math.max(0, cursorPosition - lengthDiff)
                );

                // Show warning toast
                if (window.Toast) {
                    Toast.warning('Contenuto potenzialmente pericoloso rimosso per la sicurezza', 3000);
                }
            }
        },

        handlePaste(event) {
            const input = event.target;
            const shouldSanitize = input.dataset.sanitize === 'true';
            const allowHtml = input.dataset.allowHtml === 'true';

            if (!shouldSanitize) return;

            // Get pasted data
            const paste = (event.clipboardData || window.clipboardData).getData('text');

            // Sanitize pasted content
            const sanitized = this.sanitizeText(paste, allowHtml);

            if (sanitized !== paste) {
                event.preventDefault();

                // Insert sanitized content at cursor position
                const start = input.selectionStart;
                const end = input.selectionEnd;
                const before = input.value.substring(0, start);
                const after = input.value.substring(end);

                input.value = before + sanitized + after;
                input.setSelectionRange(start + sanitized.length, start + sanitized.length);

                // Trigger input event
                input.dispatchEvent(new Event('input', { bubbles: true }));

                // Show warning toast
                if (window.Toast) {
                    Toast.warning('Contenuto incollato è stato sanitizzato per la sicurezza', 3000);
                }
            }
        },

        sanitizeText(text, allowHtml = false) {
            if (!text) return text;

            // Remove null bytes
            text = text.replace(/\0/g, '');

            // Remove script tags and their content
            text = text.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');

            // Remove javascript: URLs
            text = text.replace(/javascript:/gi, '');

            // Remove on* event handlers
            text = text.replace(/\bon\w+\s*=\s*["'][^"']*["']/gi, '');

            // Remove dangerous HTML elements
            const dangerousTags = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'meta', 'link', 'style'];
            dangerousTags.forEach(tag => {
                const regex = new RegExp(`<\\/?${tag}\\b[^>]*>`, 'gi');
                text = text.replace(regex, '');
            });

            // If HTML is not allowed, encode HTML entities
            if (!allowHtml) {
                text = text.replace(/[<>&"']/g, function(match) {
                    const entities = {
                        '<': '&lt;',
                        '>': '&gt;',
                        '&': '&amp;',
                        '"': '&quot;',
                        "'": '&#39;'
                    };
                    return entities[match];
                });
            }

            // Remove excessive whitespace
            text = text.replace(/\s+/g, ' ').trim();

            return text;
        },

        addLengthCounter(input, maxLength) {
            // Create counter element
            const counter = document.createElement('div');
            counter.className = 'text-xs text-gray-500 mt-1 text-right';
            counter.innerHTML = `<span class="char-count">0</span>/${maxLength}`;

            // Insert after input
            input.parentNode.insertBefore(counter, input.nextSibling);

            // Update counter on input
            const updateCounter = () => {
                const currentLength = input.value.length;
                const counterSpan = counter.querySelector('.char-count');
                counterSpan.textContent = currentLength;

                // Change color based on length
                if (currentLength > maxLength * 0.9) {
                    counter.className = 'text-xs text-red-500 mt-1 text-right';
                } else if (currentLength > maxLength * 0.7) {
                    counter.className = 'text-xs text-yellow-600 mt-1 text-right';
                } else {
                    counter.className = 'text-xs text-gray-500 mt-1 text-right';
                }
            };

            input.addEventListener('input', updateCounter);
            updateCounter(); // Initial update
        }
    }
}
</script>