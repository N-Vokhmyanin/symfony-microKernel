<?php

namespace Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function notFound(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND);
    }
}