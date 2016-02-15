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
    /**
     * Do nothing. Variables are assumed to set in any way.
     *
     * @return array
     */
    public function load()
    {
        return [];
    }

    /**
     * Since this class do nothing, we always return true, to avoid load() is called again and again.
     *
     * @return bool
     */
    public function loaded()
    {
        return true;
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
