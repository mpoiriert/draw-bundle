<?php namespace Draw\DrawBundle\ErrorHandling;

use Throwable;

interface ExceptionMessageFormatterInterface
{
    public function formatExceptionMessage(Throwable $throwable);
}