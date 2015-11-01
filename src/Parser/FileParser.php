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
use InvalidArgumentException;
use RuntimeException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class FileParser implements ParserInterface
{
    /**
     * @var string
     */
    private $filepath;

    /**
     * @param string $filepath
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * @inheritdoc
     */
    public function parse()
    {
        if (! is_readable($this->filepath) || ! is_file($this->filepath)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Environment file path "%s" not found or not readable.',
                    $this->filepath
                )
            );
        }

        $autodetect = @ini_get('auto_detect_line_endings');
        @ini_set('auto_detect_line_endings', '1');

        $parsed = [];
        foreach ($this->getLines() as $line) {
            $parsed[] = $this->parseLine($line);
        }

        @ini_set('auto_detect_line_endings', $autodetect);

        return $parsed;
    }

    /**
     * Read file line by line using a generator.
     *
     * @return \Generator
     */
    private function getLines()
    {
        $f = fopen($this->filepath, 'r');
        if (!$f) {
            throw new RuntimeException(
                sprintf(
                    'Environment file "%s" can\'t be read.',
                    $this->filepath
                )
            );
        }
        while ($line = fgets($f)) {
            yield $line;
        }
        fclose($f);
    }

    /**
     * @param  string                 $lineString
     * @return \Gea\Variable\Variable
     */
    private function parseLine($lineString = '')
    {
        $data = [];
        $lineString = trim($lineString);

        if (
            empty($lineString)
            || (strpos($lineString, '#') === 0)
            || (strpos($lineString, '=') === false)
        ) {
            return new Variable($data);
        }

        list($rawName, $rawValue) = array_map('trim', explode('=', $lineString, 2));
        $name = trim(str_replace(['export ', '\'', '"'], '', $rawName));

        if (! empty($name) && ! empty($rawValue)) {
            $data['value'] = $this->sanitiseValue($rawValue);
            $data['name'] = $name;
        }

        return new Variable($this->checkNested($data));
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
            ? $this->sanitiseQuotedValue($value)
            : $this->sanitiseUnquotedValue($value);
    }

    /**
     * @param  string $value
     * @return string
     */
    private function sanitiseQuotedValue($value)
    {
        $quote = $value[0];
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
        $value = preg_replace($regexPattern, '$1', $value);
        $value = str_replace("\\$quote", $quote, $value);
        $value = str_replace('\\\\', '\\', $value);

        return trim($value);
    }

    /**
     * @param  string $value
     * @return string
     */
    private function sanitiseUnquotedValue($value)
    {
        $parts = explode(' #', $value, 2);
        $value = trim($parts[0]);

        // Unquoted values cannot contain whitespace
        if (preg_match('/\s+/', $value) > 0) {
            throw new InvalidArgumentException('Dotenv values containing spaces must be surrounded by quotes.');
        }

        return trim($value);
    }

    /**
     * Resolve the nested variables.
     *
     * Look for {$varname} patterns in the variable value and replace with an existing
     * environment variable.
     *
     * @param  array $data
     * @return array
     */
    private function checkNested(array $data)
    {
        if (strpos($data['value'], '$') !== false) {
            $matches = [];
            if (preg_match_all('/\${([a-zA-Z0-9_]+)}/', $data['value'], $matches) === 1) {
                $data['nested'] = array_unique($matches[1]);
            }
        }

        return $data;
    }
}
