<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Entity\Nexus\GamePeriodIdEnum;
use App\Doctrine\Entity\Nexus\GamePeriod;
use App\Doctrine\Entity\Nexus\Leaderboard;
use App\Doctrine\Entity\Nexus\LeaderboardEntry;
use App\Service\Repository\Nexus\GamePeriodRepository;
use App\Service\Repository\Nexus\LeaderboardRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Serializer\SerializerInterface;

use function array_key_exists;
use function intval;
use function is_numeric;
use function max;
use function mb_convert_case;
use function mb_strlen;
use function printf;
use function sprintf;

use const MB_CASE_TITLE;
use const PHP_EOL;

#[AsCommand(
    name: 'app:export:leaderboards',
    description: 'Export leaderboards into a file',
)]
final class ExportLeaderboardsCommand extends BaseCommand
{
    private const OPTION_NAME_GAME_PERIOD_ID = 'period';
    private const TABLE_HEADER_CHARACTER = 'Character';
    private const ENCODING_UTF8 = 'UTF-8';

    private const BREATH_4_NAME_REPLACEMENTS = [
        "I\u{00c3}\u{00af}\u{00c2}\u{00bf}\u{00c2}\u{00bd}unn" => "I\u{00f0}unn",
        "J\u{00c3}\u{00af}\u{00c2}\u{00bf}\u{00c2}\u{00bd}stein Bever" => "J\u{00f8}stein Bever",
        "Mockfj\u{00c3}\u{00af}\u{00c2}\u{00bf}\u{00c2}\u{00bd}rdsvapnet" => "Mockfj\u{00e4}rdsvapnet",
    ];

    private ?GamePeriod $gamePeriod = null;

    public function __construct(
        private GamePeriodRepository $gamePeriodRepository,
        private LeaderboardRepository $leaderboardRepository,
        SerializerInterface $serializer,
    ) {
        parent::__construct(serializer: $serializer);
    }

    protected function configure(): void
    {
        $this->addOption(
            name: self::OPTION_NAME_GAME_PERIOD_ID,
            mode: InputOption::VALUE_REQUIRED,
            description: 'Game period ID',
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = $this->createSymfonyStyle(input: $input, output: $output);
        $helper = $this->getQuestionHelper();
        $question = new Question(question: 'Input game period ID:');

        $inputStr = $input->getOption(name: self::OPTION_NAME_GAME_PERIOD_ID);
        $gamePeriodId = is_numeric($inputStr) ? intval($inputStr) : null;

        while (true) {
            if (null === $gamePeriodId) {
                do {
                    $inputStr = $helper->ask(input: $input, output: $output, question: $question);
                    $gamePeriodId = is_numeric($inputStr) ? intval($inputStr) : null;
                } while (null === $gamePeriodId);
            }
            $this->gamePeriod = $this->gamePeriodRepository->findById(id: $gamePeriodId);
            if (null === $this->gamePeriod) {
                $io->error(message: sprintf('Game period with id=%s does not exists!', $inputStr));
                $gamePeriodId = null;
            } else {
                break;
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->createSymfonyStyle(input: $input, output: $output);

        $this->displayValue(io: $io, label: 'Selected game period', value: $this->gamePeriod->getName());

        $leaderboards = $this->leaderboardRepository->findByGamePeriod(gamePeriod: $this->gamePeriod);

        /** @var Leaderboard $leaderboard */
        foreach ($leaderboards as $leaderboard) {
            echo PHP_EOL, PHP_EOL, PHP_EOL;
            $category = $leaderboard->getCategory();
            printf(
                '[b]%s (%s)[/b]',
                $category->getName(),
                mb_convert_case(string: $category->getType(), mode: MB_CASE_TITLE, encoding: self::ENCODING_UTF8),
            );
            echo PHP_EOL, '[code]', PHP_EOL;
            $headerCharacterLength = mb_strlen(string: self::TABLE_HEADER_CHARACTER, encoding: self::ENCODING_UTF8);
            $characterColumnWidth = $headerCharacterLength;
            $entries = [];
            /** @var LeaderboardEntry $entry */
            foreach ($leaderboard->getEntries() as $entry) {
                $characterName = $entry->getCharacterName();
                if (
                    GamePeriodIdEnum::BREATH_4 === $this->gamePeriod->getId()
                    && array_key_exists(key: $characterName, array: self::BREATH_4_NAME_REPLACEMENTS)
                ) {
                    $characterName = self::BREATH_4_NAME_REPLACEMENTS[$characterName];
                }
                $characterNameLength = mb_strlen(string: $characterName, encoding: self::ENCODING_UTF8);
                $characterColumnWidth = max($characterColumnWidth, $characterNameLength + 4);
                $entries[] = [
                    'position' => $entry->getPosition(),
                    'characterName' => $characterName,
                    'characterNameLength' => $characterNameLength,
                    'score' => $entry->getScore(),
                ];
            }
            $padding = str_repeat(string: ' ', times: $characterColumnWidth - $headerCharacterLength);
            printf('%s%s %s', self::TABLE_HEADER_CHARACTER, $padding, $category->getScoreLabel());
            echo PHP_EOL;
            /** @var LeaderboardEntry $entry */
            foreach ($entries as $entry) {
                $characterStr = sprintf('%d) %s', $entry['position'], $entry['characterName']);
                $characterStrLength = mb_strlen(string: $characterStr, encoding: self::ENCODING_UTF8);
                $padding = str_repeat(string: ' ', times: $characterColumnWidth - $characterStrLength);
                printf('%s%s %s', $characterStr, $padding, $entry['score']);
                echo PHP_EOL;
            }
            echo '[/code]', PHP_EOL;
        }

        return Command::SUCCESS;
    }
}
