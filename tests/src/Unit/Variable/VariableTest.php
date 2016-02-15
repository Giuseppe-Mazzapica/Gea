<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit\Variable;

use Gea\Tests\TestCase;
use Gea\Variable\Variable;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class VariableTest extends TestCase
{
    public function testValuesIgnoreKeyCase()
    {
        $var = new Variable([
            'NAME'  => 'FOO',
            'ValuE' => 'BAR',
        ]);

        assertTrue($var->isValid());
        assertSame('FOO', $var->offsetGet('name'));
        assertSame('FOO', $var->offsetGet('nAmE'));
        assertSame('BAR', $var->offsetGet('VALUE'));
        assertSame('BAR', $var->offsetGet('value'));
        assertSame('BAR', $var->offsetGet('ValuE'));
    }

    public function testIsNotValidWithNoName()
    {
        $var = new Variable([
            'value' => 'BAR',
        ]);

        assertFalse($var->isValid());
    }

    public function testIsNotValidWithNoValue()
    {
        $var = new Variable([
            'name' => 'BAR',
        ]);

        assertFalse($var->isValid());
    }

    public function testIsNotValidWithNonStringName()
    {
        $var = new Variable([
            'name'  => 1,
            'value' => 'BAR',
        ]);

        assertFalse($var->isValid());
    }

    public function testIsValidWithNullValue()
    {
        $var = new Variable([
            'name'  => 'BAR',
            'value' => null,
        ]);

        assertTrue($var->isValid());
    }

    public function testIsNotNestedWithNoNested()
    {
        $var = new Variable([
            'name'  => 'BAR',
            'value' => 'BAR',
        ]);

        assertFalse($var->isNested());
    }

    public function testIsNotNestedWithNoArrayNested()
    {
        $var = new Variable([
            'name'   => 'BAR',
            'value'  => 'BAR',
            'nested' => true,
        ]);

        assertFalse($var->isNested());
    }

    public function testIsNotNestedWithEmptyArrayNested()
    {
        $var = new Variable([
            'name'   => 'BAR',
            'value'  => 'BAR',
            'nested' => [],
        ]);

        assertFalse($var->isNested());
    }

    public function testIsNested()
    {
        $var = new Variable([
            'name'   => 'BAR',
            'value'  => 'BAR',
            'nested' => ['FOO'],
        ]);

        assertTrue($var->isNested());
    }

    public function testArrayAccess()
    {
        $var = new Variable([
            'name'   => 'BAR',
            'value'  => 'BAR',
        ]);

        assertFalse(empty($var['name']));
        assertFalse(empty($var['value']));
        assertTrue(empty($var['nested']));
        assertTrue(empty($var['meh']));
        assertSame('BAR', $var['name']);
        assertSame('BAR', $var['value']);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /Invalid offset/
     */
    public function testOffsetGetFailsIfOffsetNotExist()
    {
        $var = new Variable([
            'name'   => 'BAR',
            'value'  => 'BAR',
        ]);

        $var['nested'];
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /immutable/
     */
    public function testImmutabilityOnSet()
    {
        $var = new Variable([
            'name'   => 'BAR',
            'value'  => null,
        ]);

        $var['value'] = 'BAR';
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /immutable/
     */
    public function testImmutabilityOnUnset()
    {
        $var = new Variable([
            'name'   => 'BAR',
            'value'  => 'BAR',
        ]);

        unset($var['value']);
    }
}
