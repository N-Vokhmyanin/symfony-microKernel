<?php

declare(strict_types=1);

namespace Core\Services\Example\DTO\Messages;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class ExampleMessagesDTO
{
    /** @var MessageDTO[] Массив сообщений */
    private array $items = [];
    
    /** @var string|null Курсор для пагинации */
    private ?string $next_cursor = null;

    /**
     * @Groups({"example:read"})
     * 
     * @return MessageDTO[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param MessageDTO[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @param MessageDTO $item
     */
    public function setItem(MessageDTO $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @Groups({"example:read"})
     * @SerializedName("next_cursor")
     * 
     * @return string|null
     */
    public function getNextCursor(): ?string
    {
        return $this->next_cursor;
    }

    /**
     * @param string|null $next_cursor
     */
    public function setNextCursor(?string $next_cursor): void
    {
        $this->next_cursor = $next_cursor;
    }
}
