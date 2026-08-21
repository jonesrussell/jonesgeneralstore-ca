<?php

declare(strict_types=1);

namespace App\Provider;

use App\Content\CanonicalContentRouteResolver;
use App\Controller\PublishedContentController;
use Waaseyaa\Listing\HasListingsInterface;
use Waaseyaa\Listing\ListingDefinition;
use Waaseyaa\Listing\ListingDefinitionRegistry;
use Waaseyaa\Listing\ListingResolver;
use Waaseyaa\Listing\Sort;
use Waaseyaa\Path\PathAliasResolver;
use Waaseyaa\Routing\RouteBuilder;
use Waaseyaa\Routing\WaaseyaaRouter;
use Waaseyaa\Seo\MetaTagBuilder;
use Waaseyaa\Seo\SchemaOrg\EntitySchemaOrgMapper;
use Waaseyaa\Seo\SitemapGenerator;
use Waaseyaa\Foundation\ServiceProvider\ServiceProvider;
use Waaseyaa\Field\BundleTemplateCompiler;
use Twig\Environment;
use Waaseyaa\Access\EntityAccessHandler;
use Waaseyaa\Entity\EntityTypeManagerInterface;

final class PublishedContentServiceProvider extends ServiceProvider implements HasListingsInterface
{
    /** @var list<array<string, mixed>>|null */
    private ?array $definitions = null;

    public function register(): void
    {
        $this->singleton(PathAliasResolver::class, fn(): PathAliasResolver => new PathAliasResolver(
            $this->resolve(EntityTypeManagerInterface::class)->getRepository('path_alias'),
        ));
        $this->singleton(CanonicalContentRouteResolver::class, fn(): CanonicalContentRouteResolver => new CanonicalContentRouteResolver(
            (string) ($this->config['APP_ORIGIN'] ?? ''),
            $this->definitions(),
            $this->resolve(PathAliasResolver::class),
        ));
        $this->singleton(PublishedContentController::class, fn(): PublishedContentController => new PublishedContentController(
            $this->resolve(ListingResolver::class),
            $this->resolve(ListingDefinitionRegistry::class),
            $this->resolve(CanonicalContentRouteResolver::class),
            $this->resolve(SitemapGenerator::class),
            $this->resolve(MetaTagBuilder::class),
            $this->resolve(EntitySchemaOrgMapper::class),
            $this->resolve(EntityTypeManagerInterface::class),
            $this->resolve(EntityAccessHandler::class),
            $this->resolve(Environment::class),
        ));
    }

    public function boot(): void
    {
        $classes = array_map(
            static fn(array $definition): string => 'App\\Content\\Bundle\\' . str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $definition['bundle']))) . 'Bundle',
            $this->definitions(),
        );
        $this->resolve(BundleTemplateCompiler::class)->compile($classes);
    }

    /** @return list<ListingDefinition> */
    public function listings(): array
    {
        return array_map(static fn(array $definition): ListingDefinition => new ListingDefinition(
            id: $definition['listing_id'],
            entityType: 'node',
            bundle: $definition['bundle'],
            sorts: [Sort::desc('changed')],
            pageSize: 24,
            accessOps: ['view'],
        ), $this->definitions());
    }

    public function routes(WaaseyaaRouter $router, \Waaseyaa\Entity\EntityTypeManager $entityTypeManager): void
    {
        foreach ($this->definitions() as $definition) {
            $bundle = (string) $definition['bundle'];
            $controller = fn(): PublishedContentController => $this->resolve(PublishedContentController::class);
            $router->addRoute('site.' . $bundle . '.index', RouteBuilder::create($definition['index_route'])
                ->controller(fn(\Symfony\Component\HttpFoundation\Request $request) => $controller()->index($request, $bundle))
                ->allowAll()->methods('GET')->build());
            $router->addRoute('site.' . $bundle . '.detail', RouteBuilder::create($definition['detail_route'])
                ->controller(fn(\Symfony\Component\HttpFoundation\Request $request) => $controller()->detail($request, $bundle))
                ->allowAll()->methods('GET')->build());
        }
        $router->addRoute('site.sitemap', RouteBuilder::create('/sitemap.xml')
            ->controller(fn() => $this->resolve(PublishedContentController::class)->sitemap())
            ->allowAll()->methods('GET')->build());
    }

    /** @return list<array<string, mixed>> */
    private function definitions(): array
    {
        return $this->definitions ??= require $this->projectRoot . '/config/waaseyaa-recipes/published-content.php';
    }
}