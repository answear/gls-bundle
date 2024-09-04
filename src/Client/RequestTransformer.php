<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Client;

use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Request\RequestInterface;
use Answear\GlsBundle\Serializer\Serializer;
use GuzzleHttp\Psr7\Request as HttpRequest;
use GuzzleHttp\Psr7\Uri;

class RequestTransformer
{
    private Serializer $serializer;
    private ConfigProvider $configuration;

    public function __construct(
        Serializer $serializer,
        ConfigProvider $configuration,
    ) {
        $this->serializer = $serializer;
        $this->configuration = $configuration;
    }

    public function transform(RequestInterface $request): HttpRequest
    {
        $uri = $this->configuration->getUrl() . $request->getEndpoint();

        return new HttpRequest(
            $request->getMethod(),
            new Uri($uri),
            [
                'Content-type' => 'application/json',
            ],
            'GET' === $request->getMethod() ? null : $this->serializer->serialize($request)
        );
    }
}
