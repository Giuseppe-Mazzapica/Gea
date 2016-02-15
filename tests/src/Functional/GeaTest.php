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
class GeaTest extends TestCase
{
    /**
     * @var string
     */
    protected $path;

    protected function setUp()
    {
        parent::setUp();
        $this->path = getenv('GEA_TESTS_FIXTURES_PATH');
    }

    protected function tearDown()
    {
        $this->cleanUp();
        parent::tearDown();
    }

    protected function cleanUp($vars = [])
    {
        $vars = array_merge($vars, ['FOO', 'BAR', 'SPACED', 'NULL']);

        array_walk($vars, function ($var) {
            putenv($var);
            unset($_SERVER[$var]);
            unset($_ENV[$var]);
        });
    }

    public function testGeaLoadsWriteDiscardFlushHoldNames()
    {
        $gea = Gea::instance($this->path, '.env', Gea::VAR_NAMES_HOLD);
        $names = $gea->load();

        assertSame('baz', getenv('BAR'));
        assertNull($gea->read('NEW_ONE'));
        assertSame(['FOO', 'BAR', 'SPACED', 'NULL'], array_values($gea->varNames()));
        assertSame(['FOO', 'BAR', 'SPACED', 'NULL'], array_values($names));

        $gea->write('NEW_ONE', 'New!');

        assertSame('New!', getenv('NEW_ONE'));
        assertSame('New!', $gea->read('NEW_ONE'));
        assertSame('New!', $gea['NEW_ONE']);
        assertSame(['FOO', 'BAR', 'SPACED', 'NULL', 'NEW_ONE'], array_values($gea->varNames()));

        $gea->discard('NEW_ONE', 'New!');
        $gea->discard('BAR', 'New!');

        assertNull($gea->read('BAR'));
        assertFalse(getenv('BAR'));
        assertNull($gea->read('NEW_ONE'));
        assertSame(['FOO', 'SPACED', 'NULL'], array_values($gea->varNames()));

        $gea->flush(Gea::FLUSH_HARD);

        assertFalse(isset($_ENV['FOO']));
        assertFalse(isset($_ENV['SPACED']));
        assertFalse(isset($_ENV['NULL']));
        assertFalse(isset($_SERVER['FOO']));
        assertFalse(isset($_SERVER['SPACED']));
        assertFalse(isset($_SERVER['NULL']));
        assertFalse(getenv('FOO'));
        assertFalse(getenv('SPACED'));
        assertFalse(getenv('NULL'));
        assertNull($gea->read('FOO'));
        assertNull($gea->read('SPACED'));
        assertNull($gea->read('NULL'));

        assertSame([], $gea->varNames());
    }

    /**
     * @expectedException \Gea\Exception\ImmutableWriteException
     */
    public function testImmutabilityOnWrite()
    {
        $gea = Gea::instance($this->path);
        $gea->load();
        $gea->write('FOO', 'Meh'); // is already set
    }

    /**
     * @expectedException \Gea\Exception\ImmutableWriteException
     */
    public function testImmutabilityOnConsecutiveWrite()
    {
        $gea = Gea::instance($this->path);
        $gea->load();
        try {
            $gea->write('NEW', 'new');
            $gea->write('NEW', 'new2');
        } catch (\Exception $e) {
            $this->cleanUp(['NEW']);
            throw $e;
        }
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testReadOnlyOnWrite()
    {
        $gea = Gea::instance($this->path, '.env', Gea::READ_ONLY);
        $gea->load();
        $gea->write('NEW', 'Meh');
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testReadOnlyOnDiscard()
    {
        $gea = Gea::instance($this->path, '.env', Gea::READ_ONLY);
        $gea->load();
        $gea->discard('FOO');
    }

    /**
     * @expectedException \Gea\Exception\ReadOnlyWriteException
     */
    public function testReadOnlyOnFlush()
    {
        $gea = Gea::instance($this->path, '.env', Gea::READ_ONLY);
        $gea->load();
        $gea->flush();
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testCheckRequiredOnLoad()
    {
        $gea = Gea::instance($this->path);
        $gea->addFilter('MEH', 'required');
        $gea->load();
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testGeaCheckRequiredOnRead()
    {
        $gea = Gea::instance($this->path);
        $gea->addFilter('MEH', 'required');
        $gea->read('FOO');
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     * @expectedExceptionMessageRegExp /FOO .+ not allowed/
     */
    public function testGeaCheckEnumOnLoad()
    {
        $gea = Gea::instance($this->path);
        $gea->addFilter('FOO', ['enum' => ['a', 'b', 'c']]);
        $gea->load();
    }

    public function testGeaApplyFiltersOnRead()
    {
        $gea = Gea::instance($this->path);

        $gea->addFilter('FOO', [
            'required',
            'enum'     => ['bar'],
            'callback' => ['strtoupper'],
            new CallbackFilter(function ($foo) {
                return ['value' => $foo.'!'];
            }),
            'object'   => [\ArrayObject::class]
        ]);

        $foo = $gea->read('FOO');

        assertInstanceOf(\ArrayObject::class, $foo);
        assertSame('BAR!', $foo['value']);
    }
}
