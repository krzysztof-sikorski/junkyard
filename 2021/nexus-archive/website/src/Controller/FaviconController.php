<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Config\AppRoutes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class FaviconController
{
    #[Route(path: '/favicon.ico', name: AppRoutes::FAVICON_ICO, methods: [Request::METHOD_GET])]
    public function favicon(): Response
    {
        return new Response(content: null, status: Response::HTTP_GONE, headers: []);
    }
}
