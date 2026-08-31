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
        $description = 'Fuel up September 1–30 for a chance to win a $200 Essential Detail Package plus a JGS gift basket. Draw October 1, 2026 at 9:30 AM on Facebook Live.';
        $canonical = $this->metadata->canonicalUrl('september-draw');
        $image = $this->metadata->canonicalUrl('assets/images/jgs-september-draw-og-v1.jpg');
        $head = (new MetaTagBuilder())->buildHeadSnippet($title, $description, $canonical);
        foreach ([
            'og:type' => 'website', 'og:locale' => 'en_CA',
            'og:site_name' => 'Jones General Store', 'og:title' => $title,
            'og:description' => $description, 'og:url' => $canonical,
            'og:image' => $image, 'og:image:secure_url' => $image,
            'og:image:type' => 'image/jpeg', 'og:image:width' => '1200', 'og:image:height' => '630',
            'og:image:alt' => 'Jones General Store September draw: win a $200 detailing package plus a JGS gift basket. October 1 at 9:30 AM on Facebook Live.',
        ] as $property => $value) {
            $head .= '<meta property="' . $property . '" content="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . "\n";
        }
        foreach (['twitter:card' => 'summary_large_image', 'twitter:title' => $title,
            'twitter:description' => $description, 'twitter:image' => $image,
            'twitter:image:alt' => 'Jones General Store September draw: win a $200 detailing package plus a JGS gift basket. October 1 at 9:30 AM on Facebook Live.'] as $name => $value) {
            $head .= '<meta name="' . $name . '" content="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . "\n";
        }

        return new Response((new StorefrontRenderer())->render('september-draw', $head), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
