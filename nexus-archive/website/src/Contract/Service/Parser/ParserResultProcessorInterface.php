<?php

declare(strict_types=1);

namespace App\Contract\Service\Parser;

interface ParserResultProcessorInterface
{
    /**
     * Persist parser result to database
     */
    public function persist(ParserResultInterface $parserResult): void;
}
