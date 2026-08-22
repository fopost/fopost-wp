<?php

declare(strict_types=1);

namespace Fopost\Wp\Tests\Unit\Connection;

use Fopost\Wp\TokenService;
use Fopost\Wp\Tests\TestCase;

/**
 * A site upgrading from the previous OwlStack-branded plugin keeps its
 * pairing: the old option is copied once into the new key on activation.
 */
class LegacyMigrationTest extends TestCase
{
    private TokenService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['fopost_test_options'] = [];
        $this->service = new TokenService();
    }

    /**
     * Build a legacy option payload holding a token issued by the old plugin.
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function legacyPayload(): array
    {
        $token = 'owlstk_' . bin2hex(random_bytes(32));

        return [$token, [
            'token_hash'         => hash('sha256', $token),
            'token_hint'         => substr($token, 0, 11),
            'created_at'         => 1700000000,
            'created_by'         => 7,
            'last_used_at'       => null,
            'post_status_policy' => 'draft',
            'default_author'     => 7,
            'post_type'          => 'page',
        ]];
    }

    public function testMigrationCopiesLegacyConnection(): void
    {
        [$token, $legacy] = $this->legacyPayload();
        update_option(TokenService::LEGACY_OPTION_KEY, $legacy);

        $this->assertTrue($this->service->migrateLegacyConnection());

        $this->assertTrue($this->service->isPaired());
        $this->assertTrue($this->service->verify($token));
        $this->assertSame('draft', $this->service->all()['post_status_policy']);
    }

    public function testMigrationLeavesTheLegacyOptionUntouched(): void
    {
        [, $legacy] = $this->legacyPayload();
        update_option(TokenService::LEGACY_OPTION_KEY, $legacy);

        $this->service->migrateLegacyConnection();

        $this->assertSame($legacy, get_option(TokenService::LEGACY_OPTION_KEY));
    }

    public function testMigrationNeverOverwritesAnExistingConnection(): void
    {
        $current = $this->service->generate(1);

        [$old, $legacy] = $this->legacyPayload();
        update_option(TokenService::LEGACY_OPTION_KEY, $legacy);

        $this->assertFalse($this->service->migrateLegacyConnection());

        $this->assertTrue($this->service->verify($current));
        $this->assertFalse($this->service->verify($old));
    }

    public function testMigrationIsANoOpOnAFreshSite(): void
    {
        $this->assertFalse($this->service->migrateLegacyConnection());
        $this->assertFalse($this->service->isPaired());
    }
}
