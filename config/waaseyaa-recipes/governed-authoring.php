<?php

declare(strict_types=1);

return [
    'surface_id' => 'page',
    'entity_type' => 'node',
    'bundle' => 'page',
    'layout_field' => 'page_layout',
    'permission' => 'edit pages',
    'clients' => ['admin_spa', 'anokii'],
    'design_tokens' => [
        'color.surface' => 'var(--sheg-surface)',
        'color.text' => 'var(--sheg-ink)',
        'color.accent' => 'var(--sheg-accent)',
        'space.section' => 'var(--sheg-section-space)',
    ],
];