<?php

declare(strict_types=1);

namespace App\Content\Bundle;

use Waaseyaa\Field\Attribute\BundleTemplate;
use Waaseyaa\Field\Attribute\FieldTemplate;

#[BundleTemplate(entityType: 'node', bundle: 'page')]
final class PageBundle
{
    #[FieldTemplate(key: 'summary', type: 'text', label: 'Summary', group: 'content')]
    public string $summary;

    #[FieldTemplate(key: 'body', type: 'text', label: 'Body', group: 'content')]
    public string $body;
}