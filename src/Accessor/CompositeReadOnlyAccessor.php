<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Accessor;

use Gea\Exception\ReadOnlyWriteException;

/**
 * The read-only version of CompositeAccessor.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class CompositeReadOnlyAccessor implements AccessorInterface
{
    /**
     * @var \Gea\Accessor\CompositeAccessor
     */
    private $accessor;

    /**
     * CompositeReadOnlyAccessor constructor.
     */
    public function __construct()
    {
        $this->accessor = new CompositeAccessor();
    }

    /**
     * @inheritdoc
     */
    public function read($name)
    {
        return $this->accessor->read($name);
    }

    /**
     * Disabled because read-only
     *
     * @param  string      $name
     * @param  string|null $value
     * @return void
     */
    public function write($name, $value = null)
    {
        throw ReadOnlyWriteException::forVarName($name, 'write');
    }

    /**
     * Disabled because read-only
     *
     * @param  string $name
     * @return void
     */
    public function discard($name)
    {
        throw ReadOnlyWriteException::forVarName($name, 'discard');
    }
}
