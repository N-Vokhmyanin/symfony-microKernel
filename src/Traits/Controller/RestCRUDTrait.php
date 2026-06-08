<?php

namespace Core\Traits\Controller;

use Doctrine\Persistence\ObjectManager;
use Core\Event\Listeners\Rest\Events\RestAfterCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterUpdatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeUpdatedEvent;
use Core\Repository\Interfaces\RestRepositoryInterface;
use Core\Services\Helpers\Response\ApiResponseException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Trait RestCRUDTrait
 *
 * Трейт встраивания CRUD методов в API контроллер.
 */
trait RestCRUDTrait
{
    /** @var string|null Класс DataMapper сущности. */
    protected ?string $entityClass = null;
    /** @var string|null Класс валидатор запроса по типу "Type". */
    protected ?string $typeClass = null;

    protected RestRepositoryInterface $entityRepository;
    protected ObjectManager $objectManager;

    abstract protected function wrapperRespond(callable $payload, ?array $options = null): Response;
    abstract protected function buildForm(string $type, $data = null, array $options = []): FormInterface;
    abstract protected function getDispatcher(): EventDispatcherInterface;

    /**
     * Получение списка сущностей.
     *
     * @param Request $request
     * @param array|null $options - Массив опций:
     * <code>
     * [
     *  "statusCode" => Response::HTTP_OK, (HTTP статус в случае выполнения без исключений)
     *  "groups" => ["rest_extra:read"], (Перечисление дополнительных групп для сериализации вывода объекта)
     * ]
     * </code>
     * @return Response
     */
    protected function restListAction(Request $request, ?array $options = null): Response
    {
        return $this->wrapperRespond(function () use ($request) {
            return $this->entityRepository->getListPagination($request);
        }, $options);
    }

    /**
     * Получение сущности
     *
     * @param string|int $id
     * @param array|null $options - Массив опций:
     * <code>
     * [
     *  "statusCode" => Response::HTTP_OK, (HTTP статус в случае выполнения без исключений)
     *  "groups" => ["rest_extra:read"], (Перечисление дополнительных групп для сериализации вывода объекта)
     * ]
     * </code>
     * @return Response
     */
    protected function restShowAction($id, ?array $options = null): Response
    {
        return $this->wrapperRespond(function () use ($id) {
            return $this->getItemOrNotFoundException($id);
        }, $options);
    }

    /**
     * Создание сущности.
     *
     * @param Request $request
     * @param array|null $options - Массив опций:
     * <code>
     * [
     *  "statusCode" => Response::HTTP_OK, (HTTP статус в случае выполнения без исключений)
     *  "groups" => ["rest_extra:read"], (Перечисление дополнительных групп для сериализации вывода объекта)
     * ]
     * </code>
     * @return Response
     */
    protected function restCreateAction(Request $request, ?array $options = null): Response
    {
        return $this->wrapperRespond(function () use ($request) {
            $form = $this->buildForm($this->typeClass, null, [
                // Используется для более гибкой кастомизации в шаблонах валидации.
                'block_name' => $request->attributes->get('_route'),
            ]);

            $form->handleRequest($request);
            if (!$form->isSubmitted() || !$form->isValid()) {
                throw new ApiResponseException($form, Response::HTTP_BAD_REQUEST);
            }

            $model = $form->getData();

            $this->getDispatcher()->dispatch(new RestBeforeCreatedEvent($model, $request), RestBeforeCreatedEvent::EVENT_NAME);

            $this->objectManager->persist($model);
            $this->objectManager->flush();

            $this->getDispatcher()->dispatch(new RestAfterCreatedEvent($model, $request), RestAfterCreatedEvent::EVENT_NAME);

            return $model;
        }, $options);
    }

    /**
     * Обновление сущности.
     *
     * @param Request $request
     * @param callable $getEntityOrException - Функция возвращаемая сущность которую важно изменить (в случае не успеха, выбрасываем ошибку ResourceNotFoundException).
     * <code>
     *
     * function () use ($id) {
     *  return $this->getItemOrNotFoundException($id);
     * }
     *
     * function () use ($request) {
     *  $entity = $this->entityRepository->findOneBy(['name' => $request->get('name')]);
     *  if (is_null($entity)) {
     *      throw new ResourceNotFoundException('Entity not found');
     *  }
     *
     * return $entity;
     * }
     *
     * </code>
     * @param array|null $options - Массив опций:
     * <code>
     * [
     *  "statusCode" => Response::HTTP_OK, (HTTP статус в случае выполнения без исключений)
     *  "groups" => ["rest_extra:read"], (Перечисление дополнительных групп для сериализации вывода объекта)
     * ]
     * </code>
     * @return Response
     */
    protected function restUpdateAction(Request $request, callable $getEntityOrException, ?array $options = null): Response
    {
        return $this->wrapperRespond(function () use ($request, $getEntityOrException) {
            $entity = $getEntityOrException();

            $form = $this->buildForm($this->typeClass, $entity, [
                // Используется для более гибкой кастомизации в шаблонах валидации.
                'block_name' => $request->attributes->get('_route'),
                'method' => $request->getMethod(),
            ]);

            $form->handleRequest($request);
            if (!$form->isSubmitted() || !$form->isValid()) {
                throw new ApiResponseException($form, Response::HTTP_BAD_REQUEST);
            }

            $model = $form->getData();

            $this->getDispatcher()->dispatch(new RestBeforeUpdatedEvent($model, $request), RestBeforeUpdatedEvent::EVENT_NAME);

            $this->objectManager->persist($model);
            $this->objectManager->flush();

            $this->getDispatcher()->dispatch(new RestAfterUpdatedEvent($model, $request), RestAfterUpdatedEvent::EVENT_NAME);

            return $model;
        }, $options);
    }

    /**
     * Удаление сущности.
     *
     * @param string|int $id
     * @param array|null $options - Массив опций:
     * <code>
     * [
     *  "statusCode" => Response::HTTP_OK, (HTTP статус в случае выполнения без исключений)
     *  "groups" => ["rest_extra:read"], (Перечисление дополнительных групп для сериализации вывода объекта)
     * ]
     * </code>
     * @return Response
     */
    protected function restDeleteAction($id, ?array $options = null): Response
    {
        return $this->wrapperRespond(function () use ($id) {
            $model = $this->getItemOrNotFoundException($id);

            $this->getDispatcher()->dispatch(new RestBeforeDeletedEvent($model, $id), RestBeforeDeletedEvent::EVENT_NAME);

            $this->objectManager->remove($model);
            $this->objectManager->flush();

            $this->getDispatcher()->dispatch(new RestAfterDeletedEvent($model, $id), RestAfterDeletedEvent::EVENT_NAME);

            return null;
        }, $options);
    }

    /**
     * Получение модели Entity.
     *
     * @param string|int $id
     * @return object|null
     *
     * @throws ResourceNotFoundException
     */
    protected function getItemOrNotFoundException($id): ?object
    {
        $entity = $this->entityRepository->find($id);
        if (is_null($entity)) {
            throw new ResourceNotFoundException(sprintf('Entity not found by id = %s', $id));
        }

        return $entity;
    }

    /**
     * Получение модели Entity по параметрам.
     *
     * @param array $params
     * @return object|null
     *
     * @throws ResourceNotFoundException
     */
    protected function getItemByParamsOrNotFoundException(array $params): ?object
    {
        $entity = $this->entityRepository->findOneBy($params);
        if (is_null($entity)) {
            throw new ResourceNotFoundException('Entity not found by params');
        }

        return $entity;
    }
}