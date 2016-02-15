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

use Gea\Accessor\AccessorInterface;
use Gea\Parser\ParserInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class LoaderFactory implements LoaderFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function factory(ParserInterface $parser, AccessorInterface $accessor, $class = null)
    {
        if (! is_string($class) || ! is_subclass_of($class, self::CONTRACT, true)) {
            $class = NestedAllowedLoader::class;
        }

        return new $class($parser, $accessor);
    }
}
