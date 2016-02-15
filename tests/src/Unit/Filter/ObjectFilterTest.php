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

use Gea\Filter\ObjectFilter;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class ObjectFilterTest extends TestCase
{
    public function testFilterWithNull()
    {
        $filter = new ObjectFilter('\stdClass');
        $object = $filter->filter(null);

        assertInternalType('object', $object);
        assertInstanceOf(\stdClass::class, $object);
    }

    public function testFilterWithValue()
    {
        $filter = new ObjectFilter(\ArrayObject::class);
        $object = $filter->filter(['foo' => 'Foo']);

        assertInternalType('object', $object);
        assertInstanceOf(\ArrayObject::class, $object);
        assertSame('Foo', $object['foo']);
    }

    public function testFilterUsingClassSyntax()
    {
        $filter = new ObjectFilter('stdClass::class');
        $object = $filter->filter(null);

        assertInternalType('object', $object);
        assertInstanceOf(\stdClass::class, $object);
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     * @expectedExceptionMessageRegExp /given class name must be in a string/
     */
    public function testFilterFailsIfClassGivenIsNotString()
    {
        $filter = new ObjectFilter(1);
        $filter->filter(1);
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     * @expectedExceptionMessageRegExp /given class name is not a class/
     */
    public function testFilterFailsIfClassGivenIsNotClass()
    {
        $filter = new ObjectFilter('meh');
        $filter->filter('meh');
    }

    public function testLazy()
    {
        $filter = new ObjectFilter('stdClass');
        assertTrue($filter->isLazy());
    }
}
