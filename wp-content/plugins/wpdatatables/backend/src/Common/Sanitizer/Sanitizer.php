<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Sanitizer;

use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Centralised request/value sanitisation helpers.
 *
 * The SQL identifier sanitiser and the request guards the controllers/services
 * lean on.
 *
 * @package WPDataTables\Common\Sanitizer
 */
class Sanitizer
{
    /**
     * Cast a request value to a positive integer id.
     *
     * @param mixed $value
     * @return int
     * @throws InvalidArgumentException
     */
    public static function sanitizeId($value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            throw new InvalidArgumentException('invalid_id');
        }

        return (int) $id;
    }

    /**
     * Sanitise a plain text field.
     *
     * @param mixed $value
     * @return string
     */
    public static function sanitizeText($value): string
    {
        return sanitize_text_field((string) $value);
    }

    /**
     * Sanitise a SQL identifier (table/column name) to a safe character set.
     *
     * Mirrors the defensive identifier handling already used in the legacy
     * frontend SQL path; keeps only word characters so identifiers can be
     * interpolated into queries without injection risk.
     *
     * @param string $identifier
     * @return string
     */
    public static function sanitizeSqlIdentifier(string $identifier): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);
    }

    /**
     * Verify a WordPress REST/admin-ajax nonce or throw.
     *
     * @param string|null $nonce
     * @param string      $action
     * @throws ForbiddenException
     */
    public static function verifyNonce($nonce, string $action = 'wp_rest'): void
    {
        if (empty($nonce) || !wp_verify_nonce($nonce, $action)) {
            throw new ForbiddenException('invalid_nonce');
        }
    }
}
