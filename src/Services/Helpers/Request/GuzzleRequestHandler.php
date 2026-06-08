<?php


namespace Core\Services\Helpers\Request;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GuzzleRequestHandler
 * Абстрактный класс с методами-помощниками для удобной работы с запросами
 * @package App\Components\Request
 */
abstract class GuzzleRequestHandler
{
    /** Список HTTP методов */
    protected const REQUEST_METHOD_GET = 'GET';
    protected const REQUEST_METHOD_POST = 'POST';
    protected const REQUEST_METHOD_PUT = 'PUT';
    protected const REQUEST_METHOD_DELETE = 'DELETE';
    protected const REQUEST_METHOD_PATCH = 'PATCH';

    /** Список HTTP статусов */
    protected const REQUEST_STATUS_OK = 200;
    protected const REQUEST_STATUS_BAD_REQUEST = 400;
    protected const REQUEST_STATUS_UNAUTHORIZED = 401;
    protected const REQUEST_STATUS_FORBIDDEN = 403;
    protected const REQUEST_STATUS_NOT_FOUND = 404;
    protected const REQUEST_STATUS_METHOD_NOT_ALLOWED = 405;
    protected const REQUEST_STATUS_CONFLICT = 409;
    protected const REQUEST_STATUS_CODE_UNPROCESSABLE_ENTITY = 422;
    protected const REQUEST_STATUS_CODE_TOO_MANY_REQUESTS = 429;
    protected const REQUEST_STATUS_INTERNAL_SERVER_ERROR = 500;

    /** @var string Одно из стандартных значений заголовка - User-Agent */
    protected const CUSTOM_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0';

    /** @var string|null Базовый путь API */
    protected ?string $baseRequestUri;
    /** @var string|null Динамический базовый путь API */
    protected ?string $dynamicBaseRequestUri = null;
    /** @var array Дополнительная конфигурация клиента (Кроме: base_uri, handler) */
    protected array $clientConfig = [];
    /** @var bool Режим отладки запросов {@see https://phpguzzle.org/ru/request-options.html#debug} */
    protected bool $debugRequestMode = false;
    /** @var array Ассоциативный массив значений cookies */
    protected array $cookiesData = [];
    /** @var array Общая конфигурация запроса {@see https://phpguzzle.org/ru/request-options.html} */
    protected array $requestConfig = [];
    /** @var int Максимально количество отправленных за раз запросов при асинхронном подходе */
    protected int $asyncConcurrency = 10;
    /** @var string[]|array[] Список прокси, которые случайным образом будут применяться к запросам {@see https://phpguzzle.org/ru/request-options.html#proxy} */
    protected array $listRandomProxy = [];

    /** @var Client|null Экземпляр http клиента */
    private ?Client $client;
    /** @var array Данные асинхронных запросов */
    private array $asyncData = [];

    /**
     * Обработчик ошибок
     *
     * @param RequestException $e
     */
    abstract protected function requestRejectedHandle(RequestException $e): void;

    /**
     * Конфигурация Middlewares GuzzleHttp/Client
     *
     * {@see https://phpguzzle.org/ru/handlers-and-middleware.html}
     *
     * Auth API Middleware
     * <code>
     * $hs->push(Middleware::mapRequest(function (RequestInterface $request) {
     * return $request->withHeader('Authorization', API_KEY);
     * }))
     * </code>
     * Retry request Logic {@see https://github.com/caseyamcl/guzzle_retry_middleware}
     * <code>
     * $hs->push(GuzzleRetryMiddleware::factory([
     * 'max_retry_attempts' => VALUE,
     * 'give_up_after_secs' => VALUE,
     * ...options
     * ]));
     * </code>
     * Masking Guzzle User Agent Header
     * <code>
     * $hs->push(Middleware::mapRequest(function (RequestInterface $request) {
     * return $request->withHeader('User-Agent', $this::CUSTOM_USER_AGENT);
     * }));
     * </code>
     *
     * @param HandlerStack $hs
     */
    abstract protected function configHandlerStack(HandlerStack $hs): void;

