<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Doctrine\Entity\Document;

use App\Contracts\Entity\Document\PageInterface;
use App\Doctrine\Entity\Document;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Page extends Document implements PageInterface
{
    #[ORM\Column(name: 'page_title', type: Types::TEXT, nullable: true)]
    private null | string $title = null;

    #[ORM\Column(name: 'page_content', type: Types::TEXT, nullable: true)]
    private null | string $content = null;

    public function getTitle(): null | string
    {
        return $this->title;
    }

    public function setTitle(null | string $value): void
    {
        $this->title = $value;
    }

    public function getContent(): null | string
    {
        return $this->content;
    }

    public function setContent(null | string $value): void
    {
        $this->content = $value;
    }
}
