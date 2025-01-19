<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Config\AppRoutes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class HomeController
{
    public function __construct(private Environment $twigEnvironment)
    {
    }

    #[Route(path: '/', name: AppRoutes::HOME, methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $content = $this->twigEnvironment->render(name: 'home/index.html.twig');

        return new Response(content: $content);
    }
}
