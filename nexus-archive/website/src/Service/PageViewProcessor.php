<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Service\ClockInterface;
use App\Contract\Service\Parser\ParserResultProcessorInterface;
use App\Contract\Service\Parser\ParserSelectorInterface;
use App\Doctrine\Entity\PageView;
use App\DTO\ParserResult;
use App\Service\Repository\PageViewRepository;
use Psr\Log\LoggerInterface;
use Throwable;

use function count;
use function get_class;

final class PageViewProcessor
{
    public function __construct(
        private ClockInterface $clock,
        private LoggerInterface $logger,
        private PageViewRepository $pageViewRepository,
        private ParserSelectorInterface $parserSelector,
        private ParserResultProcessorInterface $parserResultManager,
    ) {
    }

    public function process(int $batchSize): void
    {
        $startDateTime = $this->clock->getCurrentDateTime();
        $this->logger->debug(message: 'Processing started...', context: ['start' => $startDateTime]);

        $records = $this->pageViewRepository->getUnparsed(batchSize: $batchSize);

        $this->logger->debug(
            message: 'Finished fetching page views from database',
            context: ['count' => count(value: $records)],
        );

        /** @var PageView $pageView */
        foreach ($records as $pageView) {
            $idAsString = $pageView->getId()->toRfc4122();
            $this->logger->debug(
                message: 'Started processing a page view',
                context: ['id' => $idAsString, 'createdAt' => $pageView->getCreatedAt()],
            );
            $parser = $this->parserSelector->findParser($pageView);
            $this->logger->debug(
                message: 'Selected parser',
                context: ['id' => $idAsString, 'class' => null !== $parser ? get_class(object: $parser) : null],
            );

            if (null !== $parser) {
                try {
                    $parserResult = $parser->parse(pageView: $pageView);
                } catch (Throwable $exception) {
                    $parserResult = new ParserResult();
                    $parserResult->addError(error: $exception);
                }
            } else {
                $parserResult = new ParserResult();
                $parserResult->addError(error: 'Could not find parser that supports this page view');
            }

            if (false === $parserResult->hasErrors()) {
                $this->parserResultManager->persist(parserResult: $parserResult);
            } else {
                $this->logger->error(
                    message: 'Parsing has failed',
                    context: ['id' => $idAsString, 'errors' => $parserResult->getErrors()],
                );
            }

            $currentDateTime = $this->clock->getCurrentDateTime();
            $this->pageViewRepository->saveAsParsed(
                pageView: $pageView,
                parsedAt: $currentDateTime,
                errors: $parserResult->getErrors(),
            );
        }

        $endDateTime = $this->clock->getCurrentDateTime();
        $this->logger->debug(
            message: 'Processing complete',
            context: ['start' => $startDateTime, 'end' => $endDateTime],
        );
    }
}
