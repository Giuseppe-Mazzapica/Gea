<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file incorporates work covered by the following copyright and
 * permission notices:
 *
 *   Dotenv is (c) 2013 Vance Lucas - vance@vancelucas.com - http://www.vancelucas.com
 *   Dotenv is released under BSD 3-Clause License
 */

namespace Gea\Accessor;

/**
 * Accessor uses all of `$_ENV`, `$_SERVER` and `getenv` / `putenv` to retrieve and store variables.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class CompositeAccessor implements AccessorInterface
{
    /**
     * @inheritdoc
     */
    public function read($name)
    {
        switch (true) {
            case array_key_exists($name, $_ENV):
                return $_ENV[$name];
            case array_key_exists($name, $_SERVER):
                return $_SERVER[$name];
            default:
                $value = getenv($name);
        }

        return $value === false ? null : $value; // switch getenv default to null
    }

    /**
     * Set an environment variable.
     *
     * This is done using:
     * - putenv
     * - $_ENV
     * - $_SERVER.
     *
     * The environment variable value is stripped of single and double quotes.
     *
     * @param  string      $name
     * @param  string|null $value
     * @return string      Name of the variable just set
     */
    public function write($name, $value = null)
    {
        if (is_null($value)) {
            return $this->discard($name);
        }

        $now = $this->read($name);

        if (! is_null($now)) {
            throw new \RuntimeException(
                sprintf(
                    'Variable %s can\'t be overwritten. You need either to discard or to hard-flush vars to change their value.',
                    $name
                )
            );
        }

        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;

        return $name;
    }

    /**
     * @inheritdoc
     */
    public function discard($name)
    {
        putenv($name);
        unset($_ENV[$name]);
        unset($_SERVER[$name]);

        return $name;
    }
}
