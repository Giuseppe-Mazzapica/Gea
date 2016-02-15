<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Variable;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
interface VariableFactoryInterface
{
    const CONTRACT = VariableInterface::class;

    /**
     * @param  string                          $name
     * @param  mixed                           $value
     * @param  string|null                     $class
     * @return \Gea\Variable\VariableInterface
     */
    public function factory($name, $value, $class = null);
}
