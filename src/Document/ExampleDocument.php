<?php

namespace Core\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @MongoDB\Document(collection="example_documents", repositoryClass="Core\Repository\Documents\ExampleDocumentRepository")
 */
class ExampleDocument
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Groups({"rest:read"})
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @MongoDB\Field(type="string", name="name")
     */
    private $name;

    /**
     * @Groups({"rest:read"})
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @MongoDB\Field(type="string", name="value")
     */
    private $value;

    /**
     * @Groups({"rest:read"})
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @MongoDB\Field(type="int", name="channel_id")
     */
    private $channelId;

    /**
     * @return mixed
     */
    public function getChannelId()
    {
        return $this->channelId;
    }

    /**
     * @param int $channelId
     */
    public function setChannelId(int $channelId): void
    {
        $this->channelId = $channelId;
    }
}