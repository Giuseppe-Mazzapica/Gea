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

use Gea\Accessor\CompositeReadOnlyAccessor;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class CompositeReadOnlyAccessorTest extends TestCase
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

        $accessor = new CompositeReadOnlyAccessor();

        assertSame('FOO!', $accessor->read('FOO'));
        assertSame('BAR!', $accessor->read('BAR'));
        assertSame('BAZ!', $accessor->read('BAZ'));
        assertNull($accessor->read('MEH'));

        $this->cleanUp(['FOO', 'BAR', 'BAZ']);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /read-only/
     */
    public function testWriteDisabled()
    {
        $accessor = new CompositeReadOnlyAccessor();
        $accessor->write('FOO', 'meh');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /read-only/
     */
    public function testDiscardDisabled()
    {
        putenv('FOO=BAR');
        $accessor = new CompositeReadOnlyAccessor();
        try {
            $accessor->discard('FOO');
        } catch (\Exception $e) {
            $this->cleanUp(['FOO']);
            throw $e;
        }
    }
}
