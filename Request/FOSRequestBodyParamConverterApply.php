<?php namespace Draw\DrawBundle\Request;

namespace Draw\DrawBundle\Request;

use Draw\DrawBundle\PropertyAccess\DynamicArrayObject;
use Draw\DrawBundle\Request\Exception\RequestValidationException;
use Draw\DrawBundle\Serializer\GroupHierarchy;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FOSRequestBodyParamConverterApply extends \FOS\RestBundle\Request\RequestBodyParamConverter
{
    /**
     * @var GroupHierarchy
     */
    private $groupHierarchy;

    private $validationErrorsArg;

    public function __construct(
        Serializer $serializer,
        $groups = null,
        $version = null,
        ValidatorInterface $validator = null,
        $validationErrorsArgument = null
    ) {
        $this->validationErrorsArg = $validationErrorsArgument;
        parent::__construct($serializer, $groups, $version, $validator, $validationErrorsArgument);
    }


    public function setGroupHierarchy(GroupHierarchy $groupHierarchy)
    {
        $this->groupHierarchy = $groupHierarchy;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array)$configuration->getOptions();

        if (isset($options['propertiesMap'])) {
            $content = new DynamicArrayObject(json_decode($request->getContent(), true));

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

        if ($this->validationErrorsArg && $request->attributes->has($this->validationErrorsArg)) {
            if (count($errors = $request->attributes->get($this->validationErrorsArg))) {
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

    public function configureContext(Context $context, array $options)
    {
        if (!isset($options['groups'])) {
            $options['groups'] = ['Default'];
        }

        $options['groups'] = $this->groupHierarchy->getReachableGroups($options['groups']);

        parent::configureContext($context, $options);

        if(isset($options['attributes'])) {
            foreach($options['attributes'] as $attribute => $value) {
                $context->setAttribute($attribute, $value);
            }
        }

        return $context;
    }
}