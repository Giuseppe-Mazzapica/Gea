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
final class CachedFilteredAccessor implements FilteredAccessorInterface
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
     * @var array
     */
    private $cache = [];

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
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $value = $this->accessor->read($name);

        if (array_key_exists($name, $this->filters)) {
            try {
                $value = @array_reduce(
                    $this->filters[$name],
                    function ($carry, FilterInterface $filter) {
                        return $filter->filter($carry);
                    },
                    $value
                );
            } catch (FilterException $e) {
                $message = str_replace(':name', $name, $e->getMessage());
                throw new FilterException($message, $e->getCode(), $e);
            }
        }

        $this->cache[$name] = $value;

        return $value;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function write($name, $value = null)
    {
        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        return $this->accessor->write($name, $value);
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function discard($var)
    {
        if (isset($this->cache[$var])) {
            unset($this->cache[$var]);
        }

        return $this->accessor->discard($var);
    }
}
