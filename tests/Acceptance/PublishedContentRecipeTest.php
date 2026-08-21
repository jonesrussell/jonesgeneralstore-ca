<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PublishedContentRecipeTest extends TestCase
{
    /** @return list<array<string, mixed>> */
    private function definitions(): array
    {
        return require dirname(__DIR__, 2) . '/config/waaseyaa-recipes/published-content.php';
    }

    public function testListingIsAccessAwareAndPageable(): void
    {
        foreach ($this->definitions() as $definition) {
            self::assertSame(['view'], $definition['access_ops']);
            self::assertGreaterThan(0, $definition['page_size']);
            self::assertLessThanOrEqual(100, $definition['page_size']);
        }
    }

    public function testCanonicalRoutesDriveSitemapUrls(): void
    {
        foreach ($this->definitions() as $definition) {
            self::assertStringContainsString('{slug}', $definition['detail_route']);
            self::assertStringStartsWith('/', $definition['index_route']);
        }
    }

    public function testMetadataAndJsonLdUseTheCanonicalUrl(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2) . '/src/Provider/PublishedContentServiceProvider.php');
        self::assertStringContainsString('MetaTagBuilder::class', $provider);
        self::assertStringContainsString('EntitySchemaOrgMapper::class', $provider);
    }

    public function testInternalEntityPathsNeverEnterTheSitemap(): void
    {
        foreach ($this->definitions() as $definition) {
            self::assertStringNotContainsString('/node/', $definition['detail_route']);
            self::assertStringNotContainsString('/node/', $definition['index_route']);
        }
    }
}