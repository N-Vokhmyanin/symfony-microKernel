<?php

namespace Core\Services\Example;

use Doctrine\Persistence\ManagerRegistry as DoctrineManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry as MongoManagerRegistry;
use Core\Services\Api\Example\DTO\Request\MessagesRequestDTO;
use Core\Services\Api\Example\ExampleApi;
use Core\Document\ExampleDocument;
use Core\Repository\Documents\ExampleDocumentRepository;
use Core\Services\Example\Mappers\MessagesMappers;
use Core\Services\Api\Example\DTO\Response\MessagesResponseDTO as ExampleAPIMessagesResponse;
use Psr\Log\LoggerInterface;
use Core\Services\Helpers\PlugLogger;
use Core\Services\Example\DTO\Messages\ExampleMessagesDTO;

class ExampleService
{
    private ?LoggerInterface $logger = null;
    private ExampleApi $exampleApi;
    private DoctrineManagerRegistry $doctrineManagerRegistry;
    private MongoManagerRegistry $mongoManagerRegistry;

    public function __construct(
        LoggerInterface $logger,
        ExampleApi $exampleApi,
        DoctrineManagerRegistry $managerRegistry,
        MongoManagerRegistry $mongoManagerRegistry
    ) {
        $this->logger = $logger;
        $this->exampleApi = $exampleApi;
        $this->doctrineManagerRegistry = $managerRegistry;
        $this->mongoManagerRegistry = $mongoManagerRegistry;
    }

    /**
     * Возвращает логгер.
     * Если логгер не установлен, то возвращает заглушку.
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        if (is_null($this->logger)) {
            return new PlugLogger();
        }

        return $this->logger;
    }

    /**
     * Получение тестового сообщений по test_id.
     *
     * @param int $testId
     * @param MessagesRequestDTO $request
     * @return ExampleMessagesDTO
     */
    public function getExampleMessagesByTestId(int $testId, MessagesRequestDTO $request): ExampleMessagesDTO
    {
        return $this->getPrepareExampleMessages(
            $request,
            $this->exampleApi->getMessagesByTestId($testId, $request)
        );
    }

    /**
     * Подготовка статистики сообщений.
     *
     * @param MessagesRequestDTO $request
     * @param ExampleAPIMessagesResponse $statResp
     * @return ExampleMessagesDTO
     */
    private function getPrepareExampleMessages(MessagesRequestDTO $request, ExampleAPIMessagesResponse $statResp): ExampleMessagesDTO
    {
        if (empty($statResp->items)) {
            return MessagesMappers::getEmptyExampleMessagesResponse($statResp);
        }

        $documentValues = [];
        foreach ($this->getExampleDocumentRepository()->findBy(['id' => 1]) as $example) {
                $documentValues[$example->getId()] = $example->getValue();
        }

        return MessagesMappers::getExampleMessagesResponse(
            $statResp,
            $documentValues
        );
    }

    private function getExampleDocumentRepository(): ExampleDocumentRepository
    {
        return $this->mongoManagerRegistry->getRepository(ExampleDocument::class);
    }
}
