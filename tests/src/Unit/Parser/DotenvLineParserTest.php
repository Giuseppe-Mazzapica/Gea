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

use Gea\Parser\DotenvLineParser;
use Gea\Tests\TestCase;
use Gea\Variable\Variable;
use Gea\Variable\VariableFactoryInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class DotenvLineParserTest extends TestCase
{
    public function testParseLineEmptyLine()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('');

        assertInstanceOf(Variable::class, $var);
        assertFalse($var->isValid());
    }

    public function testParseLineSpaces()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('         ');

        assertInstanceOf(Variable::class, $var);
        assertFalse($var->isValid());
    }

    public function testParseLineSpacesAndEqual()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('  =  ');

        assertInstanceOf(Variable::class, $var);
        assertFalse($var->isValid());
    }

    public function testParseLineNoName()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine(' =BAR');

        assertInstanceOf(Variable::class, $var);
        assertFalse($var->isValid());
    }

    public function testParseLineCommentLine()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('#FOO=BAR');

        assertInstanceOf(Variable::class, $var);
        assertFalse($var->isValid());
    }

    public function testParseLineInvalidLine()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('FOO>BAR');

        assertInstanceOf(Variable::class, $var);
        assertFalse($var->isValid());
    }

    public function testParseLineExportName()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('export FOO=BAR');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineQuotedName()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('"FOO"=BAR');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineExportAndQuotedName()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('export "FOO"=BAR');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineQuotedValue()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('FOO="BAR"');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineQuotedValueWithSpaces()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('FOO="BAR BAZ"');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR BAZ', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineQuotedNameAndValue()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('"FOO"="BAR BAZ"');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR BAZ', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineQuotedValueDiscardAnythingAfterQuote()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('FOO="BAR" BAZ');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineWithComment()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('FOO=BAR #Here I set FOO=BAR');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR', $var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineNoValue()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine('FOO=');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertNull($var['value']);
        assertFalse($var->isNested());
    }

    public function testParseLineWithQuotesAndExportAndComment()
    {
        $parser = new DotenvLineParser();
        $var = $parser->parseLine(' "FOO"="BAR BAZ" # Here I set FOO=BAR ');

        assertInstanceOf(Variable::class, $var);
        assertTrue($var->isValid());
        assertSame('FOO', $var['name']);
        assertSame('BAR BAZ', $var['value']);
        assertFalse($var->isNested());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /spaces .+ quotes/
     */
    public function testParseLineFailsForUnquotedValueWithSpaces()
    {
        $parser = new DotenvLineParser();
        $parser->parseLine('FOO=BAR BAZ');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /spaces .+ quotes/
     */
    public function testParseLineFailsForQuotedNameUnquotedValueWithSpaces()
    {
        $parser = new DotenvLineParser();
        $parser->parseLine('"FOO"=BAR BAZ');
    }

    public function testParseLineCustomVarFactory()
    {
        $factory = \Mockery::mock(VariableFactoryInterface::class);
        $factory
            ->shouldReceive('factory')
            ->with('FOO', 'BAR')
            ->andReturn(new Variable(['name' => 'MEH', 'value' => 'MEH']));

        $parser = new DotenvLineParser($factory);
        $var = $parser->parseLine('FOO=BAR');
        assertTrue($var->isValid());
        assertSame('MEH', $var['name']);
        assertSame('MEH', $var['value']);
        assertFalse($var->isNested());
    }
}
