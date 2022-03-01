<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Exception;

class MalformedResponseException extends \RuntimeException
{
    private $response;

    public function __construct(string $message, $response, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
