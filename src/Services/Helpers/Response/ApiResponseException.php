<?php

namespace Core\Services\Helpers\Response;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseException extends RuntimeException
{
    private $content;
    private int $statusCode;

    public function __construct($content, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;

        parent::__construct(Response::$statusTexts[$statusCode] ?? 'Unidentified error');
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}