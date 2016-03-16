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
use Gea\Accessor\CachedAccessorInterface;
use Gea\Accessor\FilteredAccessorInterface;
use Gea\Filter\FilterFactoryInterface;
use Gea\Filter\FilterInterface;
use Gea\Gea;
use Gea\Loader\LoaderInterface;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class GeaTest extends TestCase
{
    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /before first accessing value/
     */
    public function testAddFilterFailsWhenLoaded()
    {
        $accessor = \Mockery::mock(CachedAccessorInterface::class, FilteredAccessorInterface::class);
        $accessor->shouldReceive('isCached')->once()->with('foo')->andReturn(true);
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        $gea->addFilter('foo', 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /name to be filtered .+ in a string/
     */
    public function testAddFilterFailsWhenBadVarName()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        $gea->addFilter(true, 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /a string or an array/
     */
    public function testAddFilterFailsWhenBadFilterName()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);
        $gea->addFilter('foo', new \stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /a string or an array/
     */
    public function testAddFilterFailsWhenFilterIsNotStringOrArray()
    {
        $filter = \Mockery::mock(FilterInterface::class);
        $filter->shouldReceive('isLazy')->andReturn(false);

        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('addFilter')
            ->with(\Mockery::type('string'), $filter)
            ->andReturnNull();

        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);

        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);
        $filterFactory
            ->shouldReceive('factory')
            ->with(\Mockery::type('string'), \Mockery::type('array'))
            ->andReturn($filter);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        $gea->addFilter('foo', ['bar', new \stdClass()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /a string or an array/
     */
    public function testAddFilterFailsWhenFilterIsBadArray()
    {
        $filter = \Mockery::mock(FilterInterface::class);
        $filter->shouldReceive('isLazy')->andReturn(false);

        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('addFilter')
                 ->with(\Mockery::type('string'), $filter)
                 ->andReturnNull();

        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);

        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);
        $filterFactory
            ->shouldReceive('factory')
            ->with(\Mockery::type('string'), \Mockery::type('array'))
            ->andReturn($filter);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        $gea->addFilter('foo', ['bar', ['bar', 'baz']]);
    }

    public function testAddFilterLazyFromString()
    {
        $filter = \Mockery::mock(FilterInterface::class);
        $filter->shouldReceive('isLazy')->andReturn(true);

        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('addFilter')
                 ->with('the_name', $filter)
                 ->andReturnNull();

        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);

        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);
        $filterFactory
            ->shouldReceive('factory')
            ->with('foo', [])
            ->andReturn($filter);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $toRead = $proxy->toReadFirst;

        assertSame($gea, $gea->addFilter('the_name', 'foo'));
        assertSame([], $toRead);
    }

    public function testAddFilterNotLazyFromString()
    {
        $filter = \Mockery::mock(FilterInterface::class);
        $filter->shouldReceive('isLazy')->andReturn(false);

        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('addFilter')
                 ->with('the_name', $filter)
                 ->andReturnNull();

        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);

        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);
        $filterFactory
            ->shouldReceive('factory')
            ->with('foo', [])
            ->andReturn($filter);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);
        $added = $gea->addFilter('the_name', 'foo');

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $toRead = $proxy->toReadFirst;

        assertSame($gea, $added);
        assertSame(['the_name'], $toRead);
    }

    public function testAddFiltersFromArray()
    {
        $filterFoo = \Mockery::mock(FilterInterface::class);
        $filterBar = clone $filterFoo;

        $filterFoo->shouldReceive('isLazy')->andReturn(true);
        $filterBar->shouldReceive('isLazy')->andReturn(false);

        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('addFilter')
            ->once()
            ->with('a', $filterFoo)
            ->andReturnNull();
        $accessor->shouldReceive('addFilter')
            ->once()
            ->with('b', $filterFoo)
            ->andReturnNull();
        $accessor->shouldReceive('addFilter')
            ->with('b', $filterBar)
            ->andReturnNull();

        static $loaded = null;
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);

        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);
        $filterFactory
            ->shouldReceive('factory')
            ->twice()
            ->with('foo', [])
            ->andReturn($filterFoo);
        $filterFactory
            ->shouldReceive('factory')
            ->once()
            ->with('bar', ['some', 'args'])
            ->andReturn($filterBar);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        $added = $gea
            ->addFilter('a', ['foo'])
            ->addFilter('b', ['foo', 'bar' => ['some', 'args']]);

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $toRead = $proxy->toReadFirst;

        assertSame($gea, $added);
        assertSame(['b'], $toRead);
    }

    public function testAddFilterFromFilter()
    {
        $filter = \Mockery::mock(FilterInterface::class);
        $filter->shouldReceive('isLazy')->once()->andReturn(true);

        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('addFilter')->once()->with('foo', $filter)->andReturnNull();

        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false, true);

        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);
        $filterFactory->shouldReceive('factory')->never();

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        assertSame($gea, $gea->addFilter('foo', $filter));
    }

    public function testLoadReturnLoadedVarsAndNotHold()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->never();
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('load')->andReturn(['foo', 'bar']);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);
        $loaded =  $gea->load();

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $hold = $proxy->varNames;

        assertSame(['foo', 'bar'], $loaded);
        assertSame([], $hold);
    }

    public function testLoadReturnLoadedVarsAndHold()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->never();
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('load')->andReturn(['foo', 'bar']);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);
        $loaded =  $gea->load();

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $hold = $proxy->varNames;

        assertSame(['foo', 'bar'], $loaded);
        assertSame(['foo', 'bar'], $hold);
    }

    public function testLoadReadFirstMarkedVars()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturn();
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(true);
        $loader->shouldReceive('load')->andReturn(['foo', 'bar']);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $proxy->toReadFirst = ['foo'];

        $loaded =  $gea->load();

        assertSame(['foo', 'bar'], $loaded);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /VAR_NAMES_HOLD flag is true/
     */
    public function testVarNamesFailsWhenNotHoldingVars()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);
        $gea->varNames();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /after variables are loaded/
     */
    public function testVarNamesFailsWhenNotLoaded()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(false);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);
        $gea->varNames();
    }

    public function testVarNamesReturnLoadedIfHoldIsTrue()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('load')->andReturn(['foo', 'bar']);
        $loader->shouldReceive('loaded')->andReturn(true);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);
        $gea->load();

        assertSame(['foo', 'bar'], $gea->varNames());
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testFlushFailsIfReadOnly()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::READ_ONLY, $filterFactory);
        $gea->flush();
    }

    public function testSoftFlushFlushesVarNames()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('discard')->never();
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('load')->andReturn(['foo']);
        $loader->shouldReceive('loaded')->andReturn(true);
        $loader->shouldReceive('flush')->once()->andReturnNull();
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);
        $gea->load();

        assertSame(['foo'], $gea->varNames());
        $gea->flush();
        assertSame([], $gea->varNames());
    }

    public function testHardFlushFlushesAndDiscardAllVarNamesByDefault()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('discard')
            ->twice()
            ->andReturnUsing(function($var) {
                assertTrue(in_array($var, ['foo', 'bar'], true));
            });

        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('load')->andReturn(['foo', 'bar']);
        $loader->shouldReceive('loaded')->andReturn(true);
        $loader->shouldReceive('flush')->once()->andReturnNull();

        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);
        $gea->load();

        assertSame(['foo', 'bar'], $gea->varNames());
        $gea->flush(Gea::FLUSH_HARD);
        assertSame([], $gea->varNames());
    }

    public function testHardFlushFlushesAllVarNamesDiscardGiven()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('discard')
                 ->twice()
                 ->andReturnUsing(function($var) {
                     assertTrue(in_array($var, ['foo', 'bar'], true));
                 });

        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('load')->andReturn(['foo', 'bar']);
        $loader->shouldReceive('loaded')->andReturn(true);
        $loader->shouldReceive('flush')->once()->andReturnNull();
        
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);
        $gea->load();

        assertSame(['foo', 'bar'], $gea->varNames());
        $gea->flush(Gea::FLUSH_HARD, ['foo']);
        assertSame([], $gea->varNames());
    }

    public function testReadLoadsIfNotLoaded()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturn('Foo!');
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->once()->andReturn(false);
        $loader->shouldReceive('load')->once()->andReturnNull();
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);

        assertSame('Foo!', $gea->read('foo'));
    }

    public function testReadNotLoadsIfLoaded()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturn('Foo!');
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->once()->andReturn(true);
        $loader->shouldReceive('load')->never();
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);

        assertSame('Foo!', $gea->read('foo'));
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testWriteFailsIfReadOnly()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::READ_ONLY, $filterFactory);
        $gea->write('foo', 'bar');
    }

    /**
     * @expectedException \Gea\Exception\ImmutableWriteException
     */
    public function testWriteFailsIfAlreadyWritten()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $proxy->varNames = ['foo'];

        $gea->write('foo', 'bar');
    }

    public function testWriteUpdateVarNamesWhenHoldIsTrue()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('write')->once()->with('foo', 'bar')->andReturn('foo');
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_HOLD, $filterFactory);
        $name = $gea->write('foo', 'bar');

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $varNames = $proxy->varNames;

        assertSame(['foo'], $varNames);
        assertSame('foo', $name);
    }

    public function testWriteNotUpdateVarNamesWhenHoldIsFalse()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('write')->once()->with('foo', 'bar')->andReturn('foo');
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);
        $name = $gea->write('foo', 'bar');

        $proxy = new Proxy($gea);
        /** @noinspection PhpUndefinedFieldInspection */
        $varNames = $proxy->varNames;

        assertSame([], $varNames);
        assertSame('foo', $name);
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testDiscardFailsIfReadOnly()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::READ_ONLY, $filterFactory);
        $gea->discard('foo');
    }

    public function testDiscardDoNothingIfValueNotSet()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturnNull();
        $accessor->shouldReceive('discard')->never();
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(true);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        assertNull($gea->discard('foo'));
    }

    public function testDiscardReturnDiscarded()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturn('Foo!');
        $accessor->shouldReceive('discard')->once()->with('foo');
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(true);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        assertSame('Foo!', $gea->discard('foo'));
    }

    public function testOffsetExists()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturnNull();
        $accessor->shouldReceive('read')->once()->with('bar')->andReturn('Bar!');
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(true);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        assertFalse($gea->offsetExists('foo'));
        assertTrue($gea->offsetExists('bar'));
    }

    public function testOffsetGetAliasRead()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturnNull();
        $accessor->shouldReceive('read')->once()->with('bar')->andReturn('Bar!');
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(true);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        assertNull($gea->offsetGet('foo'));
        assertSame('Bar!', $gea->offsetGet('bar'));
    }

    public function testOffsetSetAliasWriteButReturnNull()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('write')->once()->with('foo', 'Foo!')->andReturn('foo');
        $loader = \Mockery::mock(LoaderInterface::class);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        assertNull($gea->offsetSet('foo', 'Foo!'));
    }

    public function testOffsetSetAliasDiscardButReturnNull()
    {
        $accessor = \Mockery::mock(FilteredAccessorInterface::class);
        $accessor->shouldReceive('read')->once()->with('foo')->andReturn('Foo!');
        $accessor->shouldReceive('discard')->once()->with('foo')->andReturn('Foo!');
        $loader = \Mockery::mock(LoaderInterface::class);
        $loader->shouldReceive('loaded')->andReturn(true);
        $filterFactory = \Mockery::mock(FilterFactoryInterface::class);

        $gea = new Gea($accessor, $loader, Gea::VAR_NAMES_NOT_HOLD, $filterFactory);

        assertNull($gea->offsetUnset('foo'));
    }
}
