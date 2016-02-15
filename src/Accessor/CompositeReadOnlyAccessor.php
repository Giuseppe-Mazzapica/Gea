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

use Gea\Exception\ReadOnlyWriteException;

/**
 * Accessor uses all of `$_ENV`, `$_SERVER` and `getenv` / `putenv` to retrieve and store variables.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class CompositeReadOnlyAccessor implements AccessorInterface
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
     * Disabled because read-only
     *
     * @param  string      $name
     * @param  string|null $value
     * @return void
     */
    public function write($name, $value = null)
    {
        throw ReadOnlyWriteException::forVarName($name, 'write');
    }

    /**
     * Disabled because read-only
     *
     * @param  string $name
     * @return void
     */
    public function discard($name)
    {
        throw ReadOnlyWriteException::forVarName($name, 'discard');
    }
}
