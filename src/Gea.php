<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea;

use Gea\Accessor\CompositeAccessor;
use Gea\Accessor\AccessorInterface;
use Gea\Accessor\CachedFilteredAccessor;
use Gea\Accessor\CompositeReadOnlyAccessor;
use Gea\Accessor\FilteredAccessorInterface;
use Gea\Filter\FilterFactory;
use Gea\Filter\FilterFactoryInterface;
use Gea\Loader\DummyLoader;
use Gea\Loader\LoaderFactory;
use Gea\Loader\LoaderFactoryInterface;
use Gea\Loader\LoaderInterface;
use Gea\Parser\FileParser;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class Gea implements \ArrayAccess
{

    const VAR_NAMES_HOLD     = 1;
    const VAR_NAMES_NOT_HOLD = 2;
    const NO_LOADER          = 128;
    const READ_ONLY          = 256;
    const FLUSH_SOFT         = 0;
    const FLUSH_HARD         = 1;

    /**
     * @var \Gea\Accessor\FilteredAccessorInterface
     */
    protected $accessor;

    /**
     * @var \Gea\Loader\LoaderInterface
     */
    protected $loader;

    /**
     * @var \Gea\Filter\FilterFactory
     */
    protected $filterFactory;

    /**
     * All the names of loaded variables, when hold names is enabled.
     *
     * @var array
     */
    protected $varNames = [];

    /**
     * Bitmask of configuration flags.
     *
     * @var int
     */
    protected $flags;

    /**
     * A named constructor.
     *
     * It is the simplest way to get an instance of Gea using defaults, and starting from basic
     * configuration.
     *
     * @param  string                             $dir
     * @param  string                             $filename
     * @param  int                                $flags
     * @param  \Gea\Accessor\AccessorInterface    $accessor
     * @param  \Gea\Filter\FilterFactoryInterface $filterFactory
     * @param  \Gea\Loader\LoaderFactoryInterface $loaderFactory
     * @return static
     */
    public function instance(
        $dir,
        $filename = '.env',
        $flags = self::VAR_NAMES_NOT_HOLD,
        AccessorInterface $accessor = null,
        FilterFactoryInterface $filterFactory = null,
        LoaderFactoryInterface $loaderFactory = null
    ) {
        $dir = is_string($dir) ? trim($dir, '/\\') : '';
        $filename = is_string($filename) ? trim($filename, '/\\') : '';
        $realpath = $dir && $filename ? realpath("{$dir}/{$filename}") : false;
        if ( ! $realpath) {
            throw new \InvalidArgumentException(
                sprintf('Please provide a valid path for environment file.', $dir)
            );
        }
        if ($flags & self::READ_ONLY && is_null($accessor)) {
            $accessor = new CompositeReadOnlyAccessor();
        } elseif(is_null($accessor)) {
            $accessor = new CompositeAccessor();
        }

        $accessor instanceof FilteredAccessorInterface or $accessor = new CachedFilteredAccessor($accessor);

        is_null($filterFactory) and $filterFactory = new FilterFactory();
        is_null($loaderFactory) and $loaderFactory = new LoaderFactory();

        $loader = $loaderFactory->factory(new FileParser($realpath), $accessor);

        return new static($accessor, $loader, $flags, $filterFactory);
    }

    /**
     * A named constructor for instances that does not load variables.
     *
     * In production you can load env variables in different ways than env file.
     * The instance of Gea obtained with this method, allow to access values using all the sugars
     * Gea provides (like filters), but without loading any file, or even expect env file is there.
     * Applications can easily rely on a Gea instance everywhere and then use a "fully featured"
     * instance on development and a read-only instance on production.
     *
     * @param  \Gea\Accessor\AccessorInterface    $accessor
     * @param  \Gea\Filter\FilterFactoryInterface $filterFactory
     * @param int                                 $flags
     * @return static
     */
    public function noLoaderInstance(
        AccessorInterface $accessor = null,
        FilterFactoryInterface $filterFactory = null,
        $flags = self::VAR_NAMES_NOT_HOLD
    ) {
        if ($flags & self::READ_ONLY && is_null($accessor)) {
            $accessor = new CompositeReadOnlyAccessor();
        } elseif(is_null($accessor)) {
            $accessor = new CompositeAccessor();
        }

        $accessor instanceof FilteredAccessorInterface or $accessor = new CachedFilteredAccessor($accessor);

        is_null($filterFactory) and $filterFactory = new FilterFactory();

        $flags |= self::NO_LOADER;

        return new static($accessor, new DummyLoader(), $flags, $filterFactory);
    }

    /**
     * A named constructor for instances that does not load, write, and discard variables.
     *
     * Similar to noLoaderInstance() method, the instance returned by this method does not load any
     * variable, but also ensure no variable can be written, edited or discarded.
     *
     * @param  \Gea\Accessor\AccessorInterface    $accessor
     * @param  \Gea\Filter\FilterFactoryInterface $filterFactory
     * @param int                                 $flags
     * @return static
     */
    public function readOnlyInstance(
        AccessorInterface $accessor = null,
        FilterFactoryInterface $filterFactory = null,
        $flags = self::VAR_NAMES_NOT_HOLD
    ) {
        is_null($accessor) and $accessor = new CompositeReadOnlyAccessor();
        $accessor instanceof FilteredAccessorInterface or $accessor = new CachedFilteredAccessor($accessor);

        is_null($filterFactory) and $filterFactory = new FilterFactory();

        $flags |= self::NO_LOADER;
        $flags |= self::READ_ONLY;

        return new static($accessor, new DummyLoader(), $flags, $filterFactory);
    }

    /**
     * Constructor.
     *
     * @param \Gea\Accessor\FilteredAccessorInterface $accessor
     * @param \Gea\Loader\LoaderInterface             $loader
     * @param int                                     $flags
     * @param \Gea\Filter\FilterFactoryInterface      $filterFactory
     */
    public function __construct(
        FilteredAccessorInterface $accessor,
        LoaderInterface $loader,
        $flags,
        FilterFactoryInterface $filterFactory
    ) {
        $this->accessor = $accessor;
        $this->loader = $loader;
        $this->flags = is_int($flags) ? $flags : 0;
        $this->filterFactory = $filterFactory;
    }

    /**
     * Attach one or more filters to a variable.
     *
     * @param  string       $name
     * @param  string|array $filter
     * @return static
     */
    public function addFilter($name, $filter)
    {
        if ($this->loader->loaded() && ! ($this->flags & self::NO_LOADER)) {
            throw new \BadMethodCallException('Filters can be added only before loading value.');
        }

        $badFilter = 'Filter names must be in a string or an array of strings.';

        if ( ! is_string($filter) && ! is_array($filter)) {
            throw new \InvalidArgumentException($badFilter);
        }

        $toRun = [];
        $filter = (array) $filter;

        array_walk($filter, function ($args, $key, $name) use (&$toRun, $badFilter) {
            if ( ! is_string($args) && ! (is_array($args) && is_string($key))) {
                throw new \InvalidArgumentException($badFilter);
            }
            $filterName = is_string($args) ? $args : $key;
            $filterArgs = is_string($args) ? [] : $args;
            $toRun = $this->handleFilter($filterName, $filterArgs, $name, $toRun);
        }, $name);

        // apply non-lazy filters immediately
        @array_map(array_unique($toRun), [$this, 'read']);

        return $this;
    }

    /**
     * Loads the variables.
     *
     * @return array
     */
    public function load()
    {
        $varNames = $this->loader->load();
        ($this->flags & self::VAR_NAMES_HOLD) and $this->varNames = $varNames;

        return $varNames;
    }

    /**
     * Return the names of the loaded variables if Gea was instructed to store them, otherwise
     * throws an Exception.
     *
     * @return array
     */
    public function varNames()
    {
        if ( ! ($this->flags & self::VAR_NAMES_HOLD)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Variable names can be accessed only when VAR_NAMES_HOLD flag is true.',
                    __CLASS__
                )
            );
        }

        if (!$this->loader->loaded()) {
            throw new \BadMethodCallException(
                'Variable names can be retrieved only after variables are loaded.'
            );
        }

        return $this->varNames;
    }

    /**
     * Flush the instance (allowing to load another file), optionally discarding a set of variables
     * (allowing to change their value).
     *
     * @param  int   $flags
     * @param  array $varNames
     * @return static
     */
    public function flush($flags = self::FLUSH_SOFT, array $varNames = [])
    {
        $this->bailIfReadOnly(__METHOD__);
        $this->loader->flush();
        if ($flags && self::FLUSH_HARD) {
            $toFlush = $varNames ? $varNames : $this->varNames;
            $toFlush and array_walk($toFlush, [$this->accessor, 'discard']);
        }
        $this->varNames = [];

        return $this;
    }

    /**
     * Get the value of an environment variable.
     *
     * Apply any register filter to the raw value stored in the env file.
     *
     * @param  string $name
     * @return string
     */
    public function read($name)
    {
        $this->loader->loaded() or $this->loader->load();

        return $this->accessor->read($name);
    }

    /**
     * Set the value for an environment variable.
     *
     * Variable already set can't be overwritten, if they are not discarded before that.
     *
     * @param  string      $name
     * @param  string|null $value
     * @return string
     */
    public function write($name, $value = null)
    {
        $this->bailIfReadOnly(__METHOD__);

        if (array_key_exists($name, $this->varNames)) {
            throw new \RuntimeException(
                sprintf(
                    'Variable %s can\'t be overwritten. '
                    .'You need to either discard or hard flush vars to change their value.',
                    $name
                )
            );
        }

        $name = $this->accessor->write($name, $value);

        if ($this->flags & self::VAR_NAMES_HOLD) {
            $this->varNames = array_merge($this->varNames, [$name]);
        }

        return $name;
    }

    /**
     * Discard an environment variable, setting it's value to null and allowing to overwrite it.
     *
     * @param  string $name
     * @return mixed  The old value of the value
     */
    public function discard($name)
    {
        $this->bailIfReadOnly(__METHOD__);

        $now = $this->read($name);
        is_null($now) or $this->accessor->discard($name);

        return $now;
    }

    /**
     * @param  string $id
     * @param  array  $args
     * @param  string $name
     * @param  array  $toRun
     * @return array
     */
    private function handleFilter($id, array $args, $name, array $toRun = [])
    {
        $filter = $this->filterFactory->factory($id, $args);
        $this->accessor->addFilter($id, $filter);
        $filter->isLazy() or $toRun[] = $name;

        return $toRun;
    }

    /**
     * @param $method
     */
    private function bailIfReadOnly($method)
    {
        if ($this->flags & self::READ_ONLY) {
            throw new \BadMethodCallException(
                sprintf(
                    'Impossible to run %s because %s is in read-only mode.',
                    $method,
                    __CLASS__
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return ! is_null($this->read($offset));
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->read($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->write($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->discard($offset);
    }
}
