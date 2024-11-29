<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Contracts\Symfony\RouteNames;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Twig\Environment;

#[AsController]
final readonly class DashboardController
{
    public function __construct(
        private Environment $twig,
    ) {}

    #[Route(
        path: '%app.admin.base_path%/{path}',
        name: RouteNames::ADMIN_DASHBOARD->value,
        requirements: ['path' => Requirement::CATCH_ALL],
        host: '%app.canonical_host%',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(null | string $path = null): Response
    {
        $content = $this->twig->render(name: 'admin/dashboard.html.twig', context: ['path' => $path]);

        return new Response(content: $content, status: Response::HTTP_OK);
    }
}
