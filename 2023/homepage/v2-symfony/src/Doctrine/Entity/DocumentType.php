<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Contracts\Entity\Document\FileInterface;
use App\Contracts\Entity\Document\PageInterface;
use App\Contracts\Entity\Document\PointerInterface;
use App\Doctrine\Entity\Document\File;
use App\Doctrine\Entity\Document\Page;
use App\Doctrine\Entity\Document\Pointer;

enum DocumentType: string
{
    case PAGE = 'page';
    case FILE = 'file';
    case POINTER = 'pointer';

    public function getEntityInterfaceName(): string
    {
        return match ($this) {
            self::PAGE => PageInterface::class,
            self::FILE => FileInterface::class,
            self::POINTER => PointerInterface::class,
        };
    }

    public function getEntityClassName(): string
    {
        return match ($this) {
            self::PAGE => Page::class,
            self::FILE => File::class,
            self::POINTER => Pointer::class,
        };
    }
}
