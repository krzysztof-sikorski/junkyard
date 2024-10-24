<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Repository\PageViewRepository;
use App\Service\Repository\UserAccessTokenRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:worker:prune-database',
    description: 'Background worker to prune unwanted content from database',
)]
final class WorkerPruneDatabaseCommand extends Command
{
    public function __construct(
        private UserAccessTokenRepository $userAccessTokenRepository,
        private PageViewRepository $pageViewRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userAccessTokenRepository->prune();
        $this->pageViewRepository->prune();

        return Command::SUCCESS;
    }
}
