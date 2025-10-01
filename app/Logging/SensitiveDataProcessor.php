<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Sensitive Data Processor for Logs
 *
 * SECURITY: Sanitizes sensitive data from log messages to prevent:
 * - Password exposure in logs
 * - API key/token leakage
 * - Credit card number logging
 * - Email addresses in plaintext
 * - Session tokens exposure
 */
class SensitiveDataProcessor implements ProcessorInterface
{
    /**
     * Sensitive field patterns to redact
     *
     * @var array
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'bearer',
        'api_key',
        'secret',
        'client_secret',
        'paypal_client_secret',
        'private_key',
        'card_number',
        'cvv',
        'card_cvv',
        'ssn',
        'social_security',
        'credit_card',
    ];

    /**
     * Regex patterns for sensitive data
     *
     * @var array
     */
    private const SENSITIVE_PATTERNS = [
        // Email addresses (partial masking: e***@example.com)
        'email' => '/\b([a-zA-Z0-9._%+-])[a-zA-Z0-9._%+-]*@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/',

        // Credit card numbers (16 digits)
        'credit_card' => '/\b(\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4})\b/',

        // API tokens (long alphanumeric strings)
        'token' => '/\b([a-zA-Z0-9]{40,})\b/',

        // IP addresses (keep for debugging, but could be masked in production)
        // 'ip' => '/\b(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\b/',
    ];

    /**
     * Replacement text for redacted fields
     *
     * @var string
     */
    private const REDACTED = '[REDACTED]';

    /**
     * Process log record to sanitize sensitive data
     *
     * @param LogRecord $record
     * @return LogRecord
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        // Sanitize context array (common logging pattern)
        if (!empty($record->context)) {
            $record->context = $this->sanitizeArray($record->context);
        }

        // Sanitize extra data
        if (!empty($record->extra)) {
            $record->extra = $this->sanitizeArray($record->extra);
        }

        // Sanitize message string
        $record->message = $this->sanitizeString($record->message);

        return $record;
    }

    /**
     * Sanitize array recursively
     *
     * @param array $data
     * @return array
     */
    private function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            // Check if key matches sensitive field
            if ($this->isSensitiveKey($key)) {
                $data[$key] = self::REDACTED;
                continue;
            }

            // Recursively sanitize nested arrays
            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitizeString($value);
            }
        }

        return $data;
    }

    /**
     * Sanitize string using regex patterns
     *
     * @param string $string
     * @return string
     */
    private function sanitizeString(string $string): string
    {
        // Apply regex patterns
        foreach (self::SENSITIVE_PATTERNS as $type => $pattern) {
            $string = preg_replace_callback($pattern, function ($matches) use ($type) {
                return $this->maskValue($matches[0], $type);
            }, $string);
        }

        return $string;
    }

    /**
     * Check if key is sensitive
     *
     * @param string $key
     * @return bool
     */
    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mask sensitive value based on type
     *
     * @param string $value
     * @param string $type
     * @return string
     */
    private function maskValue(string $value, string $type): string
    {
        return match($type) {
            'email' => $this->maskEmail($value),
            'credit_card' => '****-****-****-' . substr($value, -4),
            'token' => substr($value, 0, 8) . '...' . substr($value, -8),
            default => self::REDACTED,
        };
    }

    /**
     * Mask email address (show first char and domain)
     *
     * @param string $email
     * @return string
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return self::REDACTED;
        }

        $localPart = $parts[0];
        $domain = $parts[1];

        $masked = substr($localPart, 0, 1) . str_repeat('*', min(strlen($localPart) - 1, 5));

        return $masked . '@' . $domain;
    }
}
