<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Loader;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class DummyLoader implements LoaderInterface
{
    private $loaded = false;

    /**
     * Do nothing. Variables are assumed to set in any way.
     *
     * @return array
     */
    public function load()
    {
        $this->loaded or $this->loaded = true;

        return [];
    }

    /**
     * Since this class do nothing, just return true after load() has been called once.
     *
     * @return bool
     */
    public function loaded()
    {
        return $this->loaded;
    }

    /**
     * Do nothing because data are not set via this loader, so they can't be flushed.
     * Don't throw any exception, because mainly intended to be used in production environments.
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function flush()
    {
    }
}
