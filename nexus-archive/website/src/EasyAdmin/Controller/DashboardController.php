<?php

declare(strict_types=1);

namespace App\EasyAdmin\Controller;

use App\Contract\Config\AppRoutes;
use App\Doctrine\Entity\PageView;
use App\Doctrine\Entity\User;
use App\Doctrine\Entity\UserAccessToken;
use App\Service\Repository\PageViewRepository;
use App\Service\Repository\UserAccessTokenRepository;
use App\Service\Repository\UserRepository;
use DateTimeImmutable;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

final class DashboardController extends AbstractDashboardController
{
    public const ROUTE_PATH = '/easyadmin';
    public const ROUTE_SECURITY_REGEXP = '^/easyadmin';

    public function __construct(
        private Environment $twigEnvironment,
        private UserRepository $userRepository,
        private UserAccessTokenRepository $userAccessTokenRepository,
        private PageViewRepository $pageViewRepository
    ) {
    }

    #[Route(self::ROUTE_PATH, name: AppRoutes::EASYADMIN)]
    public function index(): Response
    {
        $userTotalCount = $this->userRepository->getTotalCount();
        $tokenTotalCount = $this->userAccessTokenRepository->getTotalCount();
        $dataTotalCount = $this->pageViewRepository->getTotalCount();
        $dataWeeklyCount = $this->pageViewRepository->getPartialCount(
            new DateTimeImmutable(datetime: 'midnight UTC - 1 week'),
            new DateTimeImmutable(datetime: 'midnight UTC')
        );
        $dataDailyCount = $this->pageViewRepository->getPartialCount(
            new DateTimeImmutable(datetime: 'midnight UTC - 1 day'),
            new DateTimeImmutable(datetime: 'midnight UTC')
        );

        $context = [
            'user_total_count' => $userTotalCount,
            'token_total_count' => $tokenTotalCount,
            'page_view_total_count' => $dataTotalCount,
            'page_view_weekly_count' => $dataWeeklyCount,
            'page_view_daily_count' => $dataDailyCount,
        ];

        $content = $this->twigEnvironment->render(name: 'easyadmin/dashboard/index.html.twig', context: $context);

        return new Response(content: $content);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle(title: 'Nexus Archive')
            ->renderContentMaximized()
            ->generateRelativeUrls();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(label: 'Dashboard', icon: 'fa fa-home');
        yield MenuItem::section(label: 'Page views');
        yield MenuItem::linkToCrud(label: 'Page views', icon: 'fas fa-list', entityFqcn: PageView::class);
        yield MenuItem::section(label: 'Users');
        yield MenuItem::linkToCrud(label: 'Users', icon: 'fas fa-users', entityFqcn: User::class);
        yield MenuItem::linkToCrud(
            label: 'User access tokens',
            icon: 'fas fa-users',
            entityFqcn: UserAccessToken::class
        );
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu(user: $user)
            ->displayUserAvatar(isDisplayed: false);
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setTimezone(timezoneId: 'UTC')
            ->setDateTimeFormat(dateFormatOrPattern: 'YYYY-MM-dd HH:mm:ss z')
            ->showEntityActionsInlined();
    }
}
