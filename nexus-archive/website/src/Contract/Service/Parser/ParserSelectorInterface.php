<?php

declare(strict_types=1);

namespace App\Contract\Service\Parser;

use App\Doctrine\Entity\PageView;

interface ParserSelectorInterface
{
    /**
     * Find parser that can handle given object
     */
    public function findParser(PageView $pageView): ?ParserInterface;
}
