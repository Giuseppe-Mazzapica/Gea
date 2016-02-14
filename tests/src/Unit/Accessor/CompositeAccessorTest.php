<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Accessor;

use Gea\Accessor\CompositeAccessor;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class AccessorTest extends TestCase
{
    private function cleanUp($vars)
    {
        foreach ($vars as $name) {
            putenv($name);
            unset($_ENV[$name]);
            unset($_SERVER[$name]);
        }
    }

    public function testRead()
    {
        $_ENV['FOO'] = 'FOO!';
        $_SERVER['BAR'] = 'BAR!';
        putenv('BAZ=BAZ!');

        $accessor = new CompositeAccessor();

        assertSame('FOO!', $accessor->read('FOO'));
        assertSame('BAR!', $accessor->read('BAR'));
        assertSame('BAZ!', $accessor->read('BAZ'));
        assertNull($accessor->read('MEH'));

        $this->cleanUp(['FOO', 'BAR', 'BAZ']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWriteImmutable()
    {
        $accessor = new CompositeAccessor();

        $_ENV['FOO'] = 'FOO!';
        try {
            $accessor->write('FOO', 'meh');
        } catch (\Exception $e) {
            $this->cleanUp(['FOO']);
            throw $e;
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWriteImmutableNoConsecutive()
    {
        $accessor = new CompositeAccessor();

        try {
            $accessor->write('FOO', 'one');
            $accessor->write('FOO', 'two');
        } catch (\Exception $e) {
            $this->cleanUp(['FOO']);
            throw $e;
        }
    }

    public function testWrite()
    {
        $accessor = new CompositeAccessor();

        $foo = $accessor->write('FOO', 'FOO!');
        $bar = $accessor->write('BAR', 'BAR!');
        $baz = $accessor->write('BAZ', 'BAZ!');

        assertSame('FOO!', $accessor->read('FOO'));
        assertSame('BAR!', $accessor->read('BAR'));
        assertSame('BAZ!', $accessor->read('BAZ'));

        assertSame('FOO', $foo);
        assertSame('BAR', $bar);
        assertSame('BAZ', $baz);

        $this->cleanUp(['FOO', 'BAR', 'BAZ']);
    }

    public function testDiscard()
    {
        $accessor = new CompositeAccessor();

        $accessor->write('FOO', 'FOO!');
        assertSame('FOO!', $accessor->read('FOO'));
        $accessor->discard('FOO');
        assertNull($accessor->read('FOO'));
        $accessor->write('FOO', 'FOO!');
        assertSame('FOO!', $accessor->read('FOO'));

        $this->cleanUp(['FOO']);
    }
}
