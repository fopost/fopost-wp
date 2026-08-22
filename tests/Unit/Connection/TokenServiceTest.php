<?php

declare(strict_types=1);

namespace Fopost\Wp\Tests\Unit\Connection;

use Fopost\Wp\TokenService;
use Fopost\Wp\Tests\TestCase;

class TokenServiceTest extends TestCase
{
    private TokenService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['fopost_test_options'] = [];
        $this->service = new TokenService();
    }

    public function testGenerateReturnsPrefixedToken(): void
    {
        $token = $this->service->generate(1);

        $this->assertStringStartsWith('fopst_', $token);
        $this->assertSame(6 + 64, strlen($token));
    }

    public function testPlaintextTokenIsNeverStored(): void
    {
        $token = $this->service->generate(1);

        $stored = wp_json_encode($GLOBALS['fopost_test_options'][TokenService::OPTION_KEY]);

        $this->assertStringNotContainsString($token, (string) $stored);
    }

    public function testVerifyAcceptsCorrectToken(): void
    {
        $token = $this->service->generate(1);

        $this->assertTrue($this->service->verify($token));
    }

    public function testVerifyRejectsWrongToken(): void
    {
        $this->service->generate(1);

        $this->assertFalse($this->service->verify('fopst_' . str_repeat('0', 64)));
        $this->assertFalse($this->service->verify(''));
    }

    public function testVerifyRejectsWhenNotPaired(): void
    {
        $this->assertFalse($this->service->isPaired());
        $this->assertFalse($this->service->verify('fopst_' . str_repeat('0', 64)));
    }

    public function testRegenerateInvalidatesOldToken(): void
    {
        $old = $this->service->generate(1);
        $new = $this->service->generate(1);

        $this->assertFalse($this->service->verify($old));
        $this->assertTrue($this->service->verify($new));
    }

    public function testRevokeRemovesToken(): void
    {
        $token = $this->service->generate(1);
        $this->service->revoke();

        $this->assertFalse($this->service->isPaired());
        $this->assertFalse($this->service->verify($token));
    }

    public function testRevokePreservesSettings(): void
    {
        $this->service->generate(1);
        $data = $this->service->all();
        $data['post_status_policy'] = 'draft';
        $this->service->save($data);

        $this->service->revoke();

        $this->assertSame('draft', $this->service->all()['post_status_policy']);
    }

    public function testInfoExposesHintButNotHash(): void
    {
        $token = $this->service->generate(7);
        $info  = $this->service->info();

        $this->assertSame(substr($token, 0, 10), $info['hint']);
        $this->assertSame(7, $info['created_by']);
        $this->assertNull($info['last_used_at']);
        $this->assertArrayNotHasKey('token_hash', $info);
    }

    public function testTouchRecordsLastUse(): void
    {
        $this->service->generate(1);
        $this->service->touch();

        $this->assertIsInt($this->service->info()['last_used_at']);
    }
}
