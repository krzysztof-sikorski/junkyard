<?php

declare(strict_types=1);

namespace App\Contract\Service\Parser;

use App\Doctrine\Entity\PageView;

/**
 * Parser for submitted raw data
 */
interface ParserInterface
{
    /**
     * Check if can parse given object
     */
    public function supports(PageView $pageView): bool;

    /**
     * Parse given object
     */
    public function parse(PageView $pageView): ParserResultInterface;
}
