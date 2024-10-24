<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Contracts\Symfony\RouteNames;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
final readonly class CanonicalDomainController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    #[Route(
        path: '{path}',
        name: RouteNames::CANONICAL_DOMAIN_REDIRECT->value,
        requirements: ['path' => Requirement::CATCH_ALL],
        condition: 'context.getHost() != "%app.canonical_host%"',
    )]
    public function __invoke(Request $request, null | string $path = null): Response
    {
        $url = $this->urlGenerator->generate(
            name: RouteNames::DOCUMENT->value,
            parameters: ['path' => $path, ...$request->query->all()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new RedirectResponse(url: $url, status: Response::HTTP_MOVED_PERMANENTLY);
    }
}
