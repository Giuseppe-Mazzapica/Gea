<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Loader;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
interface LoaderInterface
{
    /**
     * Load the available environment variables and return the names of the loaded variables.
     *
     * @return array
     */
    public function load();

    /**
     * Return true if loading already happen.
     *
     * @return bool
     */
    public function loaded();

    /**
     * Reset the status of the loaded and allow for subsequent loadings.
     *
     * @return void
     */
    public function flush();
}
