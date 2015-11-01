<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Accessor;

use Gea\Filter\FilterInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
interface FilteredAccessorInterface extends AccessorInterface
{
    /**
     * @param  string                                  $name
     * @param  \Gea\Filter\FilterInterface             $filter
     * @return \Gea\Accessor\FilteredAccessorInterface
     */
    public function addFilter($name, FilterInterface $filter);
}
