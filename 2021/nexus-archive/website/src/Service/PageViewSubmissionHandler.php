<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\PageViewSubmissionResultStatus;
use App\Contract\Service\ClockInterface;
use App\Doctrine\Entity\PageView;
use App\DTO\PageViewSubmissionResult;
use App\Service\Repository\PageViewRepository;
use App\Service\Repository\UserAccessTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;

use function array_key_exists;
use function get_object_vars;
use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class PageViewSubmissionHandler
{
    public function __construct(
        private ClockInterface $clock,
        private UserAccessTokenRepository $userAccessTokenRepository,
        private PageViewSubmissionValidator $validator,
        private DateTimeFactory $dateTimeFactory,
        private EntityManagerInterface $entityManager,
        private PageViewRepository $pageViewRepository,
    ) {
    }

    public function handle(string $userAccessTokenValue, string $jsonData): PageViewSubmissionResult
    {
        // validate the access token
        $userAccessToken = $this->userAccessTokenRepository->findByValue(value: $userAccessTokenValue);
        if (null === $userAccessToken) {
            $errors = [
                sprintf('Invalid access token value: %s', $userAccessTokenValue),
            ];
            return new PageViewSubmissionResult(
                status: PageViewSubmissionResultStatus::ERROR_ACCESS_TOKEN,
                errors: $errors,
            );
        }

        $owner = $userAccessToken->getOwner();

        // decode input data from submitted JSON string
        try {
            $decodedJsonData = json_decode(json: $jsonData, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return new PageViewSubmissionResult(
                status: PageViewSubmissionResultStatus::ERROR_JSON_DECODE,
                errors: [$e->getMessage()],
            );
        }

        // validate format of input data
        $validationResult = $this->validator->validate(decodedJsonData: $decodedJsonData);
        if (false === $validationResult->isValid()) {
            return new PageViewSubmissionResult(
                status: PageViewSubmissionResultStatus::ERROR_JSON_SCHEMA,
                errors: $validationResult->getErrors(),
            );
        }

        // build and persist PageView instance
        $currentDateTime = $this->clock->getCurrentDateTime();
        $pageView = $this->buildPageView(decodedJsonData: $decodedJsonData);
        $this->pageViewRepository->persist(owner: $owner, pageView: $pageView, currentDateTime: $currentDateTime);

        return new PageViewSubmissionResult(status: PageViewSubmissionResultStatus::SUCCESS);
    }


    private function buildPageView(object $decodedJsonData): PageView
    {
        $pageView = new PageView();

        $data = get_object_vars(object: $decodedJsonData);

        if (array_key_exists(key: 'requestStartedAt', array: $data)) {
            $requestStartedAt = $this->dateTimeFactory->create(dateTimeString: $data['requestStartedAt']);
            $pageView->setRequestStartedAt(requestStartedAt: $requestStartedAt);
        }

        if (array_key_exists(key: 'responseCompletedAt', array: $data)) {
            $responseCompletedAt = $this->dateTimeFactory->create(dateTimeString: $data['responseCompletedAt']);
            $pageView->setResponseCompletedAt(responseCompletedAt: $responseCompletedAt);
        }

        if (array_key_exists(key: 'method', array: $data)) {
            $pageView->setMethod(method: $data['method']);
        }

        if (array_key_exists(key: 'url', array: $data)) {
            $pageView->setUrl(url: $data['url']);
        }

        if (array_key_exists(key: 'formData', array: $data)) {
            $pageView->setFormData(formData: $data['formData']);
        }

        if (array_key_exists(key: 'responseBody', array: $data)) {
            $pageView->setResponseBody(responseBody: $data['responseBody']);
        }

        return $pageView;
    }
}
