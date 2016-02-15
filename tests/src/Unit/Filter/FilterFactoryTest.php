<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit\Filter;

use Gea\Filter\ArrayFilter;
use Gea\Filter\BoolFilter;
use Gea\Filter\EnumFilter;
use Gea\Filter\FilterFactory;
use Gea\Filter\FloatFilter;
use Gea\Filter\ObjectFilter;
use Gea\Filter\RequiredFilter;
use Gea\Tests\Stub\StubFilter;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class FilterFactoryTest extends TestCase
{
    /**
     * @dataProvider filterClasses
     * @param string $name
     * @param string $class
     * @param array  $args
     */
    public function testFactoryDefaults($name, $class, array $args)
    {
        $factory = new FilterFactory();
        $filter = $factory->factory($name, $args);

        assertInternalType('object', $filter);
        assertInstanceOf($class, $filter);
    }

    public function filterClasses()
    {
        return [
            ['array', ArrayFilter::class, []],
            ['bool', BoolFilter::class, []],
            ['enum', EnumFilter::class, [[]]],
            ['float', FloatFilter::class, []],
            ['object', ObjectFilter::class, [\ArrayObject::class]],
            ['required', RequiredFilter::class, []],
            ['ARRAY', ArrayFilter::class, []],
            ['BooL', BoolFilter::class, []],
            ['ENUM', EnumFilter::class, [[]]],
            ['fLoAt', FloatFilter::class, []],
            ['objecT', ObjectFilter::class, [\ArrayObject::class]],
            ['Required', RequiredFilter::class, []],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryFailsIfNameIsNotString()
    {
        $factory = new FilterFactory();
        $factory->factory(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryFailsIfNameIsNotRecognized()
    {
        $factory = new FilterFactory();
        $factory->factory('meh');
    }

    public function testAddFilter()
    {
        $factory = new FilterFactory();
        $return = $factory->addFilter('array-alias', ArrayFilter::class);
        $filter = $factory->factory('array-alias');

        assertSame($return, $factory);
        assertInstanceOf(ArrayFilter::class, $filter);
    }

    public function testAddAndResolveFiltersMoreArgs()
    {
        $factory = new FilterFactory();
        $factory
            ->addFilter('f2', StubFilter::class)
            ->addFilter('f3', StubFilter::class)
            ->addFilter('f4', StubFilter::class)
            ->addFilter('f5', StubFilter::class);

        /** @var \Gea\Tests\Stub\StubFilter $f2 */
        $f2 = $factory->factory('f2', ['a', 'b']);
        /** @var \Gea\Tests\Stub\StubFilter $f3 */
        $f3 = $factory->factory('f2', ['a', 'b', 'c']);
        /** @var \Gea\Tests\Stub\StubFilter $f4 */
        $f4 = $factory->factory('f2', ['a', 'b', 'c', 'd']);
        /** @var \Gea\Tests\Stub\StubFilter $f5 */
        $f5 = $factory->factory('f2', ['a', 'b', 'c', 'd', 'e']);

        assertInstanceOf(StubFilter::class, $f2);
        assertInstanceOf(StubFilter::class, $f3);
        assertInstanceOf(StubFilter::class, $f4);
        assertInstanceOf(StubFilter::class, $f5);
        assertNotSame($f2, $f3);
        assertNotSame($f2, $f4);
        assertNotSame($f2, $f3);
        assertNotSame($f3, $f4);
        assertNotSame($f3, $f5);
        assertNotSame($f4, $f5);
        assertSame($f2->args, ['a', 'b']);
        assertSame($f3->args, ['a', 'b', 'c']);
        assertSame($f4->args, ['a', 'b', 'c', 'd']);
        assertSame($f5->args, ['a', 'b', 'c', 'd', 'e']);
    }

    public function testAddFilterCaseInsensitive()
    {
        $factory = new FilterFactory();
        $return = $factory->addFilter('ARRAY-ALIAS', ArrayFilter::class);
        $filter = $factory->factory('array-alias');

        assertSame($return, $factory);
        assertInstanceOf(ArrayFilter::class, $filter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter name must be in a string
     */
    public function testAddFilterFailsIfNameIsNotString()
    {
        $factory = new FilterFactory();
        $factory->addFilter(1, ArrayFilter::class);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /already assigned/
     */
    public function testAddFilterFailsIfNameIsAlreadyAdded()
    {
        $factory = new FilterFactory();
        $factory->addFilter('ARRAY', ArrayFilter::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Filter class .+ must be in a string/
     */
    public function testAddFilterFailsIfClassIsNotString()
    {
        $factory = new FilterFactory();
        $factory->addFilter('new_filter', new ArrayFilter());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /is not a class/
     */
    public function testAddFilterFailsIfClassIsNotClass()
    {
        $factory = new FilterFactory();
        $factory->addFilter('new_filter', '.meh');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /does not implement/
     */
    public function testAddFilterFailsIfClassIsNotAFilter()
    {
        $factory = new FilterFactory();
        $factory->addFilter('new_filter', \ArrayObject::class);
    }
}
