<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Serializer;

use Answear\GlsBundle\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class Serializer
{
    private SymfonySerializer $serializer;

    public function serialize(RequestInterface $request): string
    {
        return $this->getSerializer()->serialize(
            $request,
            JsonEncoder::FORMAT,
            [Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES => true]
        );
    }

    public function decodeResponse(string $class, ResponseInterface $response)
    {
        return $this->getSerializer()->deserialize(
            $response->getBody()->getContents(),
            $class,
            JsonEncoder::FORMAT,
        );
    }

    private function getSerializer(): SymfonySerializer
    {
        if (!isset($this->serializer)) {
            $this->serializer = new SymfonySerializer(
                [
                    new Normalizer\CustomNormalizer(),
                    new Normalizer\DateTimeNormalizer(
                        [
                            Normalizer\DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\\TH:i:s.uP',
                        ]
                    ),
                    new Normalizer\PropertyNormalizer(
                        null,
                        null,
                        new ReflectionExtractor()
                    ),
                    new Normalizer\ArrayDenormalizer(),
                ],
                [new JsonEncoder()]
            );
        }

        return $this->serializer;
    }
}