    /**
     * Помощник запросов
     *
     * Встроена обработка ошибок {@see requestRejectedHandle}
     *
     * @param string $method - HTTP метод запроса
     * @param string $methodUrl - Запрашиваемый endPoint
     * @param array $options - Параметры запроса
     * @param callable $getResponseData - Функция которая получает необходимые данные из структуры ответа {@see ResponseInterface}
     * <code>
     * fn(ResponseInterface $ri): bool => $ri->getStatusCode() === 200)
     * fn(ResponseInterface $ri): array => Json::decode($ri->getBody())
     * </code>
     * @return mixed
     * @throws Exception
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    final function requestHandler(string $method, string $methodUrl, array $options, callable $getResponseData)
    {
        try {
            $response = $this
                ->getClient()
                ->request($method, $methodUrl, array_merge($this->getOptionalRequestOptions(), $options));

            return $getResponseData($response);
        } catch (RequestException $e) {
            $this->requestRejectedHandle($e);
        } catch (GuzzleException $e) {
            throw new RequestHandlerException('GuzzleException: EMessage:' . $e->getMessage());
        }
    }

    /**
     * Помощник запросов с пагинацией типа - Cursor Pagination
     *
     * @param array $requestHandlerOptions - Параметры метода {@see requestHandler}
     * @param callable $getPaginationOrNull - Функция получения данных пагинации из ответа.
     * Должна вернуть массив для склейки с следующим запросом, либо null
     * <code>
     * function (array $data) use ($PAGINATION_KEY) {
     * if (empty($data[$PAGINATION_KEY])) {
     *      return null;
     * }
     *
     * return [
     *      RequestOptions::JSON => [
     *           $PAGINATION_KEY => $data[$PAGINATION_KEY];
     *      ]
     *  ];
     * };
     * </code>
     * @param callable $getPayload - Функция получения полезной нагрузки ответа
     * @param int|null $payloadLimit - Лимит получения данных
     * @param bool $returnPaginationData - Вернуть данные пагинации
     * @return array
     * @throws Exception
     */
    final function requestsWithCursorPaginationHandler(
        array    $requestHandlerOptions,
        callable $getPaginationOrNull,
        callable $getPayload,
        ?int     $payloadLimit = null,
        bool     $returnPaginationData = false): array
    {
        [$method, $methodUrl, $options, $getResponseData] = $requestHandlerOptions;
        $options = array_merge($this->getOptionalRequestOptions(), $options);

        $result = [];
        $cursorPaginationData = [];
        do {
            $respData = $this->requestHandler($method, $methodUrl, $options, $getResponseData);
            $nextCursorPagination = $getPaginationOrNull($respData);
            if (!is_null($nextCursorPagination)) {
                $options = array_merge($options, $nextCursorPagination);
                if ($returnPaginationData) {
                    $cursorPaginationData = array_merge($cursorPaginationData, [$nextCursorPagination]);
                }
            }

            $result = array_merge($result, $getPayload($respData));
        } while (!is_null($payloadLimit) ? count($result) < $payloadLimit : !is_null($nextCursorPagination));

        return $returnPaginationData ? [$result, $cursorPaginationData] : $result;
    }

    /**
     * Помощник асинхронных запросов
     *
     * Встроена обработка ошибок {@see requestRejectedHandle}
     *
     * @param string $method - HTTP метод запроса
     * @param string $methodUrl - Запрашиваемый endPoint
     * @param array $staticOptions - Статические параметры всех запросов
     * @param callable $optionsCallable - Функция с необходимыми параметрами запросов
     * <code> function () use ($totalCount, $takeBatch, $offset) {
     * // Динамические параметры запроса
     * $optional = [
     *      RequestOptions::QUERY => [
     *      'take' => $takeBatch,
     *      'offset' => $offset,
     *      ]
     * ];
     * // Подсчет необходимого количества запросов
     * $iteration = (int)ceil(($totalCount - $offset) / $takeBatch);
     * for ($i = 0; $i < $iteration; $i++) {
     *      yield from [$optional];
     *      // Установка смещения получаемых данных
     *      $optional[RequestOptions::QUERY]['offset'] += $takeBatch;
     * }
     * }; </code>
     * // Динамическая ссылка на endpoint
     * <code>
     * function () use ($ids) {
     *      foreach ($ids as $id) {
     *          yield sprintf('bla-bla-bla/%d', $id) => [] // empty array or dynamic options;
     *      }
     * };
     * </code>
     * @param callable $getResponseData - Функция которая получает необходимые данные из структуры ответа {@see ResponseInterface}
     * <code>
     * fn(ResponseInterface $ri): bool => $ri->getStatusCode() === 200)
     * fn(ResponseInterface $ri): array => Json::decode($ri->getBody())
     * </code>
     * @return array
     * @throws Exception
     */
    final function requestAsyncHandler(
        string   $method,
        string   $methodUrl,
        array    $staticOptions,
        callable $optionsCallable,
        callable $getResponseData): array
    {
        $staticOptions = array_merge($this->getOptionalRequestOptions(), $staticOptions);
        $requests = function () use ($method, $methodUrl, $staticOptions, $optionsCallable) {
            foreach ($optionsCallable() as $dynamicUrlEndPoint => $options) {
                $requestAsyncUrl = is_int($dynamicUrlEndPoint) ? $methodUrl : $dynamicUrlEndPoint;
                yield fn(): PromiseInterface => $this->getClient()->requestAsync($method, $requestAsyncUrl, array_merge($staticOptions, $options));
            }
        };

        (new Pool($this->getClient(), $requests(), [
            'concurrency' => $this->asyncConcurrency,
            //'options' => $staticOptions, // По какой-то причине не работает =\
            'fulfilled' => function (Response $response, $index) use ($getResponseData) {
                $this->asyncData[$index] = $getResponseData($response);
            },
            'rejected' => function (RequestException $e) {
                $this->requestRejectedHandle($e);
            },
        ]))->promise()->wait();

        return $this->getAsyncData();
    }

