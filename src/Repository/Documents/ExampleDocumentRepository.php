<?php

namespace Core\Repository\Documents;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Core\Document\ExampleDocument;
use Core\Repository\Interfaces\RestRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ExampleDocumentRepository extends ServiceDocumentRepository implements RestRepositoryInterface
{
    /** @var int Лимит записей по умолчанию. */
    private int $limitDefault;

    public function __construct(ManagerRegistry $registry, ContainerInterface $container)
    {
        $this->limitDefault = $container->getParameter('items_list_limit')['example_document'];

        parent::__construct($registry, ExampleDocument::class);
    }

    /**
     * Получение списка с учетом пагинации.
     *
     * @param Request $request
     * @return array
     */
    public function getListPagination(Request $request): array
    {
        $exampleId = $request->get('example_id');
        if (is_null($exampleId)) {
            throw new ResourceNotFoundException('example_id param not found');
        }

        $limit = $request->get('limit');
        $offset = $request->get('offset');
        $cursorId = $request->get('cursor_id');

        $queryBuilder = $this
            ->createQueryBuilder()
            ->field('example_id')->equals((int)$exampleId)
            ->sort('_id', 'ASC');

        $queryBuilder->limit($limit ?? $this->limitDefault);

        if(!is_null($offset)) {
            // Пагинация смещением
            $queryBuilder->skip($offset);
        } elseif (!is_null($cursorId)) {
            //Курсорная пагинация
            $queryBuilder->field('_id')->gt(new \MongoDB\BSON\ObjectId($cursorId));
        };

        return $queryBuilder->getQuery()->toArray();
    }
}