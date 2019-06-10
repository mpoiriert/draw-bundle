<?php namespace Draw\DrawBundle\Request;

use Draw\DrawBundle\PropertyAccess\DynamicArrayObject;
use Draw\DrawBundle\Request\Exception\RequestValidationException;
use FOS\RestBundle\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBodyParamConverter extends \FOS\RestBundle\Request\RequestBodyParamConverter
{
    private $validationErrorsArgument;

    public function __construct(
        Serializer $serializer,
        $groups = null,
        $version = null,
        ValidatorInterface $validator = null,
        $validationErrorsArgument = null
    ) {
        parent::__construct($serializer, $groups, $version, $validator, $validationErrorsArgument);
        $this->validationErrorsArgument = $validationErrorsArgument;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array)$configuration->getOptions();

        if (isset($options['propertiesMap'])) {
            //This allow a empty body to be consider as '{}'
            if(is_null($requestData = json_decode($request->getContent(), true))) {
                $requestData = [];
            }
            $content = new DynamicArrayObject($requestData);

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            $attributes = (object)$request->attributes->all();
            foreach ($options['propertiesMap'] as $target => $source) {
                $propertyAccessor->setValue(
                    $content,
                    $target,
                    $propertyAccessor->getValue($attributes, $source)
                );
            }

            $property = new \ReflectionProperty(get_class($request), 'content');
            $property->setAccessible(true);
            $property->setValue($request, json_encode($content->getArrayCopy()));
        }

        $result = parent::apply($request, $configuration);

        if ($this->validationErrorsArgument && $request->attributes->has($this->validationErrorsArgument)) {
            if (count($errors = $request->attributes->get($this->validationErrorsArgument))) {
                $this->convertValidationErrorsToException($errors);
            }
        }

        return $result;
    }

    protected function convertValidationErrorsToException($errors)
    {
        $exception = new RequestValidationException();
        $exception->setViolationList($errors);
        throw $exception;
    }
}