<?php

declare(strict_types=1);

namespace App\Controller;

use App\Content\CanonicalContentRouteResolver;
use Twig\Environment;
use Waaseyaa\Access\AccountInterface;
use Waaseyaa\Access\EntityAccessHandler;
use Waaseyaa\Entity\EntityInterface;
use Waaseyaa\Entity\EntityTypeManagerInterface;
use Waaseyaa\Foundation\Http\Request;
use Waaseyaa\Listing\ExposedFilterParser;
use Waaseyaa\Listing\ListingDefinitionRegistry;
use Waaseyaa\Listing\ListingResolver;
use Waaseyaa\Seo\MetaTagBuilder;
use Waaseyaa\Seo\SchemaOrg\EntitySchemaOrgMapper;
use Waaseyaa\Seo\SitemapGenerator;
use Waaseyaa\User\AnonymousUser;

final readonly class PublishedContentController
{
    public function __construct(
        private ListingResolver $listings,
        private ListingDefinitionRegistry $definitions,
        private CanonicalContentRouteResolver $canonicalRoutes,
        private SitemapGenerator $sitemap,
        private MetaTagBuilder $metaTags,
        private EntitySchemaOrgMapper $jsonLd,
        private EntityTypeManagerInterface $entityTypes,
        private EntityAccessHandler $access,
        private Environment $twig,
    ) {}

    public function index(Request $request, string $bundle): \Symfony\Component\HttpFoundation\Response
    {
        $definition = $this->definitions->get($bundle . '_index');
        $filters = ExposedFilterParser::create()->parse($request->query->all(), $definition);
        $result = $this->listings->resolve($definition, $filters);
        $account = $this->account($request);
        $rows = [];
        foreach ($result->rows as $entity) {
            $label = $this->access->viewableLabel($entity, $account, $this->entityTypes);
            if ($label === null) {
                continue;
            }
            $rows[] = [
                'label' => $label,
                'canonical_url' => $this->canonicalRoutes->canonicalDetailUrl($bundle, (string) $entity->get('slug')),
            ];
        }

        return new \Symfony\Component\HttpFoundation\Response($this->twig->render('content/index.html.twig', [
            'bundle' => $bundle,
            'rows' => $rows,
            'pagination' => $result->pagination,
        ]));
    }

    public function detail(Request $request, string $bundle): \Symfony\Component\HttpFoundation\Response
    {
        $resolved = $this->canonicalRoutes->resolveInboundAlias($request->getPathInfo());
        if ($resolved === null || $resolved->entityTypeId !== 'node') {
            return new \Symfony\Component\HttpFoundation\Response('Not found', 404);
        }
        $entity = $this->entityTypes->getRepository('node')->find($resolved->entityId);
        $account = $this->account($request);
        if (!$entity instanceof EntityInterface || !$this->access->check($entity, 'view', $account)->isAllowed()) {
            return new \Symfony\Component\HttpFoundation\Response('Not found', 404);
        }
        $label = $this->access->viewableLabel($entity, $account, $this->entityTypes);
        if ($label === null) {
            return new \Symfony\Component\HttpFoundation\Response('Not found', 404);
        }
        $canonical = $this->canonicalRoutes->canonicalDetailUrl($bundle, (string) $entity->get('slug'));
        $description = $this->access->checkFieldAccess($entity, 'summary', 'view', $account)->isForbidden()
            ? null
            : (string) $entity->get('summary');
        $head = $this->metaTags->buildHeadSnippet($label, $description, $canonical);
        $structuredData = $this->jsonLd->toScriptTag($this->jsonLd->map(
            $entity,
            $canonical,
            $description,
            labelOverride: $label,
        ));

        return new \Symfony\Component\HttpFoundation\Response($this->twig->render('content/detail.html.twig', [
            'bundle' => $bundle,
            'title' => $label,
            'body' => (string) $entity->get('body'),
            'head' => $head,
            'structured_data' => $structuredData,
        ]));
    }

    public function sitemap(): \Symfony\Component\HttpFoundation\Response
    {
        $anonymous = new AnonymousUser(['access content']);
        $urls = $this->sitemap->collectFromEntityTypes(
            $this->entityTypes,
            function (string $entityType, int|string $id): ?string {
                $entity = $this->entityTypes->getRepository($entityType)->find($id);
                if (!$entity instanceof EntityInterface || $entityType !== 'node') {
                    return null;
                }

                return $this->canonicalRoutes->sitemapUrl($entity->bundle(), (string) $entity->get('slug'));
            },
            includeEntityTypes: ['node'],
            account: $anonymous,
        );

        return new \Symfony\Component\HttpFoundation\Response($this->sitemap->toXml($urls), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function account(Request $request): AccountInterface
    {
        $account = $request->attributes->get('_account');

        return $account instanceof AccountInterface ? $account : new AnonymousUser(['access content']);
    }
}