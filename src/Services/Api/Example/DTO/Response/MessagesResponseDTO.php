<?php

declare(strict_types=1);

namespace Core\Services\Api\Example\DTO\Response;

use Core\Services\Helpers\Patterns\BaseDto;

class MessagesResponseDTO extends BaseDto
{   
    /** @var array Массив сообщений */
    public array $items;
    
    /** @var string|null Курсор для пагинации */
    public ?string $next_cursor;

    /**
     * @return \Generator<MessageItemDTO>
     */
    public function iterateMessagesItemsDTO(): \Generator
    {
        foreach ($this->items as $item) {
            yield new MessageItemDTO($item);
        }
    }
}
