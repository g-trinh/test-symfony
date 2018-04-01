<?php

namespace AppBundle\Exception\Factory;

class FactoryMessageNotSupportedException extends \Exception
{
    protected $message = 'The message %s is not supported by the factory.';

    public function __construct($factoryMessage, $code = 0, \Throwable $previous = null)
    {
        ob_start();
        print_r($factoryMessage);

        $factoryMessageOutput = ob_get_clean();

        $this->message = printf($this->message, $factoryMessageOutput);
    }
}