<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit\Loader;

use Gea\Loader\DummyLoader;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class DummyLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new DummyLoader();
        assertSame([], $loader->load());
    }

    public function testLoaded()
    {
        $loader = new DummyLoader();
        assertFalse($loader->loaded());
        $loader->load();
        assertTrue($loader->loaded());
    }
}
