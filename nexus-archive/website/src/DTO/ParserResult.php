<?php

declare(strict_types=1);

namespace App\DTO;

use App\Contract\Entity\Nexus\GamePeriodInterface;
use App\Contract\Entity\Nexus\LeaderboardInterface;
use App\Contract\Service\Parser\ParserResultInterface;
use Throwable;

use function count;
use function get_class;
use function sprintf;

final class ParserResult implements ParserResultInterface
{
    private ?array $errors = null;
    private ?GamePeriodInterface $gamePeriod = null;
    private ?LeaderboardInterface $leaderboard = null;

    public function hasErrors(): bool
    {
        return null !== $this->errors && count($this->errors) > 0;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function addError(string|Throwable $error): void
    {
        if (null === $this->errors) {
            $this->errors = [];
        }
        if ($error instanceof Throwable) {
            $errorStr = sprintf(
                '%s [class=%s, file=%s, line=%d]',
                $error->getMessage(),
                get_class(object: $error),
                $error->getFile(),
                $error->getLine(),
            );
        } else {
            $errorStr = $error;
        }
        $this->errors[] = $errorStr;
    }

    public function getGamePeriod(): ?GamePeriodInterface
    {
        return $this->gamePeriod;
    }

    public function setGamePeriod(GamePeriodInterface $gamePeriod): void
    {
        $this->gamePeriod = $gamePeriod;
    }

    public function getLeaderboard(): ?LeaderboardInterface
    {
        return $this->leaderboard;
    }

    public function setLeaderboard(?LeaderboardInterface $leaderboard): void
    {
        $this->leaderboard = $leaderboard;
    }
}
