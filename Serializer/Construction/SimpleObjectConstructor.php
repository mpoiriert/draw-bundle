<?php namespace Draw\DrawBundle\Serializer\Construction;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

class SimpleObjectConstructor implements ObjectConstructorInterface
{
    public const ON_MISSING_NULL = 'null';
    public const ON_MISSING_EXCEPTION = 'exception';
    public const ON_MISSING_FALLBACK = 'fallback';

    /**
     * @inheritDoc
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object
    {
        $className = $metadata->name;
        return new $className();
    }
}