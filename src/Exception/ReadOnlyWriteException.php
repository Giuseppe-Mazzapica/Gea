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
final class ReadOnlyWriteException extends \RuntimeException
{
    public static function forVarName($name, $action = 'write')
    {
        is_string($name) or $name = gettype($name);
        is_string($action) or $action = 'write';

        $message = 'Can\'t %s "%s" because Gea is in read-only mode.';

        return new static(sprintf($message, $name, $action));
    }

    public static function forVars($action = 'flush')
    {
        is_string($action) or $action = 'flush';

        $message = 'Can\'t %s variables because Gea is in read-only mode.';

        return new static(sprintf($message, $action));
    }
}
