<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Exceptions;

use Exception;

/**
 * Class ServiceUnavailableException — maps to HTTP 503.
 *
 * Raised when an external dependency the request relies on is not configured or
 * temporarily unreachable (e.g. no WP AI provider is configured, so the AI
 * endpoints cannot fulfil the request). Distinct from a 500 because the failure
 * is environmental/configurational, not a bug.
 *
 * @package WPDataTables\Common\Exceptions
 */
class ServiceUnavailableException extends WDTException
{
    public function __construct($message = 'service_unavailable', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
