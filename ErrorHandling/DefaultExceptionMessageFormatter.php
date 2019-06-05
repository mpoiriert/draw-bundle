<?php namespace Draw\DrawBundle\ErrorHandling;

use Throwable;

class DefaultExceptionMessageFormatter implements ExceptionMessageFormatterInterface
{
    public function formatExceptionMessage(Throwable $throwable)
    {
        return $throwable->getMessage();
    }

}