<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Service\Parser\ParserInterface;
use App\Contract\Service\Parser\ParserSelectorInterface;
use App\Doctrine\Entity\PageView;
use TypeError;

final class ParserSelector implements ParserSelectorInterface
{
    /** @var ParserInterface[] */
    private array $parsers = [];

    public function __construct(iterable $parsers)
    {
        foreach ($parsers as $parser) {
            if (false === $parser instanceof ParserInterface) {
                throw new TypeError(message: 'Array element does not implement ParserInterface');
            }
            $this->parsers[] = $parser;
        }
    }

    public function findParser(PageView $pageView): ?ParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports(pageView: $pageView)) {
                return $parser;
            }
        }
        return null;
    }
}
