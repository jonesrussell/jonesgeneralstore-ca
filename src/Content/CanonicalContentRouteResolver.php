<?php

declare(strict_types=1);

namespace App\Content;

use Waaseyaa\Path\PathAliasResolver;

final readonly class CanonicalContentRouteResolver
{
    /** @param list<array<string, mixed>> $definitions */
    public function __construct(
        private string $canonicalOrigin,
        private array $definitions,
        private PathAliasResolver $pathAliasResolver,
    ) {}

    public function canonicalDetailUrl(string $bundle, string $slug): string
    {
        $definition = $this->definition($bundle);
        $path = str_replace('{slug}', rawurlencode($slug), (string) $definition['detail_route']);

        return rtrim($this->canonicalOrigin, '/') . $path;
    }

    public function sitemapUrl(string $bundle, string $slug): string
    {
        return $this->canonicalDetailUrl($bundle, $slug);
    }

    public function resolveInboundAlias(string $path): ?\Waaseyaa\Path\ResolvedPath
    {
        return $this->pathAliasResolver->resolve($path);
    }

    /** @return array<string, mixed> */
    private function definition(string $bundle): array
    {
        foreach ($this->definitions as $definition) {
            if (($definition['bundle'] ?? null) === $bundle) {
                return $definition;
            }
        }

        throw new \InvalidArgumentException("Unknown published-content bundle: {$bundle}");
    }
}