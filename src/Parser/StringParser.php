<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Parser;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class StringParser implements ParserInterface
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var \Gea\Parser\LineParserInterface
     */
    private $lineParser;

    /**
     * StringParser constructor.
     *
     * @param string                               $content
     * @param \Gea\Parser\LineParserInterface|null $lineParser
     */
    public function __construct($content, LineParserInterface $lineParser = null)
    {
        $this->content = is_string($content) ? trim($content) : '';
        $this->lineParser = $lineParser ?: new DotenvLineParser();
    }

    /**
     * @inheritdoc
     */
    public function parse()
    {
        $parsed = [];
        if ($this->content) {
            $lines = explode("\n", preg_replace('~\R~u', "\n", $this->content));
            $parsed = array_map([$this->lineParser, 'parseLine'], $lines);
        }

        return $parsed;
    }
}
