<?php

declare(strict_types=1);

namespace App\Seo;

use InvalidArgumentException;
use JsonException;
use Waaseyaa\Seo\JsonLdBuilder;
use Waaseyaa\Seo\MetaTagBuilder;

final class HomepageMetadataBuilder
{
    private const string TITLE = 'Jones General Store | Sagamok Anishnawbek';
    private const string DESCRIPTION = 'Everyday essentials, local finds, cold drinks, snacks and full-service fuel at Jones General Store in Sagamok Anishnawbek.';

    private readonly string $origin;
    private readonly string $facebookUrl;
    private readonly MetaTagBuilder $metaTags;
    private readonly JsonLdBuilder $jsonLd;

    public function __construct(
        string $origin,
        string $facebookUrl,
        ?MetaTagBuilder $metaTags = null,
        ?JsonLdBuilder $jsonLd = null,
    ) {
        $this->metaTags = $metaTags ?? new MetaTagBuilder();
        $this->jsonLd = $jsonLd ?? new JsonLdBuilder();
        $this->origin = $this->trustedOrigin($origin);
        $this->facebookUrl = $this->trustedOrigin($facebookUrl);
    }

    public function canonicalUrl(string $path = ''): string
    {
        return $path === '' ? $this->origin . '/' : $this->origin . '/' . ltrim($path, '/');
    }

    public function head(): string
    {
        $canonical = $this->canonicalUrl();
        $image = $this->canonicalUrl('og.png');
        $parts = [
            rtrim($this->metaTags->buildHeadSnippet(self::TITLE, self::DESCRIPTION, $canonical)),
            '<meta name="robots" content="index, follow, max-image-preview:large">',
            '<meta name="theme-color" content="#10253f">',
            $this->property('og:type', 'website'),
            $this->property('og:locale', 'en_CA'),
            $this->property('og:site_name', 'Jones General Store'),
            $this->property('og:title', self::TITLE),
            $this->property('og:description', self::DESCRIPTION),
            $this->property('og:url', $canonical),
            $this->property('og:image', $image),
            $this->property('og:image:secure_url', $image),
            $this->property('og:image:type', 'image/png'),
            $this->property('og:image:width', '1730'),
            $this->property('og:image:height', '909'),
            $this->property('og:image:alt', 'Jones General Store — Your local stop, gassed up.'),
            $this->named('twitter:card', 'summary_large_image'),
            $this->named('twitter:title', self::TITLE),
            $this->named('twitter:description', self::DESCRIPTION),
            $this->named('twitter:image', $image),
            $this->named('twitter:image:alt', 'Jones General Store — Your local stop, gassed up.'),
        ];

        return implode("\n", $parts) . "\n";
    }

    /** @throws JsonException */
    public function structuredData(): string
    {
        $website = $this->jsonLd->webSite($this->canonicalUrl(), 'Jones General Store', [$this->facebookUrl]);
        $store = $this->jsonLd->organization('Jones General Store', $this->canonicalUrl(), [$this->facebookUrl]);
        $store['@type'] = ['ConvenienceStore', 'GasStation'];
        $store['@id'] = $this->canonicalUrl('#store');
        $store['description'] = self::DESCRIPTION;
        $store['telephone'] = '+1-705-865-1633';
        $store['image'] = $this->canonicalUrl('og.png');
        $store['logo'] = $this->canonicalUrl('assets/images/jgs-logo.jpg');
        $store['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => '4027 Espaniel Street',
            'addressLocality' => 'Sagamok Anishnawbek',
            'addressRegion' => 'ON',
            'addressCountry' => 'CA',
        ];
        $store['openingHoursSpecification'] = [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens' => '09:00',
            'closes' => '22:00',
        ]];
        unset($website['@context'], $store['@context']);
        $data = [
            '@context' => 'https://schema.org',
            '@graph' => [$website, $store],
        ];
        $json = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP,
        );

        return '<script type="application/ld+json">' . $json . '</script>' . "\n";
    }

    private function property(string $property, string $content): string
    {
        return '<meta property="' . $this->escape($property) . '" content="' . $this->escape($content) . '">';
    }

    private function named(string $name, string $content): string
    {
        return '<meta name="' . $this->escape($name) . '" content="' . $this->escape($content) . '">';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function trustedOrigin(string $origin): string
    {
        $candidate = rtrim($origin, '/');
        if (filter_var($candidate, FILTER_VALIDATE_URL) === false
            || strtolower((string) parse_url($candidate, PHP_URL_SCHEME)) !== 'https') {
            throw new InvalidArgumentException('SEO origins must be configured as absolute HTTPS URLs.');
        }

        return $candidate;
    }
}
