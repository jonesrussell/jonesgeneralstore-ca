<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\HomeController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HomeControllerTest extends TestCase
{
    #[Test]
    public function it_renders_the_jones_general_store_homepage(): void
    {
        $response = new HomeController()->index();
        $html = (string) $response->getContent();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Jones General Store', $html);
        self::assertStringContainsString('4027 Espaniel Street', $html);
        self::assertStringContainsString('(705) 865-1633', $html);
        self::assertStringContainsString('9 AM–10 PM', $html);
        self::assertStringContainsString('Home-made hot lunches', $html);
        self::assertStringContainsString('Beads &amp; local finds', $html);
        self::assertStringContainsString('Family owned since 1984', $html);
        self::assertStringContainsString('title="Map showing Jones General Store at 4027 Espaniel Street"', $html);
        self::assertStringContainsString('Open map &amp; get directions', $html);
        self::assertStringNotContainsString('>Staff</a>', $html);
        self::assertStringNotContainsString('Waaseyaa</title>', $html);
    }
}
