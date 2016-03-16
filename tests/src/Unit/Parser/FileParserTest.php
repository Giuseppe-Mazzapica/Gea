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

use Gea\Parser\FileParser;
use Gea\Parser\LineParserInterface;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class FileParserTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /invalid/
     */
    public function testParseFailsIfFilepathIsNotString()
    {
        $parser = new FileParser(true);
        $parser->parse();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /not found/
     */
    public function testParseFailsIfFilepathIsNotFile()
    {
        $parser = new FileParser('meh');
        $parser->parse();
    }

    public function testParse()
    {
        $lineParser = \Mockery::mock(LineParserInterface::class);
        $lineParser
            ->shouldReceive('parseLine')
            ->with(\Mockery::type('string'))
            ->andReturnUsing(function ($line) {
                return trim($line);
            });

        $parser = new FileParser(GEA_TESTS_FIXTURES_PATH.'/.env', $lineParser);
        $parsed = $parser->parse();

        $expected = [
            'FOO=bar',
            'BAR=baz',
            'SPACED="with spaces"',
            'NULL=',
        ];

        assertSame($expected, array_values(array_filter($parsed)));
    }
}
