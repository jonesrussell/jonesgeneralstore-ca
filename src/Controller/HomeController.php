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
        $html = (new StorefrontRenderer())->render('home', $this->metadata->head(), $this->metadata->structuredData());

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
            new SitemapUrl(
                loc: $this->metadata->canonicalUrl('september-draw'),
                changefreq: 'weekly',
                priority: 0.7,
            ),
        ]);

        return new Response($body, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
