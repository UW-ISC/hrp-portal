<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Rest;

/**
 * Request sanitization helpers for the wpDataTables REST controllers.
 *
 * Mirrors the ivyforms `Common\Sanitizer` role: controllers read raw values
 * from `WP_REST_Request` and pass them through these helpers before handing
 * them to a service.
 *
 * @package WPDataTables\Common\Rest
 */
class RestSanitizer
{
    /**
     * Coerce a route/query value to a non-negative integer id.
     *
     * @param mixed $value
     * @return int
     */
    public static function id($value)
    {
        return absint($value);
    }

    /**
     * Sanitize a scalar text value.
     *
     * @param mixed $value
     * @return string
     */
    public static function text($value)
    {
        return sanitize_text_field((string)$value);
    }

    /**
     * Coerce a request value to a strict boolean (accepts "1"/"true"/"yes").
     *
     * @param mixed $value
     * @return bool
     */
    public static function boolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
    }
}
