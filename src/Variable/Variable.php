<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Variable;

use InvalidArgumentException;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class Variable implements VariableInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->offsetExists('name') && $this->offsetExists('value');
    }

    /**
     * @return bool
     */
    public function isNested()
    {
        return $this->offsetExists('nested') && is_array($this['nested']) && ! empty($this['nested']);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return is_string($offset) && array_key_exists($offset, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            throw new InvalidArgumentException(sprintf('Invalid offset for %s', __CLASS__));
        }

        return $this->data[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException(sprintf('%s is immutable.', __CLASS__));
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new LogicException(sprintf('%s is immutable.', __CLASS__));
    }
}
