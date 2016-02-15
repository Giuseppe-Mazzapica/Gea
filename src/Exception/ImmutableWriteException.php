<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Exception;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class ImmutableWriteException extends \RuntimeException
{
    public static function forVarName($name)
    {
        is_string($name) or $name = gettype($name);
        $message = 'Gea does\'t allow to overwrite variables, so "%s" can\'t be overwritten.';
        $message .= ' When Gea is not in read-only mode, you can either discard or to hard-flush';
        $message .= ' variables to change their value.';

        return new static(sprintf($message, $name));
    }
}
