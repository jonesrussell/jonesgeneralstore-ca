<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\DrawController;
use App\Controller\HomeController;
use App\Controller\StorefrontRenderer;
use App\Seo\HomepageMetadataBuilder;
use PHPUnit\Framework\TestCase;

final class DrawControllerTest extends TestCase
{
    private function metadata(): HomepageMetadataBuilder
    {
        return new HomepageMetadataBuilder('https://jonesgeneralstore.ca', 'https://www.facebook.com/jones.general.store.sagamok/');
    }

    public function testPublicDrawPageHasSharedShellAndPageSpecificMetadata(): void
    {
        $response = (new DrawController($this->metadata()))->index();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $html = (string) $response->getContent();
        foreach (['class="site-header"', 'class="site-footer"', 'href="/#store"',
            'https://jonesgeneralstore.ca/september-draw', 'og:title', 'twitter:title',
            'assets/images/jgs-september-draw-charlotte.png', 'Ashton', 'October 1',
            'does not collect entries', 'How to enter', 'href="#details"',
            'Essential Detail Package', '$200 value', '2–3 hours', 'No vehicle restrictions',
            'excessive dirt or buildup', '2026-09-01T09:00:00-04:00',
            '2026-09-30T22:00:00-04:00', '2026-10-01T09:30:00-04:00',
            'Regular, diesel, premium and midgrade', 'Any fuel amount gets you one ballot',
            'collection bin', 'reserves the right to refuse entries',
            'assets/documents/jgs-september-draw-charlotte.pdf'] as $expected) {
            self::assertStringContainsString($expected, $html);
        }
        foreach (['DESIGN PREVIEW', 'INTERNAL REVIEW', 'DRAFT', 'noindex', 'Charlotte',
            'no-purchase option', '<form', '<!-- jgs:', '/og.png', '127.0.0.1',
            'More details', 'finishing touches', 'jgs-september-draw.png'] as $privateOrIncorrect) {
            self::assertStringNotContainsString($privateOrIncorrect, $html);
        }
        self::assertSame(1, substr_count($html, '<h1 '));
    }

    public function testHomepageAndSitemapLinkToDraw(): void
    {
        $home = new HomeController($this->metadata());
        self::assertStringContainsString('href="/september-draw"', (string) $home->index()->getContent());
        self::assertStringContainsString('<loc>https://jonesgeneralstore.ca/september-draw</loc>', (string) $home->sitemapXml()->getContent());
        self::assertStringNotContainsString('<!-- jgs:', (string) $home->index()->getContent());
    }

    public function testSharingCardIsLandscapeAndIndependentFromPoster(): void
    {
        $html = (string) (new DrawController($this->metadata()))->index()->getContent();
        $url = 'https://jonesgeneralstore.ca/assets/images/jgs-september-draw-og-v1.jpg';
        self::assertStringContainsString('<meta property="og:image" content="' . $url . '">', $html);
        self::assertStringContainsString('<meta name="twitter:image" content="' . $url . '">', $html);
        self::assertStringContainsString('<meta property="og:image:secure_url" content="' . $url . '">', $html);
        self::assertStringContainsString('<meta property="og:image:type" content="image/jpeg">', $html);
        self::assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        self::assertStringContainsString('<meta property="og:image:height" content="630">', $html);
        self::assertStringContainsString('src="/assets/images/jgs-september-draw-charlotte.png"', $html);
        $path = dirname(__DIR__, 3) . '/public/assets/images/jgs-september-draw-og-v1.jpg';
        $size = getimagesize($path);
        self::assertIsArray($size);
        self::assertSame(1200, $size[0]);
        self::assertSame(630, $size[1]);
        self::assertSame('image/jpeg', $size['mime']);
        self::assertLessThan(200_000, filesize($path));
    }

    public function testTemplatePathIsAllowlisted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new StorefrontRenderer())->render('../.env', '');
    }
}
