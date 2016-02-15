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

use Gea\Accessor\AccessorInterface;
use Gea\Parser\ParserInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
interface LoaderFactoryInterface
{
    const CONTRACT = LoaderInterface::class;

    /**
     * @param  \Gea\Parser\ParserInterface     $parser
     * @param  \Gea\Accessor\AccessorInterface $accessor
     * @return \Gea\Loader\LoaderInterface
     */
    public function factory(ParserInterface $parser, AccessorInterface $accessor);
}
