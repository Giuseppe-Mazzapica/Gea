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

use Gea\Accessor\AccessorInterface;
use Gea\Loader\NestedAllowedLoader;
use Gea\Parser\ParserInterface;
use Gea\Tests\TestCase;
use Gea\Variable\VariableInterface;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class NestedAllowedLoaderTest extends TestCase
{
    private function cleanUp($vars)
    {
        foreach ($vars as $name) {
            putenv($name);
            unset($_ENV[$name]);
            unset($_SERVER[$name]);
        }
    }

    public function testLoadEmptyArrayWhenNothing()
    {
        /** @var \Gea\Parser\ParserInterface|\Mockery\MockInterface $parser */
        $parser = Mockery::mock(ParserInterface::class);
        $parser->shouldReceive('parse')->andReturn([]);

        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);

        $loader = new NestedAllowedLoader($parser, $accessor);

        assertSame([], $loader->load());
    }

    public function testLoadSimple()
    {
        $foo = \Mockery::mock(VariableInterface::class);
        $foo->shouldReceive('isNested')->andReturn(false);
        $foo->shouldReceive('isValid')->andReturn(true);
        $bar = clone $foo;
        $baz = clone $foo;
        $foo->shouldReceive('offsetGet')->with('name')->andReturn('foo');
        $foo->shouldReceive('offsetGet')->with('value')->andReturn('Foo!');
        $bar->shouldReceive('offsetGet')->with('name')->andReturn('bar');
        $bar->shouldReceive('offsetGet')->with('value')->andReturn('Bar!');
        $baz->shouldReceive('offsetGet')->with('name')->andReturn('baz');
        $baz->shouldReceive('offsetGet')->with('value')->andReturn('Baz!');

        /** @var \Gea\Parser\ParserInterface|\Mockery\MockInterface $parser */
        $parser = Mockery::mock('Gea\Parser\ParserInterface');
        $parser->shouldReceive('parse')->andReturn([$foo, $bar, $baz]);

        /** @var \Gea\Accessor\AccessorInterface|\Mockery\MockInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);
        $accessor->shouldReceive('write')->andReturnUsing(function ($key, $value) {
            putenv("{$key}={$value}");
        });

        $loader = new NestedAllowedLoader($parser, $accessor);

        assertSame(['foo', 'bar', 'baz'], $loader->load());
        assertSame('Foo!', getenv('foo'));
        assertSame('Bar!', getenv('bar'));
        assertSame('Baz!', getenv('baz'));

        $this->cleanUp(['foo', 'bar', 'baz']);
    }

    public function testLoadNested()
    {
        $fooBar = \Mockery::mock(VariableInterface::class);
        $fooBar->shouldReceive('isValid')->andReturn(true);

        $foo = clone $fooBar;

        $fooBar->shouldReceive('offsetGet')->with('name')->andReturn('foo_bar');
        $fooBar->shouldReceive('offsetGet')->with('value')->andReturn('${foo}/${bar}/${foo}/ok');
        $fooBar->shouldReceive('isNested')->andReturn(true);
        $fooBar->shouldReceive('offsetGet')->with('nested')->andReturn(['foo', 'bar']);

        $foo->shouldReceive('isNested')->andReturn(false);
        $bar = clone $foo;

        $foo->shouldReceive('offsetGet')->with('name')->andReturn('foo');
        $foo->shouldReceive('offsetGet')->with('value')->andReturn('Foo');

        $bar->shouldReceive('offsetGet')->with('name')->andReturn('bar');
        $bar->shouldReceive('offsetGet')->with('value')->andReturn('Bar');

        /** @var \Gea\Parser\ParserInterface|\Mockery\MockInterface $parser */
        $parser = Mockery::mock('Gea\Parser\ParserInterface');
        $parser->shouldReceive('parse')->andReturn([$foo, $bar, $fooBar]);

        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);
        $accessor->shouldReceive('write')->andReturnUsing(function ($key, $value) {
            putenv("{$key}={$value}");
        });
        $accessor->shouldReceive('read')->andReturnUsing(function ($key) {
            $v = getenv($key);

            return $v === false ? null : $v;
        });

        $loader = new NestedAllowedLoader($parser, $accessor);
        $loader->load();

        assertSame('Foo/Bar/Foo/ok', getenv('foo_bar'));

        $this->cleanUp(['foo', 'bar', 'foo_bar']);
    }

    public function testLoaded()
    {
        /** @var \Gea\Parser\ParserInterface|\Mockery\MockInterface $parser */
        $parser = Mockery::mock('Gea\Parser\ParserInterface');
        $parser->shouldReceive('parse')->andReturn([]);

        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);

        $loader = new NestedAllowedLoader($parser, $accessor);

        assertFalse($loader->loaded());

        $loader->load();

        assertTrue($loader->loaded());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /already loaded/
     */
    public function testLoadFailsIfLoaded()
    {
        /** @var \Gea\Parser\ParserInterface|\Mockery\MockInterface $parser */
        $parser = Mockery::mock('Gea\Parser\ParserInterface');
        $parser->shouldReceive('parse')->andReturn([]);

        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);

        $loader = new NestedAllowedLoader($parser, $accessor);
        $loader->load();
        $loader->load();
    }

    public function testLoadCanLoadAgainAfterFlush()
    {
        /** @var \Gea\Parser\ParserInterface|\Mockery\MockInterface $parser */
        $parser = Mockery::mock('Gea\Parser\ParserInterface');
        $parser->shouldReceive('parse')->andReturn([]);

        /** @var \Gea\Accessor\AccessorInterface $accessor */
        $accessor = Mockery::mock(AccessorInterface::class);

        $loader = new NestedAllowedLoader($parser, $accessor);
        $loader->load();
        $loader->flush();
        $loader->load();
    }
}
