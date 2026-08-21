<?php

declare(strict_types=1);

namespace App\Controller;

use App\Seo\HomepageMetadataBuilder;
use Symfony\Component\HttpFoundation\Response;
use Waaseyaa\Seo\RobotsTxtGenerator;
use Waaseyaa\Seo\SitemapGenerator;
use Waaseyaa\Seo\SitemapUrl;

final class HomeController
{
    private readonly HomepageMetadataBuilder $metadata;
    private readonly RobotsTxtGenerator $robots;
    private readonly SitemapGenerator $sitemap;

    public function __construct(
        HomepageMetadataBuilder $metadata,
        ?RobotsTxtGenerator $robots = null,
        ?SitemapGenerator $sitemap = null,
    ) {
        $this->metadata = $metadata;
        $this->robots = $robots ?? new RobotsTxtGenerator();
        $this->sitemap = $sitemap ?? new SitemapGenerator();
    }

    public function index(): Response
    {
        $template = dirname(__DIR__, 2) . '/templates/home.html.twig';
        $html = (string) file_get_contents($template);
        $html = str_replace('<!-- jgs:seo-head -->', $this->metadata->head(), $html);
        $html = str_replace('<!-- jgs:structured-data -->', $this->metadata->structuredData(), $html);

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function robotsTxt(): Response
    {
        $body = $this->robots->toText(
            sitemapUrl: $this->metadata->canonicalUrl('sitemap.xml'),
            disallowPaths: [],
        );

        return new Response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemapXml(): Response
    {
        $body = $this->sitemap->toXml([
            new SitemapUrl(
                loc: $this->metadata->canonicalUrl(),
                changefreq: 'weekly',
                priority: 1.0,
            ),
        ]);

        return new Response($body, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
