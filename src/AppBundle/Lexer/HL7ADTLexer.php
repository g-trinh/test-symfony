<?php

namespace AppBundle\Lexer;

class HL7ADTLexer implements LexerInterface
{
    private $delimiter;
    private $lexer;

    /**
     * HL7ADTLexer constructor.
     *
     * @param string $delimiter
     * @param LexerInterface $lexer
     */
    public function __construct(string $delimiter, LexerInterface $lexer = null)
    {
        if ($delimiter === 'crlf') {
            // Passing the delimiter with yaml causes it to be injected with simple quotes
            // but php needs EOL tokens to be passed with double quotes.
            // So we need to reset before setting it in the object.
            $delimiter = PHP_EOL;
        }

        $this->delimiter = $delimiter;
        $this->lexer = $lexer;
    }

    /**
     * {@inheritdoc}
     */
    public function tokenize(string $string)
    {
        if (!$this->isParsable($string)) {
            return $string;
        }

        $tokens = explode($this->delimiter, $string);

        if (null !== $this->lexer && !empty($tokens)) {
            foreach ($tokens as $key => $token) {
                $tokens[$key] = $this->lexer->tokenize($token);
            }
        }


        return $tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function isParsable(string $string): bool
    {
        return strpos($string, $this->delimiter) !== false;
    }
}