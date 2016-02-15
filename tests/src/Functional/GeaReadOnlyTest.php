<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Functional;

use Gea\Filter\CallbackFilter;
use Gea\Filter\EnumFilter;
use Gea\Gea;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 * @coversNothing
 */
class GeaReadOnlyTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        putenv('FOO=Foo!');
        putenv('BAR=Bar!');
        putenv('BAZ=Baz!');
    }

    protected function tearDown()
    {
        putenv('FOO');
        putenv('BAR');
        putenv('BAZ');
        parent::tearDown();
    }

    public function testReadOnlyInstanceRead()
    {
        $gea = Gea::readOnlyInstance();

        assertSame('Foo!', $gea['FOO']);
        assertSame('Bar!', $gea->read('BAR'));
        assertSame('Baz!', $gea->read('BAZ'));
        assertNull($gea->read('NOPE'));
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testReadOnlyInstanceCantWrite()
    {
        $gea = Gea::readOnlyInstance();
        $gea->write('NOPE', 'Nope!');
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testReadOnlyInstanceCantFlush()
    {
        $gea = Gea::readOnlyInstance();
        $gea->flush();
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testReadOnlyInstanceCheckRequiredOnLoad()
    {
        $gea = Gea::readOnlyInstance();
        $gea->addFilter('MEH', 'required');
        $gea->load();
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testReadOnlyInstanceCheckRequiredOnRead()
    {
        $gea = Gea::readOnlyInstance();
        $gea->addFilter('MEH', 'required');
        $gea->read('FOO');
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     * @expectedExceptionMessageRegExp /FOO .+ not allowed/
     */
    public function testReadOnlyInstanceCheckEnumOnLoad()
    {
        $gea = Gea::readOnlyInstance();
        $gea->addFilter('FOO', new EnumFilter('a', 'b'));
        $gea->load();
    }

    public function testReadOnlyInstanceApplyFiltersOnRead()
    {
        $gea = Gea::readOnlyInstance();

        $gea->addFilter('FOO', [
            'required',
            'enum'     => ['Foo!'],
            'callback' => ['strtolower'],
            new CallbackFilter(function ($foo) {
                return ['value' => $foo];
            }),
            'object'   => [\ArrayObject::class]
        ]);

        $foo = $gea->read('FOO');

        assertInstanceOf(\ArrayObject::class, $foo);
        assertSame('foo!', $foo['value']);
    }
}
