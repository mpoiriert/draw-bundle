<?php

namespace Draw\DrawBundle\EventListener;

use Draw\DrawBundle\ErrorHandling\ExceptionMessageFormatterInterface;
use Draw\DrawBundle\Validator\Exception\ConstraintViolationListException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $debug;

    private $exceptionCodes;

    /**
     * @var ExceptionMessageFormatterInterface
     */
    private $exceptionMessageFormatter;

    /**
     * @var string
     */
    private $violationKey;

    const DEFAULT_STATUS_CODE = 500;

    public function __construct(
        ExceptionMessageFormatterInterface $exceptionMessageFormatter,
        $debug,
        $exceptionCodes,
        $violationKey = 'errors'
    ) {
        $this->debug = $debug;
        $this->exceptionMessageFormatter = $exceptionMessageFormatter;
        $this->exceptionCodes = $exceptionCodes;
        $this->exceptionCodes[ConstraintViolationListException::class] = 400;
        $this->violationKey = $violationKey;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', 255)
        );
    }

    /**
     * @param $exception
     * @return int
     */
    protected function getStatusCode($exception)
    {
        if($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return $this->isSubclassOf($exception, $this->exceptionCodes) ?: self::DEFAULT_STATUS_CODE;
    }

    /**
     * @param $exception
     * @param $exceptionMap
     * @return bool|string
     */
    protected function isSubclassOf($exception, $exceptionMap)
    {
        $exceptionClass = get_class($exception);
        $reflectionExceptionClass = new \ReflectionClass($exceptionClass);

        foreach ($exceptionMap as $exceptionMapClass => $value) {
            if ($value
                && ($exceptionClass === $exceptionMapClass || $reflectionExceptionClass->isSubclassOf($exceptionMapClass))
            ) {
                return $value;
            }
        }

        return false;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($this->getFormat($request) != 'json') {
            return;
        }

        $this->logger->notice('Intercepted exception', $this->getExceptionDetail($exception, false));

        $statusCode = $this->getStatusCode($exception);

        $data = array(
            "code" => $statusCode,
            "message" => $this->exceptionMessageFormatter->formatExceptionMessage($exception)
        );

        if ($exception instanceof ConstraintViolationListException) {
            $errors = array();
            foreach ($exception->getViolationList() as $constraintViolation) {
                /* @var $constraintViolation \Symfony\Component\Validator\ConstraintViolationInterface */
                $error = array(
                    'propertyPath' => $constraintViolation->getPropertyPath(),
                    'message' => $constraintViolation->getMessage(),
                    'invalidValue' => $constraintViolation->getInvalidValue(),
                    'code' => $constraintViolation->getCode()
                );

                if ($constraintViolation->getConstraint() && !is_null($payload = $constraintViolation->getConstraint()->payload)) {
                    $error['payload'] = $payload;
                }

                $errors[] = $error;
            }

            $data[$this->violationKey] = $errors;
        }

        if ($this->debug) {
            $data['detail'] = $this->getExceptionDetail($exception);
        }

        $event->stopPropagation();
        $event->setResponse(
            new JsonResponse(
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
                $statusCode,
                [],
                true
            )
        );
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getFormat(Request $request)
    {
        $acceptKey = null;
        if ($request->headers->has('Accept')) {
            $acceptKey = 'Accept';
        }
        if ($request->headers->has('accept')) {
            $acceptKey = 'accept';
        }
        if (strstr($request->headers->get($acceptKey), 'json')) {
            return 'json';
        }
        return 'other';
    }

    public function getExceptionDetail($e, $full = true)
    {
        $result = array(
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        );

        if ($full) {
            foreach (explode("\n", $e->getTraceAsString()) as $line) {
                $result['stack'][] = $line;
            }

            if ($previous = $e->getPrevious()) {
                $result['previous'] = $this->getExceptionDetail($previous);
            }
        }


        return $result;
    }
}