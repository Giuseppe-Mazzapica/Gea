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

use Andrew\Proxy;
use Gea\Tests\TestCase;
use Gea\Variable\Variable;
use Gea\Variable\VariableFactory;
use Gea\Variable\VariableInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class VariableFactoryTest extends TestCase
{
    public function testFactoryDefaultClassIfGivenIsNotString()
    {
        $factory = new VariableFactory();

        $var = $factory->factory('foo', 'bar', 1);

        assertInstanceOf(Variable::class, $var);
    }

    public function testFactoryDefaultClassIfGivenIsNotAVariableClass()
    {
        $factory = new VariableFactory();

        $var = $factory->factory('foo', 'bar', __CLASS__);

        assertInstanceOf(Variable::class, $var);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /non-empty string/
     */
    public function testFactoryFailsIfNameIsNotString()
    {
        $factory = new VariableFactory();
        $factory->factory(1, 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /non-empty string/
     */
    public function testFactoryFailsIfNameIsEmptyString()
    {
        $factory = new VariableFactory();
        $factory->factory('     ', 'bar');
    }

    public function testFactoryNested()
    {
        $factory = new VariableFactory();

        $var = $factory->factory('foo', '${bar}baz');

        $proxy = new Proxy($var);

        /** @noinspection PhpUndefinedFieldInspection */
        $nested = $proxy->data['nested'];

        assertInstanceOf(Variable::class, $var);
        assertSame(['bar'], $nested);
    }

    public function testFactoryMultipleNested()
    {
        $factory = new VariableFactory();
        $var = $factory->factory('foo', '${bar}/${baz}');

        $proxy = new Proxy($var);

        /** @noinspection PhpUndefinedFieldInspection */
        $nested = $proxy->data['nested'];

        assertInstanceOf(Variable::class, $var);
        assertSame(['bar', 'baz'], $nested);
    }

    public function testFactoryCustomClass()
    {
        $varClass = get_class(\Mockery::mock(VariableInterface::class));

        $factory = new VariableFactory();
        $var = $factory->factory('foo', 'bar', $varClass);

        assertInstanceOf(VariableInterface::class, $var);
        assertInstanceOf($varClass, $var);
    }
}
