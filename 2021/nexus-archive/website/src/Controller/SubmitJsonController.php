<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Config\AppRoutes;
use App\Contract\PageViewSubmissionResultStatus;
use App\Service\PageViewSubmissionHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

final class SubmitJsonController
{
    public function __construct(
        private Environment $twigEnvironment,
        private PageViewSubmissionHandler $pageViewSubmissionHandler,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route(path: '/submit-json', name: AppRoutes::SUBMIT_JSON, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function json(
        Request $request
    ): Response {
        if ($request->isMethod(method: Request::METHOD_POST)) {
            $userAccessTokenValue = $request->request->get(key: 'userAccessToken');
            $jsonData = $request->request->get(key: 'jsonData');

            $submissionResult = $this->pageViewSubmissionHandler->handle(
                userAccessTokenValue: $userAccessTokenValue,
                jsonData: $jsonData,
            );

            $responseStatus = match ($submissionResult->getStatus()) {
                PageViewSubmissionResultStatus::SUCCESS => Response::HTTP_CREATED,
                PageViewSubmissionResultStatus::ERROR_ACCESS_TOKEN => Response::HTTP_UNAUTHORIZED,
                default => Response::HTTP_BAD_REQUEST,
            };

            return $this->createJsonResponse(data: $submissionResult, status: $responseStatus);
        }

        $content = $this->twigEnvironment->render(name: 'submit-json/index.html.twig');

        return new Response(content: $content);
    }

    private function createJsonResponse(mixed $data, int $status): Response
    {
        $serializedData = $this->serializer->serialize(data: $data, format: JsonEncoder::FORMAT);
        return new JsonResponse(data: $serializedData, status: $status, json: true);
    }
}
