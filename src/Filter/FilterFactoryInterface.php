<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Filter;

/**
 * Easy the instantiation of filters objects only providing filter name.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
interface FilterFactoryInterface
{
    /**
     * @param  string                      $name
     * @param  array                       $args
     * @return \Gea\Filter\FilterInterface
     */
    public function factory($name, array $args = []);
}
