<?php

namespace Core;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

class App
{
    /** @var Kernel */
    private Kernel $kernel;

    public function __construct()
    {
        //TODO: Для тестов, важно добавить соответсвующий режим "test".
        $getKernelConfig = function (): array {
            if (\App\App::getServiceLabel() === 'production') {
                return ['prod', false];
            }

            return ['dev', true];
        };

        $this->kernel = new Kernel(...$getKernelConfig());
    }

    /**
     * Инициализация приложения.
     * Предварительная обработка и проверка на сопоставление с существующими роутами по значениям "routers" { @see VkSender/config/index.php }
     * Если не не найдено соответствий, инициализация ядра Symfony 5.4.x
     *
     * @param bool $auth - выполняется проверка наличия сессии пользователя и авторизация (Для запуска облегченного приложения).
     * @return void
     * @throws \Exception
     */
    public function run(bool $auth = true): void
    {
        // Передача управления новому ядру Symfony 5.4
        $request = Request::createFromGlobals();

        $response = $this->kernel->handle($request);
        $response->send();

        $this->kernel->terminate($request, $response);
    }
}