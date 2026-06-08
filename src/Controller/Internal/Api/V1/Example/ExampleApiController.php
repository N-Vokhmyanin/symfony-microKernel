<?php

namespace Core\Controller\Internal\Api\V1\Example;

use FOS\RestBundle\Controller\Annotations\Route;
use Core\Controller\AssistantControllers\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Core\Form\Type\Example\ExampleMessagesType;
use Core\Services\Helpers\Response\ApiResponseException;
use Core\Services\Example\ExampleService;
use Core\Services\Api\Example\DTO\Request\MessagesRequestDTO;

class ExampleApiController extends AbstractApiController
{
    protected ?array $serializeGroups = ['example:public'];

    /**
     * @Route("/api/v1/example/{id}/messages/get", name="api_v1_example_test_messages_get", requirements={"id"="\d+"}, methods={"POST"})
     */
    final function getTestMessages(int $id, Request $request, ExampleService $exampleService): Response
    {
        return $this->wrapperRespond(function () use ($request, $id, $exampleService) {
            return $exampleService->getExampleMessagesByTestId($id, $this->messagesRequestProcess($request));
        });
    }

    /**
     * Валидирует запрос и возвращает валидные данные.
     *
     * @param Request $request
     * @return MessagesRequestDTO
     * @throws ApiResponseException
     */
    private function messagesRequestProcess(Request $request): MessagesRequestDTO
    {
        $form = $this->buildForm(ExampleMessagesType::class, null, [
            'block_name' => $request->attributes->get('_route'),
        ]);

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new ApiResponseException($form, Response::HTTP_BAD_REQUEST);
        }

        return $form->getData();
    }
}
