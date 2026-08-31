<?php

declare(strict_types=1);

namespace App\Controller;

use App\Seo\HomepageMetadataBuilder;
use Symfony\Component\HttpFoundation\Response;
use Waaseyaa\Seo\MetaTagBuilder;

final readonly class DrawController
{
    public function __construct(private HomepageMetadataBuilder $metadata) {}

    public function index(): Response
    {
        $title = 'September Draw | Jones General Store';
        $description = 'A chance to win an interior and exterior detailing package plus a JGS gift basket. September draw information from Jones General Store in Sagamok.';
        $canonical = $this->metadata->canonicalUrl('september-draw');
        $image = $this->metadata->canonicalUrl('assets/images/jgs-september-draw.png');
        $head = (new MetaTagBuilder())->buildHeadSnippet($title, $description, $canonical);
        foreach ([
            'og:type' => 'website', 'og:locale' => 'en_CA',
            'og:site_name' => 'Jones General Store', 'og:title' => $title,
            'og:description' => $description, 'og:url' => $canonical,
            'og:image' => $image, 'og:image:alt' => 'JGS September draw: detailing package and gift basket',
        ] as $property => $value) {
            $head .= '<meta property="' . $property . '" content="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . "\n";
        }
        foreach (['twitter:card' => 'summary_large_image', 'twitter:title' => $title,
            'twitter:description' => $description, 'twitter:image' => $image,
            'twitter:image:alt' => 'JGS September draw: detailing package and gift basket'] as $name => $value) {
            $head .= '<meta name="' . $name . '" content="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . "\n";
        }

        return new Response((new StorefrontRenderer())->render('september-draw', $head), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
