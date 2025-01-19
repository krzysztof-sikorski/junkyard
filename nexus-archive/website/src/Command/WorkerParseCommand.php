<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Service\ClockInterface;
use App\Service\PageViewProcessor;
use DateInterval;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function intval;
use function is_numeric;
use function sprintf;

#[AsCommand(
    name: 'app:worker:parse',
    description: 'Background worker to parse submitted raw data',
)]
final class WorkerParseCommand extends Command
{
    private const OPTION_NAME_BATCH_SIZE = 'batch-size';
    private const OPTION_NAME_MAX_ITERATIONS = 'max-iterations';
    private const OPTION_NAME_MAX_DURATION = 'max-duration';

    public function __construct(
        private ClockInterface $clock,
        private PageViewProcessor $pageViewProcessor,
        private int $batchSize,
        private int $maxIterations,
        private string $maxDurationStr,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp(
            help: <<<'HELP'
            Gradually parses batches of submitted raw data, starting from the oldest unparsed records.
            Processing stops after enough rows are processed or too much time elapses.

            This command is intended to be executed periodically as a background process,
            for example by cron job or systemd timer.

            The option "max-duration" only accepts a strict set of relative date formats,
            see <https://www.php.net/manual/en/datetime.formats.relative.php> for details.
            HELP
        );

        $this->addOption(
            name: self::OPTION_NAME_BATCH_SIZE,
            mode: InputOption::VALUE_OPTIONAL,
            description: 'Number of records processed in single iteration',
            default: $this->batchSize
        );

        $this->addOption(
            name: self::OPTION_NAME_MAX_ITERATIONS,
            mode: InputOption::VALUE_OPTIONAL,
            description: 'Maximum number of iterations',
            default: $this->maxIterations
        );

        $this->addOption(
            name: self::OPTION_NAME_MAX_DURATION,
            mode: InputOption::VALUE_OPTIONAL,
            description: 'Maximum duration of execution',
            default: $this->maxDurationStr
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setBatchSize(input: $input);
        $this->setMaxBatches(input: $input);
        $this->setMaxDurationStr(input: $input);

        try {
            $maxDuration = DateInterval::createFromDateString(datetime: $this->maxDurationStr);
        } catch (Throwable) {
            $io = new SymfonyStyle(input: $input, output: $output);
            $io->error(sprintf('Invalid maximum duration: %s', $this->maxDurationStr));
            return Command::FAILURE;
        }

        $endDateTime = (clone($this->clock->getCurrentDateTime()))->add(interval: $maxDuration);

        for ($iteration = 0; $iteration < $this->maxIterations; $iteration++) {
            $this->pageViewProcessor->process(batchSize: $this->batchSize);
            if ($this->clock->getCurrentDateTime() >= $endDateTime) {
                break;
            }
        }

        return Command::SUCCESS;
    }

    private function setBatchSize(InputInterface $input): void
    {
        if ($input->hasOption(name: self::OPTION_NAME_BATCH_SIZE)) {
            $valueStr = $input->getOption(name: self::OPTION_NAME_BATCH_SIZE);
            if (is_numeric(value: $valueStr)) {
                $this->batchSize = intval(value: $valueStr);
            }
        }
    }

    private function setMaxBatches(InputInterface $input): void
    {
        if ($input->hasOption(name: self::OPTION_NAME_MAX_ITERATIONS)) {
            $valueStr = $input->getOption(name: self::OPTION_NAME_MAX_ITERATIONS);
            if (is_numeric(value: $valueStr)) {
                $this->maxIterations = intval(value: $valueStr);
            }
        }
    }

    private function setMaxDurationStr(InputInterface $input): void
    {
        if ($input->hasOption(name: self::OPTION_NAME_MAX_DURATION)) {
            $this->maxDurationStr = $input->getOption(name: self::OPTION_NAME_MAX_DURATION);
        }
    }
}
