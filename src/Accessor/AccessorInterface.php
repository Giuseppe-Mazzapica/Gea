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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Dotenv
 */
interface AccessorInterface
{
    /**
     * Get an environment variable.
     *
     * @param  string $name
     * @return string
     */
    public function read($name);

    /**
     * Set an environment variable.
     *
     * @param  string      $name
     * @param  string|null $value
     * @return string      Name of the variable just set
     */
    public function write($name, $value = null);

    /**
     * @param  string $var
     * @return string Name of the variable just discarded
     */
    public function discard($var);
}
