<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit;

use Andrew\Proxy;
use Gea\Accessor\CachedFilteredAccessor;
use Gea\Accessor\CompositeAccessor;
use Gea\Accessor\CompositeReadOnlyAccessor;
use Gea\Accessor\FilteredAccessorInterface;
use Gea\Filter\FilterFactory;
use Gea\Filter\FilterFactoryInterface;
use Gea\Gea;
use Gea\Loader\DummyLoader;
use Gea\Loader\LoaderFactoryInterface;
use Gea\Loader\LoaderInterface;
use Gea\Loader\NestedAllowedLoader;
use Gea\Parser\FileParser;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class GeaNamedConstructorTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /valid path/
     */
    public function testInstanceFailIfBadPath()
    {
        Gea::instance(__DIR__, '.env');
    }

    public function testInstanceDefaults()
    {
        $gea = Gea::instance(getenv('GEA_TESTS_FIXTURES_PATH'));

        $proxy = new Proxy($gea);

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame(Gea::VAR_NAMES_NOT_HOLD, $proxy->flags);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CachedFilteredAccessor::class, $proxy->accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(NestedAllowedLoader::class, $proxy->loader);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(FilterFactory::class, $proxy->filterFactory);
    }

    public function testInstanceReadOnly()
    {
        $gea = Gea::instance(getenv('GEA_TESTS_FIXTURES_PATH'), '.env', Gea::READ_ONLY);

        $proxy = new Proxy($gea);

        /** @noinspection PhpUndefinedFieldInspection */
        $accessor = new Proxy($proxy->accessor);

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame(Gea::READ_ONLY, $proxy->flags);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CompositeReadOnlyAccessor::class, $accessor->accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(NestedAllowedLoader::class, $proxy->loader);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(FilterFactory::class, $proxy->filterFactory);
    }

    public function testInstanceCustomDeps()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $filters = \Mockery::mock(FilterFactoryInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $loaders = \Mockery::mock(LoaderFactoryInterface::class);
        $loaders
            ->shouldReceive('factory')
            ->with(\Mockery::type(FileParser::class), $accessor)
            ->andReturn($loader);

        $path = getenv('GEA_TESTS_FIXTURES_PATH');

        $gea = Gea::instance(
            $path,
            '.env',
            Gea::VAR_NAMES_NOT_HOLD,
            $accessor,
            $filters,
            $loaders
        );

        $proxy = new Proxy($gea);

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame($accessor, $proxy->accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame($filters, $proxy->filterFactory);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame($loader, $proxy->loader);
    }

    public function testNoLoaderInstance()
    {
        $gea = Gea::noLoaderInstance();

        $proxy = new Proxy($gea);

        /** @noinspection PhpUndefinedFieldInspection */
        $accessor = $proxy->accessor;
        $innerAccessor = (new Proxy($accessor))->accessor;

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CachedFilteredAccessor::class, $accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CompositeAccessor::class, $innerAccessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(FilterFactory::class, $proxy->filterFactory);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(DummyLoader::class, $proxy->loader);
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::NO_LOADER));
    }

    public function testNoLoaderInstanceReadOnly()
    {
        $gea = Gea::noLoaderInstance(null, null, Gea::READ_ONLY);

        $proxy = new Proxy($gea);

        /** @noinspection PhpUndefinedFieldInspection */
        $accessor = $proxy->accessor;
        $innerAccessor = (new Proxy($accessor))->accessor;

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CachedFilteredAccessor::class, $accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CompositeReadOnlyAccessor::class, $innerAccessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(FilterFactory::class, $proxy->filterFactory);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(DummyLoader::class, $proxy->loader);
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::NO_LOADER));
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::READ_ONLY));
    }

    public function testNoLoaderInstanceCustomDeps()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $filters = \Mockery::mock(FilterFactoryInterface::class);

        $gea = Gea::noLoaderInstance($accessor, $filters);

        $proxy = new Proxy($gea);

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame($accessor, $proxy->accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame($filters, $proxy->filterFactory);
    }

    public function testReadOnlyInstance()
    {
        $gea = Gea::readOnlyInstance();

        $proxy = new Proxy($gea);

        /** @noinspection PhpUndefinedFieldInspection */
        $accessor = $proxy->accessor;
        $innerAccessor = (new Proxy($accessor))->accessor;

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CachedFilteredAccessor::class, $accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(CompositeReadOnlyAccessor::class, $innerAccessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(FilterFactory::class, $proxy->filterFactory);
        /** @noinspection PhpUndefinedFieldInspection */
        assertInstanceOf(DummyLoader::class, $proxy->loader);
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::READ_ONLY));
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::NO_LOADER));
    }

    public function testReadOnlyInstanceCustomDeps()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $filters = \Mockery::mock(FilterFactoryInterface::class);

        $gea = Gea::readOnlyInstance($accessor, $filters, Gea::VAR_NAMES_HOLD);

        $proxy = new Proxy($gea);

        assertInstanceOf(Gea::class, $gea);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame($accessor, $proxy->accessor);
        /** @noinspection PhpUndefinedFieldInspection */
        assertSame($filters, $proxy->filterFactory);
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::READ_ONLY));
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::NO_LOADER));
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::READ_ONLY));
        /** @noinspection PhpUndefinedFieldInspection */
        assertGreaterThan(0, ($proxy->flags & Gea::VAR_NAMES_HOLD));
    }
}
