<?php

namespace Core\Controller\AssistantControllers;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Log\LoggerInterface;
use Core\Services\Helpers\PlugLogger;
use Core\Services\Helpers\Response\ResponseData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractApiController
 *
 * Абстрактный класс общей логики контроллеров API.
 */
class AbstractApiController extends AbstractFOSRestController
{
    /** @var array|null Группы доступа для сериализации сущностей. */
    protected ?array $serializeGroups = null;

    protected LoggerInterface $logger;
    protected EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, ContainerInterface $container)
    {
        $this->logger = $container->get('monolog.logger.api', ContainerInterface::NULL_ON_INVALID_REFERENCE) ?? new PlugLogger;
        $this->dispatcher = $dispatcher;
    }

    protected function buildForm(string $type, $data = null, array $options = []): FormInterface
    {
        $options = array_merge($options, [
//            'csrf_protection' => true,
        ]);

        return $this->container->get('form.factory')->createNamed('', $type, $data, $options);
    }

    protected function respond($data, int $statusCode = Response::HTTP_OK): Response
    {
        return $this->handleView($this->view($data, $statusCode));
    }

    /**
     * Обертка сериализации ответа в единой структуре формата JSON.
     *
     * @param callable $payload - Функция возвращаемая полезные данные.
     * @param array|null $options - Дополнительные параметры: statusCode, groups, extra_fields.
     * @return Response
     */
    protected function wrapperRespond(callable $payload, ?array $options = null): Response
    {
        $respondData = new ResponseData(
            $this->logger,
            $payload,
            $options['statusCode'] ?? Response::HTTP_OK,
            $options['extra_fields'] ?? []
        );

        $view = $this->view($respondData->getResponseData(), $respondData->getStatusCode());

        $serializeGroups = $options['groups'] ?? [];
        if (!is_null($this->serializeGroups)) {
            $serializeGroups = array_merge($serializeGroups, $this->serializeGroups);
        }

        if (!empty($serializeGroups)) {
            $view->getContext()->addGroups($serializeGroups);
        }

        return $this->handleView($view);
    }

    protected function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * Установка группы для сериализации.
     *
     * @param string $group
     * @return void
     */
    protected function setSerializeGroup(string $group): void
    {
        $this->serializeGroups = [$group];
    }
}