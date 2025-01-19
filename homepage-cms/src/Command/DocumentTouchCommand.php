<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Command;

use App\Contracts\Entity\Document\FileInterface;
use App\Contracts\Entity\Document\PageInterface;
use App\Contracts\Entity\DocumentInterface;
use App\Doctrine\Entity\DocumentType;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\Uuid;

use function array_map;
use function implode;
use function mb_strtolower;
use function spl_object_id;
use function sprintf;

#[AsCommand(
    name: 'app:document:touch',
    description: 'Create a new page or update its modification date',
)]
final class DocumentTouchCommand extends Command
{
    private const ARG_NAME_TYPE = 'type';
    private const OPTION_NAME_ID = 'id';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ClockInterface $clock,
        private readonly UuidFactory $uuidFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    private function findDocumentById(DocumentType $type, Uuid $id): null | DocumentInterface
    {
        $className = $type->getEntityInterfaceName();

        return $this->entityManager->getRepository($className)->find(id: $id);
    }

    protected function createDocument(DocumentType $type, Uuid $id): DocumentInterface
    {
        $className = $type->getEntityClassName();
        $document = new $className();
        $document->setId(value: $id);

        return $document;
    }

    protected function updateDocument(DocumentInterface $document): void
    {
        $datetimeStr = $this->clock->now()->format(DateTimeInterface::RFC3339_EXTENDED);

        if ($document instanceof PageInterface) {
            $title = sprintf('DUMMY TITLE %s', $datetimeStr);
            $document->setTitle(value: $title);
        }

        if ($document instanceof FileInterface) {
            $filename = sprintf('dummy_filename.%s.txt', $datetimeStr);
            $document->setName(value: $filename);
        }
    }

    protected function configure(): void
    {
        $types = array_map(
            callback: static fn (DocumentType $type) => mb_strtolower(string: $type->name),
            array: DocumentType::cases(),
        );
        $this->addArgument(
            name: self::ARG_NAME_TYPE,
            mode: InputArgument::REQUIRED,
            description: sprintf('Document type [%s]', implode(separator: '/', array: $types)),
        );
        $this->addOption(
            name: self::OPTION_NAME_ID,
            mode: InputOption::VALUE_REQUIRED,
            description: 'Document ID',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(input: $input, output: $output);

        $typeStr = $input->getArgument(name: self::ARG_NAME_TYPE);
        $type = DocumentType::tryFrom(value: $typeStr);

        if (null === $type) {
            $this->logger->error(message: 'Invalid type: {typeStr}', context: ['typeStr' => $typeStr]);

            return Command::INVALID;
        }

        $id = $this->getSelectedId(input: $input);

        if (null !== $id) {
            $this->logger->info(message: 'Searching document by id={id}', context: ['id' => $id]);
            $document = $this->findDocumentById(type: $type, id: $id);

            if (null === $document) {
                $this->logger->error(message: 'Could not find document by id={id}', context: ['id' => $id]);

                return Command::FAILURE;
            }
        } else {
            $id = $this->uuidFactory->create();
            $document = $this->createDocument(type: $type, id: $id);

            $this->logger->debug(
                message: 'Persisting document',
                context: ['oid' => spl_object_id(object: $document), 'id' => $id, 'document' => $document],
            );
            $this->entityManager->persist($document);

            $this->logger->info(
                message: 'Created new document with id={id}',
                context: ['oid' => spl_object_id(object: $document), 'id' => $id, 'document' => $document],
            );
        }

        $this->updateDocument($document);

        $this->logger->debug(
            message: 'Flushing changes to database',
            context: ['oid' => spl_object_id(object: $document), 'id' => $id, 'document' => $document],
        );
        $this->entityManager->flush();

        $this->logger->info(
            message: 'Document touched successfully! {document}',
            context: ['oid' => spl_object_id(object: $document), 'id' => $id, 'document' => $document],
        );

        $io->info('Document touched successfully!');

        return Command::SUCCESS;
    }

    private function getSelectedId(InputInterface $input): null | Uuid
    {
        $idStr = $input->getOption(self::OPTION_NAME_ID);

        return null !== $idStr ? Uuid::fromString($idStr) : null;
    }
}
