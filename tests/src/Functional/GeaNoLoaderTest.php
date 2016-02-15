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
use Gea\Gea;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 * @coversNothing
 */
class GeaNoLoaderTest extends TestCase
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

    public function testNoLoaderInstanceReadAndWrite()
    {
        $gea = Gea::noLoaderInstance();

        assertSame('Foo!', $gea['FOO']);
        assertSame('Bar!', $gea->read('BAR'));
        assertSame('Baz!', $gea->read('BAZ'));
        assertNull($gea->read('NOPE'));

        $gea->write('NOPE', 'Yup!');

        assertSame('Yup!', $gea->read('NOPE'));

        $gea->discard('BAR');
        $gea->discard('NOPE');

        assertNull($gea->read('BAR'));
        assertNull($gea->read('NOPE'));
    }

    /**
     * @expectedException \Gea\Exception\ImmutableWriteException
     */
    public function testNoLoaderImmutable()
    {
        $gea = Gea::noLoaderInstance();
        $gea->write('FOO', 'meh!');
    }

    public function testNoLoaderFlush()
    {
        $gea = Gea::noLoaderInstance();
        $gea->flush(Gea::FLUSH_HARD, ['FOO']);

        assertNull($gea->read('FOO'));
        assertSame('Bar!', $gea->read('BAR'));
        assertSame('Baz!', $gea->read('BAZ'));

        $gea->write('FOO', 'New Foo!');

        assertSame('New Foo!', $gea->read('FOO'));
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testNoLoaderCheckRequiredOnLoad()
    {
        $gea = Gea::noLoaderInstance();
        $gea->addFilter('MEH', 'required');
        $gea->load();
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testNoLoaderCheckRequiredOnRead()
    {
        $gea = Gea::noLoaderInstance();
        $gea->addFilter('MEH', 'required');
        $gea->read('FOO');
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     * @expectedExceptionMessageRegExp /FOO .+ not allowed/
     */
    public function testNoLoaderCheckEnumOnLoad()
    {
        $gea = Gea::noLoaderInstance();
        $gea->addFilter('FOO', ['enum' => ['a', 'b', 'c']]);
        $gea->load();
    }

    public function testNoLoaderApplyFiltersOnRead()
    {
        $gea = Gea::noLoaderInstance();

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
