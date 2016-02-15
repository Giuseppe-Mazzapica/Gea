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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class VariableFactory implements VariableFactoryInterface
{
    /**
     * @param  string                          $name
     * @param  string                          $value
     * @param  string|null                     $class
     * @return \Gea\Variable\VariableInterface
     */
    public function factory($name, $value, $class = null)
    {
        if (! is_string($class) || ! is_subclass_of($class, self::CONTRACT, true)) {
            $class = Variable::class;
        }

        if (! is_string($name) || trim($name) === '') {
            throw new \InvalidArgumentException('Variable name must be a non-empty string.');
        }

        $data = [
            'name'   => trim($name),
            'value'  => $value,
            'nested' => [],
        ];

        if (is_string($value) && strpos($value, '${') !== false) {
            $matches = [];
            if (preg_match_all('/\${([a-zA-Z0-9_]+)}/', $value, $matches) > 0) {
                $data['nested'] = array_unique($matches[1]);
            }
        }

        return new $class($data);
    }
}
