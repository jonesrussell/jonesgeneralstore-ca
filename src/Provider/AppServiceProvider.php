<?php

declare(strict_types=1);

namespace App\Provider;

use App\Controller\HomeController;
use App\Controller\DrawController;
use App\Seo\HomepageMetadataBuilder;
use Symfony\Component\HttpFoundation\Response;
use Waaseyaa\Foundation\Diagnostic\CleanUrlProbe;
use Waaseyaa\Foundation\ServiceProvider\ServiceProvider;
use Waaseyaa\Routing\RouteBuilder;
use Waaseyaa\Routing\WaaseyaaRouter;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->singleton(DrawController::class, fn(): DrawController => new DrawController(
            new HomepageMetadataBuilder(
                origin: (string) ($this->config['seo']['canonical_origin'] ?? ''),
                facebookUrl: (string) ($this->config['seo']['facebook_url'] ?? ''),
            ),
        ));
        $this->singleton(HomeController::class, fn(): HomeController => new HomeController(
            new HomepageMetadataBuilder(
                origin: (string) ($this->config['seo']['canonical_origin'] ?? ''),
                facebookUrl: (string) ($this->config['seo']['facebook_url'] ?? ''),
            ),
        ));
    }

    public function routes(WaaseyaaRouter $router, ?\Waaseyaa\Entity\EntityTypeManager $entityTypeManager = null): void
    {
        $router->addRoute(
            'jgs.september_draw',
            RouteBuilder::create('/september-draw')
                ->controller([DrawController::class, 'index'])
                ->allowAll()
                ->methods('GET')
                ->priority(20)
                ->build(),
        );
        $router->addRoute(
            'home',
            RouteBuilder::create('/')
                ->controller([HomeController::class, 'index'])
                ->allowAll()
                ->methods('GET')
                ->build(),
        );

        $router->addRoute(
            'jgs.seo.robots_txt',
            RouteBuilder::create('/robots.txt')
                ->controller([HomeController::class, 'robotsTxt'])
                ->allowAll()
                ->methods('GET')
                ->priority(20)
                ->build(),
        );

        $router->addRoute(
            'jgs.seo.sitemap_xml',
            RouteBuilder::create('/sitemap.xml')
                ->controller([HomeController::class, 'sitemapXml'])
                ->allowAll()
                ->methods('GET')
                ->priority(20)
                ->build(),
        );

        $router->addRoute(
            'waaseyaa.clean_url_probe',
            RouteBuilder::create(CleanUrlProbe::PATH)
                ->controller(static fn() => new Response(
                    CleanUrlProbe::SENTINEL,
                    200,
                    ['Content-Type' => 'text/plain; charset=UTF-8'],
                ))
                ->allowAll()
                ->methods('GET')
                ->build(),
        );
    }
}
