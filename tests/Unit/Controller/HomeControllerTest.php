<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\HomeController;
use App\Seo\HomepageMetadataBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HomeControllerTest extends TestCase
{
    #[Test]
    public function it_renders_the_jones_general_store_homepage(): void
    {
        $response = $this->controller()->index();
        $html = (string) $response->getContent();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Jones General Store', $html);
        self::assertStringContainsString('4027 Espaniel Street', $html);
        self::assertStringContainsString('(705) 865-1633', $html);
        self::assertStringContainsString('9 AM–10 PM', $html);
        self::assertStringContainsString('Cold drinks &amp; snacks', $html);
        self::assertStringNotContainsString('hot lunches', strtolower($html));
        self::assertStringContainsString('Beads &amp; local finds', $html);
        self::assertStringContainsString('Family owned since 1984', $html);
        self::assertStringContainsString('gassed up.', $html);
        self::assertStringNotContainsString('brightened up.', $html);
        self::assertStringContainsString('title="Map showing Jones General Store at 4027 Espaniel Street"', $html);
        self::assertStringContainsString('Open map &amp; get directions', $html);
        self::assertStringNotContainsString('>Staff</a>', $html);
        self::assertStringNotContainsString('Waaseyaa</title>', $html);
        self::assertStringContainsString('<link rel="canonical" href="https://jonesgeneralstore.ca/">', $html);
        self::assertStringContainsString('<meta property="og:image" content="https://jonesgeneralstore.ca/og.png">', $html);
        self::assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        self::assertStringContainsString('application/ld+json', $html);
        self::assertStringContainsString('ConvenienceStore', $html);
    }

    #[Test]
    public function it_serves_crawler_files_with_the_canonical_homepage(): void
    {
        $controller = $this->controller();
        $robots = $controller->robotsTxt();
        $sitemap = $controller->sitemapXml();

        self::assertSame('text/plain; charset=UTF-8', $robots->headers->get('Content-Type'));
        self::assertStringContainsString('Sitemap: https://jonesgeneralstore.ca/sitemap.xml', (string) $robots->getContent());
        self::assertSame('application/xml; charset=UTF-8', $sitemap->headers->get('Content-Type'));
        self::assertStringContainsString('<loc>https://jonesgeneralstore.ca/</loc>', (string) $sitemap->getContent());
    }

    private function controller(): HomeController
    {
        return new HomeController(new HomepageMetadataBuilder(
            origin: 'https://jonesgeneralstore.ca',
            facebookUrl: 'https://www.facebook.com/jones.general.store.sagamok/',
        ));
    }
}