    /**
     * Помощник запросов с условием максимального количества параметров
     *
     * @param int $maxItemLimit - Максимальное количество параметров
     * @param array $items
     * @param callable $getRequestData
     * <code>
     * function (array $items) {
     *      return $this->requestHandler(METHOD, METHOD_URL, [
     *          RequestOptions::JSON => [
     *              'limitItems' => $items
     *          ]
     *      ], fn(ResponseInterface $ri): array => Json::decode($ri->getBody())['result']['items']);
     * };
     * </code>
     * @return array
     */
    protected function requestChunkingHandler(int $maxItemLimit, array $items, callable $getRequestData): array
    {
        if (count($items) <= $maxItemLimit) {
            return $getRequestData($items);
        }

        $result = [];
        foreach (array_chunk($items, $maxItemLimit) as $chunkItemsRequest) {
            $result = array_merge($result, $getRequestData($chunkItemsRequest));
        }

        return $result;
    }

    /**
     * Получить значение для вставки Referer
     *
     * @param RequestInterface $request
     * @return string
     */
    protected function getRefererUrl(RequestInterface $request): string
    {
        $requestUrl = $request->getUri();
        return sprintf('%s://%s%s', $requestUrl->getScheme(), $requestUrl->getHost(), $requestUrl->getPath());
    }

    /**
     * Получение кастомного пути для свойства Referer
     *
     * @param string $methodUrl
     * @return string
     */
    protected function getCustomRefererUrl(string $methodUrl): string
    {
        return $this->baseRequestUri . '/' . $methodUrl;
    }

    /**
     * Получить хост базового url
     * @return string
     */
    protected function getBaseRequestHost(): string
    {
        return parse_url($this->baseRequestUri)['host'];
    }

    /**
     * Получение опциональных параметров запроса
     *
     * @return array
     */
    private function getOptionalRequestOptions(): array
    {
        $optionalOptions = $this->requestConfig;

        if ($this->debugRequestMode) {
            $optionalOptions['debug'] = true;
        }
        if ($this->listRandomProxy) {
            $optionalOptions['proxy'] = $this->listRandomProxy[array_rand($this->listRandomProxy)];
        }

        return $optionalOptions;
    }

    /**
     * Получение упорядоченных асинхронных данных
     *
     * @return array
     * @throws Exception
     */
    private function getAsyncData(): array
    {
        $result = [];

        ksort($this->asyncData);
        foreach ($this->asyncData as $dataResponse) {
            $result = array_merge($result, $dataResponse);
        }

        $this->asyncData = [];

        return $result;
    }

    /**
     * Получить экземпляр http Client
     *
     * @return Client
     * @throws Exception
     */
    private function getClient(): Client
    {
        if (!isset($this->client) || (!is_null($this->dynamicBaseRequestUri) && $this->dynamicBaseRequestUri !== $this->baseRequestUri)) {
            if (!is_null($this->dynamicBaseRequestUri)) {
                $this->baseRequestUri = $this->dynamicBaseRequestUri;
            }

            if (!isset($this->baseRequestUri)) {
                throw new Exception('Не назначен параметр - baseRequestUri');
            }

            $config = array_merge([
                'base_uri' => $this->baseRequestUri,
                'handler' => $this->getHandlerClient(),
                'cookies' => $this->getCookiesConfig() ?? false
            ], $this->clientConfig);

            $this->client = new Client($config);
        }

        return $this->client;
    }

    /**
     * Настройки Middleware
     *
     * @return HandlerStack
     */
    private function getHandlerClient(): HandlerStack
    {
        $handlerStack = HandlerStack::create();
        $this->configHandlerStack($handlerStack);

        return $handlerStack;
    }

    /**
     * Настройки Cookie
     *
     * @return CookieJar|null
     */
    private function getCookiesConfig(): ?CookieJar
    {
        if (empty($this->cookiesData)) {
            return null;
        }

        return CookieJar::fromArray($this->cookiesData, $this->getBaseRequestHost());
    }
}