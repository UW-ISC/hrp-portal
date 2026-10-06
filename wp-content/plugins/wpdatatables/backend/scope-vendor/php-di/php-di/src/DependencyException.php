<?php

declare(strict_types=1);

namespace WPDataTables\Vendor\DI;

use WPDataTables\Vendor\Psr\Container\ContainerExceptionInterface;

/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
