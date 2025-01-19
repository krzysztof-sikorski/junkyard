<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Config\AppSerializationGroups;
use App\Doctrine\Entity\User;
use App\Service\Repository\UserAccessTokenRepository;
use App\Service\Repository\UserRepository;
use DateInterval;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function sprintf;

#[AsCommand(
    name: 'app:user:create-access-token',
    description: 'Creates a new user access token',
)]
final class UserCreateAccessTokenCommand extends BaseCommand
{
    private const ARGUMENT_NAME_OWNER = 'owner';
    private const ARGUMENT_NAME_DURATION = 'duration';
    private const DEFAULT_DURATION = '1 month';

    private ?User $owner = null;
    private ?string $durationStr = null;
    private ?DateInterval $duration = null;

    public function __construct(
        private UserRepository $userRepository,
        private UserAccessTokenRepository $userAccessTokenRepository,
        SerializerInterface $serializer,
    ) {
        parent::__construct(serializer: $serializer);
    }

    protected function configure(): void
    {
        $this->addOption(
            name: self::ARGUMENT_NAME_OWNER,
            shortcut: null,
            mode: InputOption::VALUE_REQUIRED,
            description: 'Owner\'s username',
            default: null
        );

        $this->addOption(
            name: self::ARGUMENT_NAME_DURATION,
            shortcut: null,
            mode: InputOption::VALUE_REQUIRED,
            description: 'Token duration (how long it is valid)',
            default: self::DEFAULT_DURATION
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = $this->createSymfonyStyle(input: $input, output: $output);

        $helper = $this->getQuestionHelper();
        $question = new Question(question: 'Owner\'s username?', default: null);

        $ownerUsername = $input->getOption(name: self::ARGUMENT_NAME_OWNER);
        while (true) {
            while (null === $ownerUsername) {
                $ownerUsername = $helper->ask(input: $input, output: $output, question: $question);
            }
            $this->owner = $this->userRepository->findByUsername(username: $ownerUsername);
            if (null !== $this->owner) {
                break;
            } else {
                $io->error(message: sprintf('User with username=%s does not exist!', $ownerUsername));
                $ownerUsername = null;
            }
        }

        $questionText = sprintf('Token duration (default: %s)?', self::DEFAULT_DURATION);
        $question = new Question(question: $questionText, default: self::DEFAULT_DURATION);

        $this->durationStr = $input->getOption(name: self::ARGUMENT_NAME_DURATION);
        while (true) {
            if (null === $this->durationStr) {
                $this->durationStr = $helper->ask(input: $input, output: $output, question: $question);
            }
            try {
                $duration = DateInterval::createFromDateString(datetime: $this->durationStr);
            } catch (Exception $e) {
                $duration = null;
            }
            if (false !== $duration instanceof DateInterval) {
                $this->duration = $duration;
                break;
            } else {
                $io->error(message: sprintf('Invalid duration: %s', $this->durationStr));
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->createSymfonyStyle(input: $input, output: $output);

        $this->displayValue(io: $io, label: 'Selected owner', value: $this->owner);

        $parsedDuration = $this->serializer->serialize(data: $this->duration, format: JsonEncoder::FORMAT);
        $message = sprintf('Selected duration: %s (parsed as: %s)', $this->durationStr, $parsedDuration);
        $io->info(message: $message);

        $token = $this->userAccessTokenRepository->create(owner: $this->owner, duration: $this->duration);

        $this->displayValue(
            io: $io,
            label: 'Created token',
            value: $token,
            serializationGroup: AppSerializationGroups::ENTITY_USER_ACCESS_TOKEN
        );

        return Command::SUCCESS;
    }
}
