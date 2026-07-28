<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace MMMScssPhp\ScssPhp\Block;

use MMMScssPhp\ScssPhp\Block;
use MMMScssPhp\ScssPhp\Type;

/**
 * @internal
 */
class ElseBlock extends Block
{
    public function __construct()
    {
        $this->type = Type::T_ELSE;
    }
}
