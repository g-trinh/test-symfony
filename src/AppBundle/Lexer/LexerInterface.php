<?php

namespace AppBundle\Lexer;

interface LexerInterface
{
    /**
     * Transforms a string into tokens
     *
     * @param string $string
     *
     * @return mixed
     */
    public function tokenize(string $string);

    /**
     * Checks if the given string is parsable
     *
     * @param string $string
     *
     * @return bool
     */
    public function isParsable(string $string): bool;
}