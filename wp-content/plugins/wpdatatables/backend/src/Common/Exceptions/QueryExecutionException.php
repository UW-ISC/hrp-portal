<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Exceptions;

use Exception;

/**
 * Class QueryExecutionException — maps to HTTP 500 (database error).
 *
 * @package WPDataTables\Common\Exceptions
 */
class QueryExecutionException extends WDTException
{
    public function __construct($message = 'query_execution_error', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
