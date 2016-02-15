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
     * @var \Gea\Parser\LineParserInterface
     */
    private $lineParser;

    /**
     * FileParser constructor.
     *
     * @param string                          $filepath
     * @param \Gea\Parser\LineParserInterface $lineParser
     */
    public function __construct($filepath, LineParserInterface $lineParser = null)
    {
        $this->filepath = is_string($filepath) ? $filepath : '';
        $this->lineParser = $lineParser ?: new DotenvLineParser();
    }

    /**
     * @inheritdoc
     */
    public function parse()
    {
        if (! is_file($this->filepath) || ! is_readable($this->filepath)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Environment file path "%s" is either invalid, not found or not readable.',
                    $this->filepath
                )
            );
        }

        $autodetect = @ini_get('auto_detect_line_endings');
        @ini_set('auto_detect_line_endings', '1');

        $parsed = [];
        foreach ($this->getLines() as $line) {
            $line and $parsed[] = $this->lineParser->parseLine($line);
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
        if (! $f) {
            throw new \RuntimeException(
                sprintf('Environment file "%s" can\'t be read.', $this->filepath)
            );
        }
        while ($line = fgets($f)) {
            yield $line;
        }
        fclose($f);
    }
}
