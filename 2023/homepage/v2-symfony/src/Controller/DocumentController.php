<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Contracts\Entity\Document\FileInterface;
use App\Contracts\Entity\Document\PageInterface;
use App\Contracts\Entity\Document\PointerInterface;
use App\Contracts\Repository\DocumentRepositoryInterface;
use App\Contracts\Symfony\RouteNames;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Twig\Environment;

use function file_exists;
use function implode;

use const DIRECTORY_SEPARATOR;

#[AsController]
final readonly class DocumentController
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire(param: 'app.uploads_dir')]
        private string $uploadsDir,
    ) {}

    #[Route(
        path: '{path}',
        name: RouteNames::DOCUMENT->value,
        requirements: ['path' => Requirement::CATCH_ALL],
        host: '%app.canonical_host%',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(null | string $path = null): Response
    {
        $document = $this->documentRepository->findByPath(path: $path);

        if (null === $document) {
            throw new NotFoundHttpException(message: "Invalid path: {$path}");
        }

        if ($document->isDeleted()) {
            throw new GoneHttpException(message: 'This document has been deleted');
        }

        if ($document instanceof PageInterface) {
            return $this->handlePage(page: $document);
        }

        if ($document instanceof FileInterface) {
            return $this->handleFile(file: $document);
        }

        if ($document instanceof PointerInterface) {
            return $this->handlePointer(pointer: $document);
        }

        throw new NotFoundHttpException(message: 'Unsupported document type');
    }

    private function handlePage(PageInterface $page): Response
    {
        $content = $this->twig->render(name: 'page.html.twig', context: ['page' => $page]);

        return new Response(content: $content, status: Response::HTTP_OK);
    }

    private function handleFile(FileInterface $file): Response
    {
        $path = implode(separator: DIRECTORY_SEPARATOR, array: [$this->uploadsDir, $file->getStoragePath()]);

        if (false === file_exists(filename: $path)) {
            throw new NotFoundHttpException(message: 'File not found');
        }

        return new BinaryFileResponse(file: $path);
    }

    private function handlePointer(PointerInterface $pointer): Response
    {
        $target = $pointer->getTarget();

        if (null === $target) {
            throw new GoneHttpException(message: 'Redirection target not found');
        }

        $url = $this->urlGenerator->generate(
            name: RouteNames::DOCUMENT->value,
            parameters: ['path' => $target->getPath()],
        );
        $status = $pointer->isPermanent() ? Response::HTTP_MOVED_PERMANENTLY : Response::HTTP_FOUND;

        return new RedirectResponse(url: $url, status: $status);
    }
}
