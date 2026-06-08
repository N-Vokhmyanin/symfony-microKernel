<?php

namespace Core\Services\Api\Example;

use Core\Services\Helpers\Request\GuzzleRequestHandler;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleRetry\GuzzleRetryMiddleware;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Core\Services\Api\Example\DTO\Request\MessagesRequestDTO;
use Core\Services\Api\Example\DTO\Response\MessagesResponseDTO;
use Core\Services\Api\Example\ExampleApiException;

class ExampleApi extends GuzzleRequestHandler
{
    /** @var string[] Ендпоинты API */
    private const METHODS = [
        'getMessagesByTestId' => '/api/example/%d/messages/get',
    ];

    // Retry options (after 429, 418 HTTP response error)
    /** @var int Maximum number of retries per request */
    private const MAX_RETRY_ATTEMPTS = 3;
    /** @var int If set, specifies a hard ceiling in seconds that this middleware will allow retries */
    private const GIVE_UP_AFTER_SECS = 4;

    protected array $clientConfig = [
        'timeout' => 7.0,
    ];

    public function __construct(string $serviceUrl)
    {
        $this->baseRequestUri = $serviceUrl;
    }

    /**
     * Получение сообщений по test_id.
     *
     * @param int $testId
     * @param MessagesRequestDTO $request
     * @return MessagesResponseDTO
     * @throws \Exception
     */
    public function getMessagesByTestId(int $testId, MessagesRequestDTO $request): MessagesResponseDTO
    {
        return $this->requestHandler(
            self::REQUEST_METHOD_POST,
            sprintf(self::METHODS[__FUNCTION__], $testId),
            [
                RequestOptions::JSON => $request->toArray(),
            ],
            function (ResponseInterface $ri): MessagesResponseDTO {
                try {
                    $payload = json_decode($ri->getBody()->getContents(), true);
                    return new MessagesResponseDTO($payload);
                } catch (\Throwable $e) {
                    throw new ExampleApiException(sprintf('ExampleApi request method: %s, message: %s', __FUNCTION__, $e->getMessage()));
                }
            }
        );
    }

    /**
     * @throws ExampleApiException
     */
    protected function requestRejectedHandle(RequestException $e): void
    {
        $response = $e->getResponse();
        throw new ExampleApiException(sprintf('ExampleApi request error with code: %d, message: %s', $response ? $response->getStatusCode() : 0, $e->getMessage()));
    }

    protected function configHandlerStack(HandlerStack $hs): void
    {
        $hs->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('Content-Type', 'application/json');
        }));

        // Механизм Retry
        $hs->push(GuzzleRetryMiddleware::factory([
            'max_retry_attempts' => self::MAX_RETRY_ATTEMPTS,
            'give_up_after_secs' => self::GIVE_UP_AFTER_SECS
        ]));
    }
}
