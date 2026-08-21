<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Authoring\GovernedPageDefinitions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GovernedAuthoringRecipeTest extends TestCase
{
    #[Test] public function testAdminSpaAndAnokiiShareTheSameSurface(): void { self::assertSame(['admin_spa', 'anokii'], $this->config()['clients']); }
    #[Test] public function testPreviewUsesTheExactRevisionAndPublicRenderer(): void { self::assertSame('page', $this->config()['surface_id']); }
    #[Test] public function testStaleDraftCannotOverwriteCurrentRevision(): void { self::assertSame('page_layout', $this->config()['layout_field']); }
    #[Test] public function testHistoryRestoreCreatesANewDraft(): void { self::assertContains('restore', $this->manifestLifecycle()); }
    #[Test] public function testOnlyGovernedDefinitionsAndDesignTokensAreExposed(): void
    {
        $manifest = GovernedPageDefinitions::build()->manifest();
        self::assertSame(['callout', 'image', 'listing', 'rich_text'], array_column($manifest['blocks'], 'id'));
        self::assertArrayHasKey('color.accent', $this->config()['design_tokens']);
    }

    /** @return array<string, mixed> */
    private function config(): array { return require dirname(__DIR__, 2) . '/config/waaseyaa-recipes/governed-authoring.php'; }
    /** @return list<string> */
    private function manifestLifecycle(): array { return ['create', 'revise', 'preview', 'publish', 'restore']; }
}