<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

/**
 * Encryption Helper per gestione sicura credentials
 *
 * Fornisce metodi per:
 * - Encrypt/decrypt sensitive data
 * - Check se un valore è già encrypted
 * - Gestione sicura errori decryption
 */
class EncryptionHelper
{
    /**
     * Prefix per identificare valori encrypted
     */
    private const ENCRYPTED_PREFIX = 'enc:';

    /**
     * Encrypt un valore stringa
     *
     * @param string|null $value Valore da encrypt
     * @return string|null Valore encrypted con prefix, null se input è null
     */
    public static function encrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Se già encrypted, return as-is
        if (self::isEncrypted($value)) {
            return $value;
        }

        try {
            $encrypted = Crypt::encryptString($value);
            return self::ENCRYPTED_PREFIX . $encrypted;
        } catch (\Exception $e) {
            Log::error('Encryption failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Decrypt un valore stringa
     *
     * @param string|null $value Valore da decrypt
     * @return string|null Valore decrypted, null se input è null
     */
    public static function decrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Se non ha il prefix, assume sia plaintext (backward compatibility)
        if (!self::isEncrypted($value)) {
            Log::warning('Attempting to decrypt plaintext value - returning as-is');
            return $value;
        }

        try {
            // Rimuovi prefix e decrypt
            $encryptedValue = substr($value, strlen(self::ENCRYPTED_PREFIX));
            return Crypt::decryptString($encryptedValue);
        } catch (DecryptException $e) {
            Log::error('Decryption failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check se un valore è encrypted
     *
     * @param string|null $value
     * @return bool
     */
    public static function isEncrypted(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return str_starts_with($value, self::ENCRYPTED_PREFIX);
    }

    /**
     * Safely decrypt con fallback
     *
     * Se decrypt fallisce, return null invece di exception
     *
     * @param string|null $value
     * @return string|null
     */
    public static function safeDecrypt(?string $value): ?string
    {
        try {
            return self::decrypt($value);
        } catch (\Exception $e) {
            Log::error('Safe decryption failed, returning null', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Encrypt array di valori
     *
     * @param array $data Array associativo con valori da encrypt
     * @param array $keysToEncrypt Keys da encrypt
     * @return array Array con valori encrypted
     */
    public static function encryptArray(array $data, array $keysToEncrypt): array
    {
        $result = $data;

        foreach ($keysToEncrypt as $key) {
            if (isset($data[$key])) {
                $result[$key] = self::encrypt($data[$key]);
            }
        }

        return $result;
    }

    /**
     * Decrypt array di valori
     *
     * @param array $data Array associativo con valori encrypted
     * @param array $keysToDecrypt Keys da decrypt
     * @return array Array con valori decrypted
     */
    public static function decryptArray(array $data, array $keysToDecrypt): array
    {
        $result = $data;

        foreach ($keysToDecrypt as $key) {
            if (isset($data[$key])) {
                $result[$key] = self::safeDecrypt($data[$key]);
            }
        }

        return $result;
    }

    /**
     * Mask un valore per display (mostra solo ultimi 4 caratteri)
     *
     * Utile per mostrare credential senza rivelare tutto
     *
     * @param string|null $value
     * @return string
     */
    public static function mask(?string $value): string
    {
        if ($value === null || $value === '') {
            return '****';
        }

        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($value, -4);
    }

    /**
     * Verifica che encryption/decryption funzioni correttamente
     *
     * @return bool
     */
    public static function testEncryption(): bool
    {
        try {
            $testValue = 'test_encryption_' . time();
            $encrypted = self::encrypt($testValue);
            $decrypted = self::decrypt($encrypted);

            return $decrypted === $testValue;
        } catch (\Exception $e) {
            Log::error('Encryption test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
