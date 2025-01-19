<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Config\AppRoutes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class AboutController
{
    public function __construct(private Environment $twigEnvironment)
    {
    }

    #[Route(path: '/about', name: AppRoutes::ABOUT, methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $responseBody = $this->twigEnvironment->render(name: 'about/index.html.twig');

        return new Response(content: $responseBody);
    }
}
