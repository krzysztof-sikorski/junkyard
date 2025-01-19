<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Config\AppParameters;
use Symfony\Component\Serializer\SerializerInterface;

use function array_merge;

/**
 * Decorator service to work around the bug that original service seems to ignore default context defined in config
 */
class Serializer implements SerializerInterface
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    public function serialize(mixed $data, string $format, array $context = []): string
    {
        $context = $this->decorateContext(context: $context);

        return $this->serializer->serialize(data: $data, format: $format, context: $context);
    }

    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        $context = $this->decorateContext(context: $context);

        return $this->serializer->deserialize(data: $data, type: $type, format: $format, context: $context);
    }

    private function decorateContext(array $context): array
    {
        return array_merge(AppParameters::SERIALIZER_DEFAULT_CONTEXT, $context);
    }
}
