<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Filter;

/**
 * Implementation of filter factory that can instantiate the filters shipped with Gea and custom
 * filters that can be "added" to the factory.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class FilterFactory implements FilterFactoryInterface
{
    const CONTRACT = FilterInterface::class;

    /**
     * @var array
     */
    private static $defaultMap = [
        'array'    => ArrayFilter::class,
        'bool'     => BoolFilter::class,
        'callback' => CallbackFilter::class,
        'enum'     => EnumFilter::class,
        'choices'  => ChoicesFilter::class,
        'float'    => FloatFilter::class,
        'int'      => IntFilter::class,
        'object'   => ObjectFilter::class,
        'required' => RequiredFilter::class,
    ];

    /**
     * @var array
     */
    private $map;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->map = self::$defaultMap;
    }

    /**
     * @inheritdoc
     */
    public function factory($name, array $args = [])
    {
        if (! is_string($name)) {
            throw new \InvalidArgumentException('Filter name must be in a string.');
        }

        $name = strtolower($name);

        if (! array_key_exists($name, $this->map)) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid filter name.', $name));
        }

        $class = $this->map[$name];

        if (empty($args)) {
            return new $class();
        }

        $num = count($args);

        switch ($num) {
            case 1:
                return new $class($args[0]);
            case 2:
                return new $class($args[0], $args[1]);
            case 3:
                return new $class($args[0], $args[1], $args[2]);
            case 4:
                return new $class($args[0], $args[1], $args[2], $args[3]);
        }

        $ref = new \ReflectionClass($class);

        return $ref->newInstanceArgs($args);
    }

    /**
     * @param  string                    $name
     * @param  string                    $class
     * @return \Gea\Filter\FilterFactory
     */
    public function addFilter($name, $class)
    {
        if (! is_string($name)) {
            throw new \InvalidArgumentException('Filter name must be in a string');
        }

        $name = strtolower($name);
        if (array_key_exists($name, $this->map)) {
            throw new \RuntimeException(
                sprintf(
                    'Filter name "%s" can\'t be set because already assigned to %s',
                    $name,
                    $this->map[$name]
                )
            );
        }

        if (! is_string($class)) {
            throw new \InvalidArgumentException(
                sprintf('Filter class for filter "%s" must be in a string', $name)
            );
        }

        if (! class_exists($class)) {
            throw new \RuntimeException(
                sprintf('"%s" is not a class and can\'t be used for "%s" filter.', $class, $name)
            );
        }

        if (! is_subclass_of($class, self::CONTRACT)) {
            throw new \LogicException(
                sprintf(
                    '"%s" does not implement "%s" and can\'t be used for "%s" filter.',
                    $class,
                    self::CONTRACT,
                    $name
                )
            );
        }

        $this->map[$name] = $class;

        return $this;
    }
}
