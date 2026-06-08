<?php

declare(strict_types=1);

namespace Core\Services\Example\DTO\Messages;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class MessageDTO
{
    /** @var string ID сообщения */
    private string $id;

    /** @var string Данные сообщения */
    private string $data;

    /** @var string Заголовок сообщения */
    private string $title;

    /** @var string Полное имя пользователя */
    private string $full_name;

    /**
     * @Groups({"example:read"})
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @Groups({"example:read"})
     * 
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * @Groups({"example:read"})
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @Groups({"example:read"})
     * @SerializedName("full_name")
     * 
     * @return string
     */
    public function getFullName(): string
    {
        return $this->full_name;
    }

    /**
     * @param string $full_name
     */
    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }
}
