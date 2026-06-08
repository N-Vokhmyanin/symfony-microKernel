<?php

declare(strict_types=1);

namespace Core\Services\Api\Example\DTO\Response;

use Core\Services\Helpers\Patterns\BaseDto;

class MessageItemDTO extends BaseDto
{    
    /** @var string Время события в формате ISO 8601 */
    public string $event_time;

    /** @var int ID сущности */
    public int $entity_id;

    /** @var string Тип сущности */
    public string $entity_type;

    /** @var string ID внешнего сообщения */
    public string $external_id;

    public function getEntityId(): int
    {
        return $this->entity_id;
    }

    public function getEntityType(): string
    {
        return $this->entity_type;
    }

    public function getExternalId(): string
    {
        return $this->external_id;
    }

    public function getEventTime(): string
    {
        return $this->event_time;
    }
}
