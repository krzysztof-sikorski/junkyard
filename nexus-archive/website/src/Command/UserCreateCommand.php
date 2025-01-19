<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Config\AppParameters;
use App\Contract\UserRoles;
use App\Service\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Serializer\SerializerInterface;

use function sprintf;

#[AsCommand(
    name: 'app:user:create',
    description: 'Creates a new user',
)]
final class UserCreateCommand extends BaseCommand
{
    private const ARGUMENT_NAME_USERNAME = 'username';
    private const ARGUMENT_NAME_ROLE = 'role';

    private ?string $username = null;
    private ?string $plaintextPassword = null;
    private array $roles = [];

    public function __construct(
        private UserRepository $userRepository,
        SerializerInterface $serializer,
    ) {
        parent::__construct(serializer: $serializer);
    }

    protected function configure(): void
    {
        $this->addOption(
            name: self::ARGUMENT_NAME_USERNAME,
            shortcut: null,
            mode: InputOption::VALUE_REQUIRED,
            description: 'Username',
            default: null
        );
        $this->addOption(
            name: self::ARGUMENT_NAME_ROLE,
            shortcut: null,
            mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            description: sprintf('Additional roles (%s is always given)', AppParameters::SECURITY_DEFAULT_ROLE),
            default: []
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = $this->createSymfonyStyle(input: $input, output: $output);

        $this->username = $input->getOption(name: self::ARGUMENT_NAME_USERNAME);

        while (true) {
            if (null === $this->username) {
                $this->askForUsername(input: $input, output: $output);
            }
            if (null !== $this->userRepository->findByUsername(username: $this->username)) {
                $io->error(message: sprintf('User with username=%s already exists!', $this->username));
                $this->username = null;
            } else {
                break;
            }
        }

        $this->askForPassword(input: $input, output: $output);

        $this->roles = $input->getOption(name: self::ARGUMENT_NAME_ROLE);
        $this->addRole(role: AppParameters::SECURITY_DEFAULT_ROLE);
        $this->askForAdditionalRoles(input: $input, output: $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->createSymfonyStyle(input: $input, output: $output);

        $this->displayValue(io: $io, label: 'Selected username', value: $this->username);
        $this->displayValue(io: $io, label: 'Selected password', value: $this->plaintextPassword);
        $this->displayValue(io: $io, label: 'Selected roles', value: $this->roles);

        $user = $this->userRepository->create(
            username: $this->username,
            plaintextPassword: $this->plaintextPassword,
            roles: $this->roles
        );

        $this->displayValue(io: $io, label: 'Created user', value: $user);

        return Command::SUCCESS;
    }

    private function askForUsername(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getQuestionHelper();
        $question = new Question(question: 'Username?');
        do {
            $this->username = $helper->ask(input: $input, output: $output, question: $question);
        } while (null === $this->username);
    }

    private function askForPassword(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getQuestionHelper();
        $question = new Question(question: 'Password?');
        do {
            $this->plaintextPassword = $helper->ask(input: $input, output: $output, question: $question);
        } while (null === $this->plaintextPassword);
    }

    private function addRole(string $role): void
    {
        $this->roles[] = $role;
        $this->roles = UserRoles::normalize(roles: $this->roles);
    }

    private function askForAdditionalRoles(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getQuestionHelper();
        $question = new Question(question: 'Additional role? (empty to finish adding)');

        $io = $this->createSymfonyStyle(input: $input, output: $output);
        while (true) {
            $this->displayValue(io: $io, label: 'Selected roles', value: $this->roles);
            $role = $helper->ask(input: $input, output: $output, question: $question);
            if (null === $role) {
                break;
            }
            if (UserRoles::isValidRole(role: $role)) {
                $this->addRole(role: $role);
            } else {
                $io->error(message: sprintf('Invalid role name: %s', $role));
            }
        }
    }
}
