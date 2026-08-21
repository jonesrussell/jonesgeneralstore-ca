<?php

declare(strict_types=1);

namespace App\Authoring;

use Waaseyaa\PageBuilder\Preview\RevisionPreviewUrlGeneratorInterface;

final readonly class GovernedPagePreviewUrlGenerator implements RevisionPreviewUrlGeneratorInterface
{
    public function generate(string $entityId, int $revisionId, int $expiresAt, string $signature): string
    {
        $query = http_build_query(['revision' => $revisionId, 'expires' => $expiresAt, 'signature' => $signature], '', '&', PHP_QUERY_RFC3986);

        return '/page-builder-preview/' . rawurlencode($entityId) . '?' . $query;
    }
}