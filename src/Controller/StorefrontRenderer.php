<?php

declare(strict_types=1);

namespace App\Controller;

/** Shared shell for the application's code-authored storefront pages. */
final class StorefrontRenderer
{
    public function render(string $template, string $head, string $structuredData = ''): string
    {
        // Names are application-owned, never derived from a URL or user input.
        if (!in_array($template, ['home', 'september-draw'], true)) {
            throw new \InvalidArgumentException('Unknown storefront template.');
        }
        $root = dirname(__DIR__, 2) . '/templates/';

        return strtr((string) file_get_contents($root . $template . '.html.twig'), [
            '<!-- jgs:seo-head -->' => $head,
            '<!-- jgs:structured-data -->' => $structuredData,
            '<!-- jgs:site-header -->' => (string) file_get_contents($root . 'partials/site-header.html.twig'),
            '<!-- jgs:site-footer -->' => (string) file_get_contents($root . 'partials/site-footer.html.twig'),
        ]);
    }
}
