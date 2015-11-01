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
    const CONTRACT = LoaderInterface::class;

    /**
     * @inheritdoc
     */
    public function factory(ParserInterface $parser, AccessorInterface $accessor, $class = null)
    {
        $loaderClass = EnvLoader::class;
        if (is_string($class) && is_subclass_of($class, self::CONTRACT)) {
            $loaderClass = $class;
        }

        return new $loaderClass($parser, $accessor);
    }
}
