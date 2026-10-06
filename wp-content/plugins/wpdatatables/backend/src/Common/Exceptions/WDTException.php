<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Exceptions;

use Exception;

/**
 * Base wpDataTables exception.
 *
 * The legacy global `WDTException` maps onto this type and the five typed
 * exceptions below (Forbidden / NotFound / InvalidArgument / Validation /
 * QueryExecution), mirroring the ivyforms Common\Exceptions hierarchy.
 *
 * @package WPDataTables\Common\Exceptions
 */
class WDTException extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
