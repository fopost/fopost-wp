<?php

declare(strict_types=1);

namespace Fopost\Wp;

defined( 'ABSPATH' ) || exit;

/**
 * Manages the site token FoPost uses to push content into this site.
 *
 * Only a SHA-256 hash of the token is stored; the plaintext is shown once
 * at generation time. The plugin never sends the token anywhere, FoPost
 * presents it back on each request via the X-Fopost-Token header.
 */
class TokenService
{
    public const OPTION_KEY = 'fopost_connection';

    /** Option written by the previous OwlStack-branded plugin. Read once on activation. */
    public const LEGACY_OPTION_KEY = 'owlstack_cloud';

    private const TOKEN_PREFIX = 'fopst_';

    /**
     * Generate a new site token, replacing any existing one.
     *
     * @return string The plaintext token, display once, never stored.
     */
    public function generate(int $userId): string
    {
        $token = self::TOKEN_PREFIX . bin2hex(random_bytes(32));

        $data = $this->all();
        $data['token_hash']   = hash('sha256', $token);
        $data['token_hint']   = substr($token, 0, strlen(self::TOKEN_PREFIX) + 4);
        $data['created_at']   = time();
        $data['created_by']   = $userId;
        $data['last_used_at'] = null;

        $this->save($data);

        return $token;
    }

    /**
     * Check a presented token against the stored hash.
     */
    public function verify(string $token): bool
    {
        $hash = $this->all()['token_hash'] ?? '';

        if (! is_string($hash) || $hash === '' || $token === '') {
            return false;
        }

        return hash_equals($hash, hash('sha256', $token));
    }

    /**
     * Whether a token has been generated (site is pairable).
     */
    public function isPaired(): bool
    {
        $hash = $this->all()['token_hash'] ?? '';

        return is_string($hash) && $hash !== '';
    }

    /**
     * Remove the token. FoPost requests fail until a new one is generated.
     */
    public function revoke(): void
    {
        $data = $this->all();
        unset($data['token_hash'], $data['token_hint'], $data['created_at'], $data['created_by'], $data['last_used_at']);
        $this->save($data);
    }

    /**
     * Record that the token was just used successfully.
     */
    public function touch(): void
    {
        $data = $this->all();
        $data['last_used_at'] = time();
        $this->save($data);
    }

    /**
     * Token metadata for the admin UI (never the token itself).
     *
     * @return array{hint: string, created_at: ?int, created_by: ?int, last_used_at: ?int}
     */
    public function info(): array
    {
        $data = $this->all();

        return [
            'hint'         => is_string($data['token_hint'] ?? null) ? $data['token_hint'] : '',
            'created_at'   => is_int($data['created_at'] ?? null) ? $data['created_at'] : null,
            'created_by'   => is_int($data['created_by'] ?? null) ? $data['created_by'] : null,
            'last_used_at' => is_int($data['last_used_at'] ?? null) ? $data['last_used_at'] : null,
        ];
    }

    /**
     * Copy a connection saved by the previous OwlStack-branded plugin.
     *
     * Runs once on activation. The old option is left untouched so that
     * downgrading does not lose the pairing. Only the token hash survives,
     * so the site owner keeps working without regenerating.
     */
    public function migrateLegacyConnection(): bool
    {
        if ($this->all() !== []) {
            return false;
        }

        $legacy = get_option(self::LEGACY_OPTION_KEY, []);

        if (! is_array($legacy) || $legacy === []) {
            return false;
        }

        $this->save($legacy);

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $data = get_option(self::OPTION_KEY, []);

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        update_option(self::OPTION_KEY, $data, false);
    }
}
