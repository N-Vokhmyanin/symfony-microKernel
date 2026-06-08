<?php

namespace Core\Services\Helpers\Request;

class RequestHandlerException extends \Exception
{
    private array $context = [];

    public function setContext(array $data): self
    {
        $this->context = $data;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}