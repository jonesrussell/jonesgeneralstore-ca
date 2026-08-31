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
            'assets/images/jgs-september-draw.png', 'Ashton', 'October 1',
            'does not collect entries', 'More details', 'href="#details"'] as $expected) {
            self::assertStringContainsString($expected, $html);
        }
        foreach (['DESIGN PREVIEW', 'INTERNAL REVIEW', 'DRAFT', 'noindex', 'Charlotte',
            'no-purchase option', '<form', '<!-- jgs:', '/og.png', '127.0.0.1', 'fuel up and receive'] as $privateOrIncorrect) {
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

    public function testTemplatePathIsAllowlisted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new StorefrontRenderer())->render('../.env', '');
    }
}
