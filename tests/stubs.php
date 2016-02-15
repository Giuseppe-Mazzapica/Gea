<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Stub;

use Gea\Filter\FilterInterface;

class StubFilter implements FilterInterface
{
    public $lazy = true;

    public $args = [];

    public function __construct()
    {
        $this->args = func_get_args();
    }

    public function isLazy()
    {
        return $this->lazy;
    }

    public function filter($value)
    {
        return $value;
    }
}
