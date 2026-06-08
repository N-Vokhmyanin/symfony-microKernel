<?php

declare(strict_types=1);

namespace Core\Services\Api\Example\DTO\Request;

use Core\Traits\Common\ReflectObjectTrait;

class MessagesRequestDTO
{
    use ReflectObjectTrait;

    /**
     * Типы сообщений
     */
    public const MESSAGE_TYPE_ALL = 'all';
    public const MESSAGE_TYPE_DELIVERED = 'delivered';
    public const MESSAGE_TYPE_UNDELIVERED = 'undelivered';

    /** @var string Дата начала периода в формате ISO 8601 (например: 2025-08-27T09:00:00Z) */
    public string $date_from;
    
    /** @var string Дата окончания периода в формате ISO 8601 (например: 2025-12-29T23:59:59Z) */
    public string $date_to;
    
    /** @var int|null Лимит записей */
    public ?int $limit;
    
    /** @var array Массив ID примеров */
    public array $example_ids;
    
    /** @var array Массив ID пользователей */
    public array $user_ids;
    
    /** @var string Тип сообщения */
    public string $message_type;
}
