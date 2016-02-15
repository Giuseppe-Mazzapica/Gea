<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit\Parser;

use Gea\Parser\StringParser;
use Gea\Parser\LineParserInterface;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class StringParserTest extends TestCase
{
    public function testParseEmptyIfContentIsNotString()
    {
        $parser = new StringParser(true);

        assertSame([], $parser->parse());
    }

    public function testParseEmptyIfContentIsEmptyString()
    {
        $parser = new StringParser('     ');

        assertSame([], $parser->parse());
    }

    public function testParseWindowsLines()
    {
        $lineParser = \Mockery::mock(LineParserInterface::class);
        $lineParser
            ->shouldReceive('parseLine')
            ->with(\Mockery::type('string'))
            ->andReturnUsing(function ($line) {
                return trim($line);
            });

        $le = "\r\n";
        $content = "{$le}FOO=bar{$le}BAR=baz{$le}";

        $parser = new StringParser($content, $lineParser);

        $expected = [
            'FOO=bar',
            'BAR=baz',
        ];

        assertSame($expected, array_values(array_filter($parser->parse())));
    }

    public function testParseLinuxLines()
    {
        $lineParser = \Mockery::mock(LineParserInterface::class);
        $lineParser
            ->shouldReceive('parseLine')
            ->with(\Mockery::type('string'))
            ->andReturnUsing(function ($line) {
                return trim($line);
            });

        $le = "\n";
        $content = "{$le}FOO=bar{$le}BAR=baz{$le}";

        $parser = new StringParser($content, $lineParser);

        $expected = [
            'FOO=bar',
            'BAR=baz',
        ];

        assertSame($expected, array_values(array_filter($parser->parse())));
    }

    public function testParseMachineLines()
    {
        $lineParser = \Mockery::mock(LineParserInterface::class);
        $lineParser
            ->shouldReceive('parseLine')
            ->with(\Mockery::type('string'))
            ->andReturnUsing(function ($line) {
                return trim($line);
            });

        $le = PHP_EOL;
        $content = "{$le}FOO=bar{$le}BAR=baz{$le}";

        $parser = new StringParser($content, $lineParser);

        $expected = [
            'FOO=bar',
            'BAR=baz',
        ];

        assertSame($expected, array_values(array_filter($parser->parse())));
    }
}
