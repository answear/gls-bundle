<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Logger;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

class GlsLogger
{
    private const MESSAGE_PREFIX = '[GLS_BUNDLE] ';
    private const INVALID_JSON = '--- INVALID JSON ---';
    private const MAX_CONTENT_LENGTH = 3000;
    private const HUGE_CONTENT_SKIPPED = '--- HUGE CONTENT SKIPPED ---';

    private LoggerInterface $logger;
    private ?string $requestId = null;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logRequest(string $endpoint, RequestInterface $request): void
    {
        try {
            $this->logger->info(
                $this->decorateMessage('Request', $endpoint),
                [
                    'glsRequestId' => $this->requestId,
                    'endpoint' => $endpoint,
                    'uri' => $this->getUri($request->getUri()),
                    'body' => $this->getParsedContent($request->getBody()->getContents()),
                ]
            );
        } catch (\Throwable $t) {
            $this->logger->error(
                $this->decorateMessage('Cannot log request', $endpoint),
                ['exception' => $t]
            );
        }
    }

    public function logResponse(string $endpoint, RequestInterface $request, ResponseInterface $response): void
    {
        try {
            $this->logger->info(
                $this->decorateMessage('Response', $endpoint),
                [
                    'endpoint' => $endpoint,
                    'glsRequestId' => $this->requestId,
                    'uri' => $this->getUri($request->getUri()),
                    'response' => $this->getParsedContent($response->getBody()->getContents()),
                ]
            );
        } catch (\Throwable $t) {
            $this->logger->error(
                $this->decorateMessage('Cannot log response', $endpoint),
                ['exception' => $t]
            );
        }
    }

    public function logError(string $endpoint, \Throwable $t): void
    {
        $this->logger->error(
            $this->decorateMessage('Exception', $endpoint),
            [
                'endpoint' => $endpoint,
                'glsRequestId' => $this->requestId,
                'exception' => $t,
            ]
        );
    }

    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function clearRequestId(): void
    {
        $this->requestId = null;
    }

    private function decorateMessage(string $message, string $endpoint): string
    {
        return self::MESSAGE_PREFIX . $message . ' - ' . $endpoint;
    }

    private function getParsedContent(string $content)
    {
        if (empty($content)) {
            return null;
        }

        if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            return self::HUGE_CONTENT_SKIPPED;
        }

        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return self::INVALID_JSON;
        }
    }

    private function getUri(UriInterface $uri): array
    {
        parse_str($uri->getQuery(), $query);

        return [
            'path' => $uri->getPath(),
            'query' => $query,
        ];
    }
}
