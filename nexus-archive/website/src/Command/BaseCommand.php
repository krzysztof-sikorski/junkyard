<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Config\AppSerializationGroups;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

use function sprintf;

abstract class BaseCommand extends Command
{
    public function __construct(protected SerializerInterface $serializer)
    {
        parent::__construct();
    }

    protected function createSymfonyStyle(InputInterface $input, OutputInterface $output): SymfonyStyle
    {
        return new SymfonyStyle(input: $input, output: $output);
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper(name: 'question');
    }

    protected function displayValue(
        SymfonyStyle $io,
        string $label,
        mixed $value,
        ?string $serializationGroup = null
    ): void {
        $context = [];
        if (null !== $serializationGroup) {
            $context[ObjectNormalizer::GROUPS] = [
                AppSerializationGroups::DEFAULT,
                $serializationGroup,
            ];
        }
        $serializedValue = $this->serializer->serialize(data: $value, format: JsonEncoder::FORMAT, context: $context);
        $io->info(message: sprintf('%s: %s', $label, $serializedValue));
    }
}
