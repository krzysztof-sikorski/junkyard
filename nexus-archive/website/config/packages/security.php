<?php

declare(strict_types=1);

use App\Contract\Config\AppParameters;
use App\Contract\Config\AppRoutes;
use App\Contract\UserRoles;
use App\Doctrine\Entity\User;
use App\EasyAdmin\Controller\DashboardController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $securityConfig, ContainerConfigurator $containerConfigurator) {
    $securityConfig->enableAuthenticatorManager(value: true);

    $authPasswordHasherConfig = $securityConfig->passwordHasher(class: PasswordAuthenticatedUserInterface::class);
    $authPasswordHasherConfig->algorithm(value: 'auto');

    $userPasswordHasherConfig = $securityConfig->passwordHasher(class: User::class);
    $userPasswordHasherConfig->algorithm(value: 'auto');

    $securityConfig->passwordHasher(class: User::class)->algorithm(value: 'auto');

    $entityConfig = $securityConfig->provider(name: AppParameters::SECURITY_USER_PROVIDER_NAME)->entity();
    $entityConfig->class(User::class);
    $entityConfig->property(value: AppParameters::SECURITY_USER_ENTITY_ID_FIELD);

    $firewallConfig = $securityConfig->firewall(name: 'dev');
    $firewallConfig->pattern(value: '^/(_(profiler|wdt)|css|images|js)/');
    $firewallConfig->security(value: false);

    $firewallConfig = $securityConfig->firewall(name: 'main');
    $firewallConfig->lazy(value: true);
    $firewallConfig->provider(value: AppParameters::SECURITY_USER_PROVIDER_NAME);

    $formLoginConfig = $firewallConfig->formLogin();
    $formLoginConfig->loginPath(value: AppRoutes::LOGIN);
    $formLoginConfig->checkPath(value: AppRoutes::LOGIN);
    $formLoginConfig->enableCsrf(value: true);
    $formLoginConfig->defaultTargetPath(value: AppRoutes::HOME);
    $formLoginConfig->alwaysUseDefaultTargetPath(value: true);

    $firewallConfig->loginThrottling(); // enable with default values

    $firewallConfig->logout()->path(value: AppRoutes::LOGOUT);

    $accessControlConfig = $securityConfig->accessControl();
    $accessControlConfig->path(value: DashboardController::ROUTE_SECURITY_REGEXP);
    $accessControlConfig->roles(value: [UserRoles::ROLE_ADMIN]);

    if ('test' === $containerConfigurator->env()) {
        // By default, password hashers are resource intensive and take time. This is
        // important to generate secure password hashes. In tests however, secure hashes
        // are not important, waste resources and increase test times. The following
        // reduces the work factor to the lowest possible values.
        $authPasswordHasherConfig->algorithm(value: 'auto');
        $authPasswordHasherConfig->cost(value: 4); // Lowest possible value for bcrypt
        $authPasswordHasherConfig->timeCost(value: 3); // Lowest possible value for argon
        $authPasswordHasherConfig->memoryCost(value: 10); // Lowest possible value for argon
    }
};
