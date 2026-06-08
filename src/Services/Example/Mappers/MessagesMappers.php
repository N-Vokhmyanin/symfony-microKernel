<?php

namespace Core\Services\Example\Mappers;

use Core\Services\Example\DTO\Messages\ExampleMessagesDTO;
use Core\Services\Example\DTO\Messages\MessageDTO;
use Core\Services\Api\Example\DTO\Response\MessageItemDTO;
use Core\Services\Api\Example\DTO\Response\MessagesResponseDTO;

class MessagesMappers
{
    /**
     * Возвращает пустой ответ со статистикой.
     *
     * @param MessagesResponseDTO $exampleResp
     * @return ExampleMessagesDTO
     */
    public static function getEmptyExampleMessagesResponse(MessagesResponseDTO $exampleResp): ExampleMessagesDTO
    {
        $response = new ExampleMessagesDTO();
        $response->setItems([]);
        $response->setNextCursor($exampleResp->next_cursor);

        return $response;
    }

    /**
     * @param MessagesResponseDTO $exampleResp
     * @param array<int, string> $entitiesValue
     * @return ExampleMessagesDTO
     */
    public static function getExampleMessagesResponse(
        MessagesResponseDTO $exampleResp,
        array $entitiesValue,
    ): ExampleMessagesDTO {
        $response = self::getEmptyExampleMessagesResponse($exampleResp);
        /** @var MessageItemDTO $mesItem */
        foreach ($exampleResp->iterateMessagesItemsDTO() as $mesItem) {
            $item = new MessageDTO();
            $item->setId($mesItem->getExternalId());
            $item->setData($mesItem->getEventTime());
            $item->setTitle($entitiesValue[$mesItem->getEntityId()]);
            $item->setFullName('TEST NAME');

            $response->setItem($item);
        }

        return $response;
    }
}
