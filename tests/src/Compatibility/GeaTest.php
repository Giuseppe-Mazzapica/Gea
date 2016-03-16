<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file incorporates work covered by the following copyright and
 * permission notices:
 *
 *   Dotenv is (c) 2013 Vance Lucas - vance@vancelucas.com - http://www.vancelucas.com
 *   Dotenv is released under BSD 3-Clause License
 */

namespace Gea\Tests\Compatibility;

use Gea\Gea;
use Gea\Tests\TestCase;

/**
 * This file runs most of the tests used by Dotenv, using same fixtures, to ensure results
 * are the same.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 * @coversNothing
 */
class GeaTest extends TestCase
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var \Gea\Gea
     */
    protected $gea;

    protected function setUp()
    {
        parent::setUp();
        $this->path = GEA_TESTS_FIXTURES_PATH;
    }

    protected function tearDown()
    {
        if ($this->gea) {
            $this->gea->flush(Gea::FLUSH_HARD);
            unset($this->gea);
        }
        parent::tearDown();
    }

    protected function buildGea($file = '.env')
    {
        $this->gea = Gea::instance($this->path, $file, Gea::VAR_NAMES_HOLD);

        return $this->gea;
    }

    public function testGeaLoadsEnvironmentVars()
    {
        $gea = $this->buildGea();
        $gea->load();
        assertSame('bar', getenv('FOO'));
        assertSame('baz', getenv('BAR'));
        assertSame('with spaces', getenv('SPACED'));
        assertSame('', getenv('NULL'));
    }

    public function testGeaLoadsCommentedEnvironmentVars()
    {
        $gea = $this->buildGea('commented.env');
        $gea->load();
        assertSame('bar', getenv('CFOO'));
        assertSame(false, getenv('CBAR'));
        assertSame(false, getenv('CZOO'));
        assertSame('with spaces', getenv('CSPACED'));
        assertSame('a value with a # character', getenv('CQUOTES'));
        assertSame('', getenv('CNULL'));
        assertSame(
            'a value with a # character & a quote " character inside quotes',
            getenv('CQUOTESWITHQUOTE')
        );
    }

    public function testGeaLoadsQuotedEnvironmentVars()
    {
        $gea = $this->buildGea('quoted.env');
        $gea->load();
        assertSame('bar', getenv('QFOO'));
        assertSame('baz', getenv('QBAR'));
        assertSame('with spaces', getenv('QSPACED'));
        assertSame('', getenv('QNULL'));
        assertSame('pgsql:host=localhost;dbname=test', getenv('QEQUALS'));
        assertSame(
            'test some escaped characters like a quote (") or maybe a backslash (\\)',
            getenv('QESCAPED')
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /spaces .+ quotes/
     */
    public function testLoadFailsWithSpacedValuesWithoutQuotes()
    {
        $gea = $this->buildGea('spaced-wrong.env');
        $gea->load();
    }

    public function testGeaLoadsExportedEnvironmentVars()
    {
        $gea = $this->buildGea('exported.env');
        $gea->load();
        assertSame('bar', getenv('EFOO'));
        assertSame('baz', getenv('EBAR'));
        assertSame('with spaces', getenv('ESPACED'));
        assertSame('', getenv('ENULL'));
    }

    public function testGeaLoadsIntoServerGlobal()
    {
        $gea = $this->buildGea();
        $gea->load();
        assertSame('bar', $_SERVER['FOO']);
        assertSame('baz', $_SERVER['BAR']);
        assertSame('with spaces', $_SERVER['SPACED']);
        assertSame('', $_SERVER['NULL']);
    }

    public function testGeaLoadsIntoEnvGlobal()
    {
        $gea = $this->buildGea();
        $gea->load();
        assertSame('bar', $_ENV['FOO']);
        assertSame('baz', $_ENV['BAR']);
        assertSame('with spaces', $_ENV['SPACED']);
        assertSame('', $_ENV['NULL']);
    }

    public function testGeaAllowsSpecialCharacters()
    {
        $gea = $this->buildGea('specialchars.env');
        $gea->load();
        assertSame('$a6^C7k%zs+e^.jvjXk', getenv('SPVAR1'));
        assertSame('?BUty3koaV3%GA*hMAwH}B', getenv('SPVAR2'));
        assertSame('jdgEB4{QgEC]HL))&GcXxokB+wqoN+j>xkV7K?m$r', getenv('SPVAR3'));
        assertSame('22222:22#2^{', getenv('SPVAR4'));
        assertSame(
            'test some escaped characters like a quote " or maybe a backslash \\',
            getenv('SPVAR5')
        );
    }

    public function testGeaLoadsNestedEnvironmentVars()
    {
        $gea = $this->buildGea('nested.env');
        $gea->load();
        assertSame('{$NVAR1} {$NVAR2}', $_ENV['NVAR3']); // not resolved
        assertSame('Hello World!', $_SERVER['NVAR4']);
        assertSame('$NVAR1 {NVAR2}', getenv('NVAR5')); // not resolved
    }

    public function testGeaTrimmedKeys()
    {
        $gea = $this->buildGea('quoted.env');
        $gea->load();
        $this->assertEquals('no space', getenv('QWHITESPACE'));
    }
}
