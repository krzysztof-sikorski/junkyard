<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Contracts\Symfony;

enum RouteNames: string
{
    case DOCUMENT = 'app_document';
    case CANONICAL_DOMAIN_REDIRECT = 'app_canonical_domain_redirect';
    case ADMIN_DASHBOARD = 'app_admin';
}
