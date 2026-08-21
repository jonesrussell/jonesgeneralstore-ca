<?php

declare(strict_types=1);

namespace App\Authoring;

use Waaseyaa\PageBuilder\Document\LayoutDocument;

/** Shared public/preview renderer. It accepts only validated governed blocks. */
final class GovernedPageRenderer
{
    /** @return list<array<string, mixed>> */
    public function viewModel(LayoutDocument $document): array
    {
        $sections = [];
        foreach ($document->sections() as $section) {
            $regions = [];
            foreach (($section['regions'] ?? []) as $region => $blocks) {
                $regions[(string) $region] = array_map(fn (array $block): array => $this->block($block), $blocks);
            }
            $sections[] = ['id' => $section['id'], 'layout' => $section['layout']['id'], 'regions' => $regions];
        }

        return $sections;
    }

    /** @param array<string, mixed> $block @return array<string, mixed> */
    private function block(array $block): array
    {
        $type = (string) ($block['type'] ?? '');
        if (!in_array($type, ['rich_text', 'image', 'callout', 'listing'], true)) {
            throw new \DomainException("Unsupported governed block: {$type}");
        }

        return ['id' => $block['id'], 'type' => $type, 'config' => $block['config']];
    }
}