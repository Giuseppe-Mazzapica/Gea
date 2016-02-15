<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit\Loader;

use Gea\Accessor\AccessorInterface;
use Gea\Loader\LoaderFactory;
use Gea\Loader\LoaderInterface;
use Gea\Loader\NestedAllowedLoader;
use Gea\Parser\ParserInterface;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class LoaderFactoryTest extends TestCase
{
    public function testFactory()
    {
        /** @var \Gea\Parser\ParserInterface $parser */
        $parser = \Mockery::mock(ParserInterface::class);
        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = \Mockery::mock(AccessorInterface::class);

        $factory = new LoaderFactory();

        $loader = $factory->factory($parser, $accessor);

        assertInstanceOf(NestedAllowedLoader::class, $loader);
    }

    public function testFactoryCustomClass()
    {
        /** @var \Gea\Parser\ParserInterface $parser */
        $parser = \Mockery::mock(ParserInterface::class);
        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = \Mockery::mock(AccessorInterface::class);

        $loaderClass = get_class(\Mockery::mock(LoaderInterface::class));

        $factory = new LoaderFactory();

        $loader = $factory->factory($parser, $accessor, $loaderClass);

        assertInstanceOf(LoaderInterface::class, $loader);
        assertInstanceOf($loaderClass, $loader);
    }
}
