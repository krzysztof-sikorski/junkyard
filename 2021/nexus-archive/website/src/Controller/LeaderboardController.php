<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Config\AppRoutes;
use App\Service\Repository\Nexus\GamePeriodRepository;
use App\Service\Repository\Nexus\LeaderboardRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

use function array_key_exists;
use function intval;
use function sprintf;

final class LeaderboardController
{
    public function __construct(
        private Environment $twigEnvironment,
        private GamePeriodRepository $gamePeriodRepository,
        private LeaderboardRepository $leaderboardRepository,
    ) {
    }

    #[Route(path: '/leaderboards', name: AppRoutes::LEADERBOARDS, methods: [Request::METHOD_GET])]
    public function index(
        Request $request
    ): Response {
        $gamePeriods = $this->gamePeriodRepository->findAll();

        $defaultGamePeriodId = null;
        $optionsGamePeriods = [];
        foreach ($gamePeriods as $gamePeriod) {
            $id = $gamePeriod->getId();
            $optionsGamePeriods[$id] = $gamePeriod;
            if ($gamePeriod->isCurrent()) {
                $defaultGamePeriodId = $id;
            }
        }

        $selectedGamePeriodStr = $request->get(key: 'gamePeriod');
        if (null !== $selectedGamePeriodStr) {
            $selectedGamePeriodId = intval(value: $selectedGamePeriodStr);
            if (false === array_key_exists(key: $selectedGamePeriodId, array: $optionsGamePeriods)) {
                throw new NotFoundHttpException(
                    message: sprintf('Invalid ID of game period: %d', $selectedGamePeriodId)
                );
            }
        } else {
            $selectedGamePeriodId = $defaultGamePeriodId;
        }

        $selectedGamePeriod = $optionsGamePeriods[$selectedGamePeriodId];
        $leaderboards = $this->leaderboardRepository->findByGamePeriod(gamePeriod: $selectedGamePeriod);

        $context = [
            'optionsGamePeriods' => $optionsGamePeriods,
            'selectedGamePeriodId' => $selectedGamePeriodId,
            'leaderboards' => $leaderboards,
        ];
        $responseBody = $this->twigEnvironment->render(name: 'leaderboards/index.html.twig', context: $context);

        return new Response(content: $responseBody);
    }

}
