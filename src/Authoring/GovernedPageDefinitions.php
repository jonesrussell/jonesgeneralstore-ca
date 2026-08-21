<?php

declare(strict_types=1);

namespace App\Authoring;

use Waaseyaa\PageBuilder\Definition\BlockDefinition;
use Waaseyaa\PageBuilder\Definition\DefinitionRegistry;
use Waaseyaa\PageBuilder\Definition\LayoutDefinition;
use Waaseyaa\PageBuilder\Definition\TemplateDefinition;

final class GovernedPageDefinitions
{
    public static function build(): DefinitionRegistry
    {
        $registry = new DefinitionRegistry();
        $registry->registerBlock(new BlockDefinition('rich_text', 1, 'Rich text', 'page.rich_text', self::objectSchema([
            'content' => ['type' => 'string', 'maxLength' => 50000],
        ], ['content'])));
        $registry->registerBlock(new BlockDefinition('image', 1, 'Image', 'page.image', self::objectSchema([
            'media_id' => ['type' => 'string', 'minLength' => 1],
            'alt' => ['type' => 'string', 'maxLength' => 500],
            'caption' => ['type' => 'string', 'maxLength' => 1000],
        ], ['media_id', 'alt'])));
        $registry->registerBlock(new BlockDefinition('callout', 1, 'Callout', 'page.callout', self::objectSchema([
            'heading' => ['type' => 'string', 'maxLength' => 200],
            'content' => ['type' => 'string', 'maxLength' => 5000],
            'tone' => ['type' => 'string', 'enum' => ['information', 'important', 'emergency']],
        ], ['content', 'tone'])));
        $registry->registerBlock(new BlockDefinition('listing', 1, 'Content listing', 'page.listing', self::objectSchema([
            'listing_id' => ['type' => 'string', 'enum' => ['post_index', 'community_event_index', 'job_posting_index', 'announcement_index']],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 24],
        ], ['listing_id'])));
        $allowed = ['rich_text', 'image', 'callout', 'listing'];
        $registry->registerLayout(new LayoutDefinition('one_column', 1, ['main'], ['main'], $allowed));
        $registry->registerLayout(new LayoutDefinition('sidebar', 1, ['main', 'aside'], ['main'], $allowed));
        $registry->registerTemplate(new TemplateDefinition('standard', 1, ['one_column', 'sidebar'], $allowed));

        return $registry;
    }

    /** @param array<string, array<string, mixed>> $properties @param list<string> $required */
    private static function objectSchema(array $properties, array $required): array
    {
        return ['type' => 'object', 'required' => $required, 'additionalProperties' => false, 'properties' => $properties];
    }
}