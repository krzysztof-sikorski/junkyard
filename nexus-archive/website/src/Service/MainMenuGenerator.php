<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Config\AppRoutes;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MainMenuGenerator
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getMenu(): array
    {
        return [
            [
                'name' => 'Home',
                'url' => $this->urlGenerator->generate(name: AppRoutes::HOME),
                'external' => false,
            ],
            [
                'name' => 'Leaderboards',
                'url' => $this->urlGenerator->generate(name: AppRoutes::LEADERBOARDS),
                'external' => false,
            ],
            [
                'name' => 'About website',
                'url' => $this->urlGenerator->generate(name: AppRoutes::ABOUT),
                'external' => false,
            ],
            [
                'name' => 'Back to Nexus Clash',
                'url' => 'https://www.nexusclash.com/',
                'external' => true,
            ],
        ];
    }
}
