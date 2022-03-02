<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Client;

use Answear\GlsBundle\Exception\ServiceUnavailableException;
use Answear\GlsBundle\Logger\GlsLogger;
use Answear\GlsBundle\Request\RequestInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private ClientInterface $client;
    private RequestTransformer $transformer;
    private GlsLogger $glsLogger;

    public function __construct(
        RequestTransformer $transformer,
        GlsLogger $glsLogger,
        ?ClientInterface $client = null
    ) {
        $this->transformer = $transformer;
        $this->glsLogger = $glsLogger;
        $this->client = $client ?? new \GuzzleHttp\Client();
    }

    public function request(RequestInterface $request): ResponseInterface
    {
        $this->glsLogger->setRequestId(uniqid('GLS', true));
        try {
            $psrRequest = $this->transformer->transform($request);
            $this->glsLogger->logRequest($request->getEndpoint(), $psrRequest);

            $psrResponse = $this->client->send($psrRequest);
            $this->glsLogger->logResponse($request->getEndpoint(), $psrRequest, $psrResponse);

            if ($psrResponse->getBody()->isSeekable()) {
                $psrResponse->getBody()->rewind();
            }
        } catch (GuzzleException $e) {
            $this->glsLogger->logError($request->getEndpoint(), $e);

            throw new ServiceUnavailableException($e->getMessage(), $e->getCode(), $e);
        } catch (\Throwable $t) {
            $this->glsLogger->logError($request->getEndpoint(), $t);

            throw $t;
        } finally {
            $this->glsLogger->clearRequestId();
        }

        return $psrResponse;
    }
}
