<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Doctrine\EventSubscriber;

use App\Contracts\Entity\DocumentInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

use function spl_object_id;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final readonly class DocumentSubscriber
{
    public function __construct(
        private LoggerInterface $logger,
        private ClockInterface $clock,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof DocumentInterface) {
            $createdAt = $this->clock->now();
            $this->logger->debug(
                message: '[{method}] Patching document: OID={oid}, createdAt={createdAt}',
                context: [
                    'method' => __FUNCTION__,
                    'oid' => spl_object_id(object: $object),
                    'createdAt' => $createdAt,
                ],
            );
            $object->setCreatedAt(value: $createdAt);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof DocumentInterface) {
            $updatedAt = $this->clock->now();
            $this->logger->debug(
                message: '[{method}] Patching document: OID={oid}, createdAt={updatedAt}',
                context: [
                    'method' => __FUNCTION__,
                    'oid' => spl_object_id(object: $object),
                    'id' => $object->getId(),
                    'updatedAt' => $updatedAt,
                ],
            );
            $object->setUpdatedAt(value: $updatedAt);
        }
    }
}
