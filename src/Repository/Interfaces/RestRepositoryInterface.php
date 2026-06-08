<?php

namespace Core\Repository\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface RestRepositoryInterface
{
    /**
     * Получение списка с учетом пагинации.
     *
     * @param Request $request
     * @return array
     */
    public function getListPagination(Request $request): array;
}