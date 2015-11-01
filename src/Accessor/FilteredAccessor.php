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

use Gea\Exception\FilterException;
use Gea\Filter\FilterInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class FilteredAccessor implements FilteredAccessorInterface
{
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var \Gea\Accessor\AccessorInterface
     */
    private $accessor;

    /**
     * @param \Gea\Accessor\AccessorInterface $accessor
     */
    public function __construct(AccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * @inheritdoc
     */
    public function addFilter($name, FilterInterface $filter)
    {
        if (! isset($this->filters[$name])) {
            $this->filters[$name] = [];
        }

        $this->filters[$name][] = $filter;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function read($name)
    {
        $value = $this->accessor->read($name);

        if (array_key_exists($name, $this->filters)) {
            try {
                $value = @array_reduce($this->filters[$name],
                    function ($carry, FilterInterface $filter) {
                        return $filter->filter($carry);
                    }, $value);
            } catch (FilterException $e) {
                $message = str_replace(':name', $name, $e->getMessage());
                throw new FilterException($message, $e->getCode(), $e);
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function write($name, $value = null)
    {
        return $this->accessor->write($name, $value);
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function discard($var)
    {
        return $this->accessor->discard($var);
    }
}
