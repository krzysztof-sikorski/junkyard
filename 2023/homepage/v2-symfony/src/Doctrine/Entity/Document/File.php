<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Doctrine\Entity\Document;

use App\Contracts\Entity\Document\FileInterface;
use App\Doctrine\Entity\Document;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class File extends Document implements FileInterface
{
    #[ORM\Column(name: 'file_filename', type: Types::TEXT, nullable: true)]
    private null | string $name = null;

    #[ORM\Column(name: 'file_storage_path', type: Types::TEXT, nullable: true)]
    private null | string $storagePath = null;

    public function getName(): null | string
    {
        return $this->name;
    }

    public function setName(null | string $value): void
    {
        $this->name = $value;
    }

    public function getStoragePath(): null | string
    {
        return $this->storagePath;
    }

    public function setStoragePath(null | string $value): void
    {
        $this->storagePath = $value;
    }
}
