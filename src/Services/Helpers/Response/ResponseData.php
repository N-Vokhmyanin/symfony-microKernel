<?php

namespace Core\Services\Helpers\Response;

use App\Service\Errors;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class ResponseData
 * Класс обертка для единой структуры ответов сервиса.
 */
class ResponseData
{
    /** @var bool Статус исполнения. */
    private bool $process = false;
    /** @var mixed Полезные данные. */
    private $data = [];
    /** @var mixed Информация по ошибке. */
    private $error = null;
    /** @var int Статус кода ответа. */
    private int $statusCode;
    /** @var array Дополнительные поля для ответа. */
    private array $extraFields = [];

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, ?callable $payload, int $statusCode = Response::HTTP_OK, array $extraFields = [])
    {
        $this->logger = $logger;
        $this->extraFields = $extraFields;

        $this->setStatusCode($statusCode);
        if (!is_null($payload)) {
            $this->setPayloadData($payload);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getJsonResponse(): JsonResponse
    {
        return new JsonResponse($this->getResponseData(), $this->statusCode);
    }

    /**
     * @return array
     */
    public function getResponseData(): array
    {
        $responseData = [
            'success' => $this->process,
        ];

        foreach ($this->extraFields as $key => $value) {
            if (is_callable($value)) {
                $responseData[$key] = $value($this->data);
            } else {
                $responseData[$key] = $value;
            }
        }

        $responseData['data'] = $this->data;

        if (!empty($this->error)) {
            $responseData['error'] = $this->error;
        }

        return $responseData;
    }

    /**
     * @param array $error
     * @return ResponseData
     */
    public function setCustomError(array $error): self
    {
        $this->error = $error;
        $this->process = false;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return ResponseData
     */
    private function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param callable $payload
     * @return ResponseData
     */
    private function setPayloadData(callable $payload): self
    {
        try {
            $this->setData($payload());
            $this->process = true;
        } catch (\Throwable $e) {
            $this->process = false;
            $this->exceptionHandler($e);
        }

        return $this;
    }

    /**
     * @param mixed $data
     */
    private function setData($data): void
    {
        $this->data = $data;
    }

    private function exceptionHandler(\Throwable $e)
    {
        switch (true) {
            case $e instanceof ResourceNotFoundException:
                $newCodeStatus = Response::HTTP_NOT_FOUND;
                $errorStruct = [
                    'message' => $e->getMessage(),
                    'code' => $newCodeStatus,
                ];
                break;
            case $e instanceof ApiResponseException:
                $newCodeStatus = $e->getStatusCode();
                $errorStruct = $e->getContent();
                break;
            default:
                $newCodeStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
                $errorStruct = [
                    'message' => Errors::unknown_error($e),
                    'code' => $newCodeStatus,
                ];

                $this->logger->error(sprintf('[x]ResponseData: Message: %s. With code: %d', $e->getMessage(), $newCodeStatus), $errorStruct);
        }

        $this->setStatusCode($newCodeStatus);
        $this->error = $errorStruct;
    }
}