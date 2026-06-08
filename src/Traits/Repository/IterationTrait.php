<?php

namespace Core\Traits\Repository;

trait IterationTrait
{
    /**
     * Генератор для итерации по объектам с заданными критериями
     * 
     * @param array $criteria Критерии поиска
     * @param array|null $orderBy Сортировка
     * @param int|null $limit Лимит
     * @param int|null $offset Смещение
     * @return \Generator
     */
    public function iterateFindBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): \Generator
    {
        foreach ($this->findBy($criteria, $orderBy, $limit, $offset) as $entity) {
            yield $entity;
        }
    }
}