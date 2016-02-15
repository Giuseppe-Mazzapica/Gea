<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file incorporates work covered by the following copyright and
 * permission notices:
 *
 *   Dotenv is (c) 2013 Vance Lucas - vance@vancelucas.com - http://www.vancelucas.com
 *   Dotenv is released under BSD 3-Clause License
 */

namespace Gea\Parser;

use Gea\Variable\Variable;
use Gea\Variable\VariableFactory;
use Gea\Variable\VariableFactoryInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class DotenvLineParser implements LineParserInterface
{
    /**
     * @var \Gea\Variable\VariableFactoryInterface
     */
    private $varFactory;

    /**
     * DotenvLineParser constructor.
     *
     * @param \Gea\Variable\VariableFactoryInterface|null $varFactory
     */
    public function __construct(VariableFactoryInterface $varFactory = null)
    {
        $this->varFactory = $varFactory ?: new VariableFactory();
    }

    /**
     * @param  string                      $lineString
     * @return \Gea\Variable\Variable|void
     */
    public function parseLine($lineString = '')
    {
        $value = null;
        $lineString = is_string($lineString) ? trim($lineString) : '';

        if (
            empty($lineString)
            || (strpos($lineString, '#') === 0)
            || (strpos($lineString, '=') === false)
        ) {
            return new Variable([]); // invalid variable
        }

        list($rawName, $rawValue) = array_map('trim', explode('=', $lineString, 2));

        $name = trim(str_replace(['export ', '\'', '"'], '', $rawName));

        if (empty($name)) {
            return new Variable([]); // invalid variable
        }

        $value = empty($rawValue) ? '' : $this->sanitiseValue($rawValue);

        return $this->varFactory->factory($name, $value);
    }

    /**
     * Strips quotes from the environment variable value.
     *
     * @param  string $value
     * @return string
     */
    private function sanitiseValue($value)
    {
        return strpbrk($value[0], '"\'') !== false
            ? $this->sanitiseQuotedValue($value, $value[0])
            : $this->sanitiseUnquotedValue($value);
    }

    /**
     * @param  string $value
     * @param         $quote
     * @return string
     */
    private function sanitiseQuotedValue($value, $quote)
    {
        $regexPattern = sprintf(
            '/^
                %1$s          # match a quote at the start of the value
                (             # capturing sub-pattern used
                 (?:          # we do not need to capture this
                  [^%1$s\\\\] # any character other than a quote or backslash
                  |\\\\\\\\   # or two backslashes together
                  |\\\\%1$s   # or an escaped quote e.g \"
                 )*           # as many characters that match the previous rules
                )             # end of the capturing sub-pattern
                %1$s          # and the closing quote
                .*$           # and discard any string after the closing quote
                /mx',
            $quote
        );

        $value = str_replace("\\$quote", $quote, preg_replace($regexPattern, '$1', $value));

        return trim(str_replace('\\\\', '\\', $value));
    }

    /**
     * @param  string $value
     * @return string
     */
    private function sanitiseUnquotedValue($value)
    {
        $parts = explode('#', $value, 2);
        $value = trim($parts[0]);

        // Unquoted values cannot contain whitespace
        if (preg_match('/\s+/', $value) > 0) {
            throw new \RuntimeException(
                'Environment variables containing spaces must be surrounded by quotes.'
            );
        }

        return $value;
    }
}
