<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Query Helper per prevenire SQL Injection
 *
 * Fornisce metodi sicuri per:
 * - Validazione campi di ordinamento
 * - Sanitizzazione input LIKE
 * - Applicazione sort sicuri
 */
class QueryHelper
{
    /**
     * Valida il campo di ordinamento contro una whitelist
     *
     * @param string $field Campo richiesto dall'utente
     * @param array $allowedFields Whitelist di campi permessi
     * @param string $default Campo di default se quello richiesto non è valido
     * @return string Campo validato
     */
    public static function validateSortField(string $field, array $allowedFields, string $default = 'created_at'): string
    {
        // Rimuovi spazi e normalizza
        $field = trim($field);

        // Controlla se il campo è nella whitelist
        if (in_array($field, $allowedFields, true)) {
            return $field;
        }

        // Log tentativo di ordinamento non valido
        \Log::warning('Invalid sort field attempted', [
            'requested_field' => $field,
            'allowed_fields' => $allowedFields,
            'using_default' => $default
        ]);

        return $default;
    }

    /**
     * Valida la direzione di ordinamento
     *
     * @param string $direction Direzione richiesta (asc/desc)
     * @param string $default Direzione di default
     * @return string Direzione validata (asc o desc)
     */
    public static function validateSortDirection(string $direction, string $default = 'desc'): string
    {
        $direction = strtolower(trim($direction));

        if (in_array($direction, ['asc', 'desc'], true)) {
            return $direction;
        }

        return $default;
    }

    /**
     * Applica ordinamento sicuro alla query
     *
     * @param Builder|Relation $query Query builder or relation
     * @param string $sortField Campo di ordinamento richiesto
     * @param string $sortDirection Direzione di ordinamento richiesta
     * @param array $allowedFields Whitelist di campi permessi
     * @param string $defaultField Campo di default
     * @param string $defaultDirection Direzione di default
     * @return Builder|Relation Query con ordinamento applicato
     */
    public static function applySafeSort(
        Builder|Relation $query,
        ?string $sortField,
        ?string $sortDirection,
        array $allowedFields,
        string $defaultField = 'created_at',
        string $defaultDirection = 'desc'
    ): Builder|Relation {
        // Valida campo e direzione
        $validField = self::validateSortField(
            $sortField ?? $defaultField,
            $allowedFields,
            $defaultField
        );

        $validDirection = self::validateSortDirection(
            $sortDirection ?? $defaultDirection,
            $defaultDirection
        );

        // Applica ordinamento sicuro
        return $query->orderBy($validField, $validDirection);
    }

    /**
     * Sanitizza input per query LIKE prevenendo wildcard injection
     *
     * @param string $input Input utente
     * @param int $maxLength Lunghezza massima (default 100)
     * @return string Input sanitizzato
     */
    public static function sanitizeLikeInput(string $input, int $maxLength = 100): string
    {
        // Rimuovi spazi all'inizio e alla fine
        $input = trim($input);

        // Limita la lunghezza per prevenire DoS
        $input = substr($input, 0, $maxLength);

        // Escape caratteri speciali LIKE: % _ \
        $input = addcslashes($input, '%_\\');

        return $input;
    }

    /**
     * Applica ricerca LIKE sicura
     *
     * @param Builder|Relation $query Query builder or relation
     * @param string $column Colonna su cui cercare
     * @param string $searchTerm Termine di ricerca
     * @param string $position Posizione wildcard: 'both', 'start', 'end'
     * @return Builder|Relation Query con LIKE applicato
     */
    public static function applySafeLike(
        Builder|Relation $query,
        string $column,
        string $searchTerm,
        string $position = 'both'
    ): Builder|Relation {
        // Sanitizza input
        $sanitized = self::sanitizeLikeInput($searchTerm);

        // Se dopo sanitizzazione è vuoto, non applicare filtro
        if (empty($sanitized)) {
            return $query;
        }

        // Applica wildcards in base alla posizione
        $likePattern = match($position) {
            'start' => $sanitized . '%',
            'end' => '%' . $sanitized,
            'both' => '%' . $sanitized . '%',
            default => '%' . $sanitized . '%'
        };

        return $query->where($column, 'LIKE', $likePattern);
    }

    /**
     * Applica ricerca LIKE sicura su multiple colonne (OR)
     *
     * @param Builder|Relation $query Query builder or relation
     * @param array $columns Array di colonne su cui cercare
     * @param string $searchTerm Termine di ricerca
     * @param string $position Posizione wildcard
     * @return Builder|Relation Query con LIKE applicato
     */
    public static function applySafeLikeMultiple(
        Builder|Relation $query,
        array $columns,
        string $searchTerm,
        string $position = 'both'
    ): Builder|Relation {
        // Sanitizza input
        $sanitized = self::sanitizeLikeInput($searchTerm);

        // Se dopo sanitizzazione è vuoto, non applicare filtro
        if (empty($sanitized)) {
            return $query;
        }

        // Applica wildcards
        $likePattern = match($position) {
            'start' => $sanitized . '%',
            'end' => '%' . $sanitized,
            'both' => '%' . $sanitized . '%',
            default => '%' . $sanitized . '%'
        };

        // Applica OR su tutte le colonne
        return $query->where(function($q) use ($columns, $likePattern) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', $likePattern);
            }
        });
    }

    /**
     * Valida e applica paginazione
     *
     * @param int|null $perPage Numero elementi per pagina richiesto
     * @param int $default Valore di default
     * @param int $max Massimo permesso
     * @return int Numero validato
     */
    public static function validatePerPage(?int $perPage, int $default = 15, int $max = 100): int
    {
        if ($perPage === null || $perPage < 1) {
            return $default;
        }

        if ($perPage > $max) {
            \Log::warning('Per page limit exceeded', [
                'requested' => $perPage,
                'max' => $max,
                'using' => $max
            ]);
            return $max;
        }

        return $perPage;
    }
}
