<?php

declare(strict_types=1);

namespace App\Provider;

use App\Authoring\GovernedPageDefinitions;
use App\Authoring\GovernedPagePreviewUrlGenerator;
use Waaseyaa\AdminSurface\PageBuilder\GenericPageBuilderSurfaceHost;
use Waaseyaa\AdminSurface\PageBuilder\PageBuilderSurfaceHostInterface;
use Waaseyaa\Entity\Field\FieldDefinitionRegistryInterface;
use Waaseyaa\Field\FieldDefinition;
use Waaseyaa\Foundation\ServiceProvider\ServiceProvider;
use Waaseyaa\PageBuilder\Definition\DefinitionRegistry;
use Waaseyaa\PageBuilder\Document\CanonicalLayoutCodec;
use Waaseyaa\PageBuilder\Draft\LayoutDraftManager;
use Waaseyaa\PageBuilder\Editor\LayoutEditor;
use Waaseyaa\PageBuilder\Revision\PageBuilderRevisionHistory;
use Waaseyaa\PageBuilder\Surface\PageBuilderSurface;
use Waaseyaa\PageBuilder\Surface\PageBuilderSurfaceRegistry;
use Waaseyaa\PageBuilder\Validation\LayoutValidator;
use Waaseyaa\Publishing\ContentPublisher;
use Waaseyaa\Publishing\PageBuilder\PublishingLayoutDraftGateway;
use Waaseyaa\Publishing\PageBuilder\PublishingPageBuilderRevisionGateway;
use Waaseyaa\Publishing\PageBuilder\PublishingRevisionPreviewGateway;
use Waaseyaa\Publishing\Preview\PreviewLinkService;

final class GovernedAuthoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = require $this->projectRoot . '/config/waaseyaa-recipes/governed-authoring.php';
        $this->singleton(DefinitionRegistry::class, static fn (): DefinitionRegistry => GovernedPageDefinitions::build());
        $this->singleton(PageBuilderSurfaceHostInterface::class, fn (): GenericPageBuilderSurfaceHost => new GenericPageBuilderSurfaceHost(
            $this->resolve(PageBuilderSurfaceRegistry::class),
        ));
        $this->singleton(PageBuilderSurfaceRegistry::class, function () use ($config): PageBuilderSurfaceRegistry {
            $definitions = $this->resolve(DefinitionRegistry::class);
            $codec = new CanonicalLayoutCodec();
            $validator = new LayoutValidator($definitions);
            $editor = new LayoutEditor($codec, $validator, $definitions);
            $publisher = $this->resolve(ContentPublisher::class);
            $drafts = new PublishingLayoutDraftGateway($publisher, $config['layout_field']);
            $historyGateway = new PublishingPageBuilderRevisionGateway($publisher, $config['layout_field']);
            $previews = new PublishingRevisionPreviewGateway(
                $publisher,
                $this->resolve(PreviewLinkService::class),
                new GovernedPagePreviewUrlGenerator(),
            );
            $surface = new PageBuilderSurface(
                $config['permission'],
                $definitions,
                new LayoutDraftManager($drafts, $codec, $validator, $editor),
                $previews,
                new PageBuilderRevisionHistory($historyGateway, $codec, $validator, $editor),
            );
            $registry = new PageBuilderSurfaceRegistry();
            $registry->register('page', $surface);

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->resolve(FieldDefinitionRegistryInterface::class)->registerBundleFields('node', 'page', [
            new FieldDefinition(
                name: 'page_layout',
                type: 'text',
                targetEntityTypeId: 'node',
                targetBundle: 'page',
                revisionable: true,
                label: 'Page layout',
                group: 'content',
            ),
        ]);
    }
}