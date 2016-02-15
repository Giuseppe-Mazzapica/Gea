<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit\Accessor;

use Andrew\Proxy;
use Gea\Accessor\CachedFilteredAccessor;
use Gea\Accessor\AccessorInterface;
use Gea\Filter\FilterInterface;
use Gea\Exception\FilterException;
use Gea\Tests\TestCase;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class CachedFilteredAccessorTest extends TestCase
{
    public function testAddFilter()
    {
        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);
        $filtered = new CachedFilteredAccessor($accessor);

        /** @var \Gea\Filter\FilterInterface $filter */
        $filter = Mockery::mock(FilterInterface::class);

        $one = $filtered->addFilter('foo', $filter);
        $two = $filtered->addFilter('foo', $filter);
        $three = $filtered->addFilter('bar', $filter);

        $filters = (new Proxy($filtered))->filters;
        $expected = ['foo' => [$filter, $filter], 'bar' => [$filter]];

        assertSame($filtered, $one);
        assertSame($one, $two);
        assertSame($two, $three);
        assertSame($expected, $filters);
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     * @expectedExceptionMessage FOO
     */
    public function testReadException()
    {
        /** @var \Gea\Accessor\AccessorInterface|\Mockery\MockInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);
        $accessor
            ->shouldReceive('read')
            ->once()
            ->with('FOO')
            ->andReturn('FOO!');

        /** @var \Gea\Filter\FilterInterface|\Mockery\MockInterface $filter */
        $filter = Mockery::mock(FilterInterface::class);
        $filter
            ->shouldReceive('filter')
            ->once()
            ->with('FOO!')
            ->andThrow(new FilterException(':name'));

        $filtered = new CachedFilteredAccessor($accessor);

        $proxy = new Proxy($filtered);
        /** @noinspection PhpUndefinedFieldInspection */
        $proxy->filters = ['FOO' => [$filter]];

        $filtered->read('FOO');
    }

    public function testRead()
    {
        /** @var \Gea\Accessor\AccessorInterface|\Mockery\MockInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);
        $accessor
            ->shouldReceive('read')
            ->once()
            ->with('vowels')
            ->andReturn('Vowels:');

        /** @var \Gea\Filter\FilterInterface|\Mockery\MockInterface $filterA */
        $filterA = Mockery::mock(FilterInterface::class);
        $filterE = clone $filterA;
        $filterI = clone $filterA;

        $filterA
            ->shouldReceive('filter')
            ->once()
            ->andReturnUsing(function ($vowels) {
                return $vowels.' A,';
            });

        $filterE
            ->shouldReceive('filter')
            ->once()
            ->andReturnUsing(function ($vowels) {
                return $vowels.' E,';
            });

        $filterI
            ->shouldReceive('filter')
            ->once()
            ->andReturnUsing(function ($vowels) {
                return $vowels.' I.';
            });

        $filtered = new CachedFilteredAccessor($accessor);

        $filtered
            ->addFilter('vowels', $filterA)
            ->addFilter('vowels', $filterE)
            ->addFilter('vowels', $filterI);

        assertSame('Vowels: A, E, I.', $filtered->read('vowels'));
    }

    public function testReadIsCached()
    {
        /** @var \Gea\Accessor\AccessorInterface|\Mockery\MockInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);
        $accessor
            ->shouldReceive('read')
            ->once()
            ->with('foo')
            ->andReturn('Foo!');

        $filtered = new CachedFilteredAccessor($accessor);
        $foo1 = $filtered->read('foo');
        $foo2 = $filtered->read('foo');

        assertSame($foo1, $foo2);
        assertSame('Foo!', $foo1);
    }
}
