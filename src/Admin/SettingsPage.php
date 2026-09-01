<?php

declare(strict_types=1);

namespace Fopost\Wp\Admin;

defined( 'ABSPATH' ) || exit;

use Fopost\Wp\Settings;
use Fopost\Wp\TokenService;

/**
 * Admin page for connecting this site to FoPost.
 *
 * Generates/revokes the site token and manages how incoming
 * content is handled (status policy, author, post type).
 */
class SettingsPage
{
    public const PAGE_SLUG = 'fopost';

    public const SITE_URL = 'https://fopost.com';

    private const REVEAL_TRANSIENT = 'fopost_token_reveal_';

    public function __construct(
        private readonly TokenService $tokens,
        private readonly Settings $settings,
    ) {
    }

    /**
     * Build a tagged link to the FoPost site so signups can be attributed.
     */
    public static function url(string $path = '/', string $placement = 'settings-page'): string
    {
        return add_query_arg(
            [
                'utm_source'   => 'wordpress-plugin',
                'utm_medium'   => 'plugin-admin',
                'utm_campaign' => 'connect',
                'utm_content'  => $placement,
            ],
            self::SITE_URL . $path,
        );
    }

    /**
     * Register the settings page under the Settings menu.
     */
    public function register(): void
    {
        add_options_page(
            page_title: __('FoPost', 'fopost'),
            menu_title: __('FoPost', 'fopost'),
            capability: 'manage_options',
            menu_slug: self::PAGE_SLUG,
            callback: [$this, 'render'],
        );
    }

    /**
     * Register admin-post form handlers.
     */
    public function registerActions(): void
    {
        add_action('admin_post_fopost_generate', [$this, 'handleGenerate']);
        add_action('admin_post_fopost_revoke', [$this, 'handleRevoke']);
        add_action('admin_post_fopost_settings', [$this, 'handleSettings']);
    }

    /**
     * Render the settings page.
     */
    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $revealKey     = self::REVEAL_TRANSIENT . get_current_user_id();
        $revealedToken = get_transient($revealKey);
        if (is_string($revealedToken) && $revealedToken !== '') {
            delete_transient($revealKey);
        } else {
            $revealedToken = null;
        }

        $isPaired  = $this->tokens->isPaired();
        $tokenInfo = $this->tokens->info();
        $settings  = $this->settings;

        require __DIR__ . '/views/settings-page.php';
    }

    /**
     * Generate (or regenerate) the site token.
     */
    public function handleGenerate(): void
    {
        $this->verifyRequest('fopost_generate');

        $token = $this->tokens->generate(get_current_user_id());

        // One-time reveal for the current admin only.
        set_transient(self::REVEAL_TRANSIENT . get_current_user_id(), $token, 5 * MINUTE_IN_SECONDS);

        $this->redirectBack('token-generated');
    }

    /**
     * Revoke the site token.
     */
    public function handleRevoke(): void
    {
        $this->verifyRequest('fopost_revoke');

        $this->tokens->revoke();

        $this->redirectBack('token-revoked');
    }

    /**
     * Save content-handling settings.
     */
    public function handleSettings(): void
    {
        $this->verifyRequest('fopost_settings');

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verifyRequest().
        $policy = isset($_POST['post_status_policy']) ? sanitize_key(wp_unslash($_POST['post_status_policy'])) : Settings::POLICY_HONOR;
        $author = isset($_POST['default_author']) ? absint(wp_unslash($_POST['default_author'])) : 0;
        $type   = isset($_POST['post_type']) ? sanitize_key(wp_unslash($_POST['post_type'])) : 'post';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $this->settings->update($policy, $author, $type);

        $this->redirectBack('settings-saved');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function verifyRequest(string $action): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to manage FoPost settings.', 'fopost'));
        }

        check_admin_referer($action);
    }

    private function redirectBack(string $notice): void
    {
        wp_safe_redirect(
            add_query_arg(
                ['page' => self::PAGE_SLUG, 'fopost-notice' => $notice],
                admin_url('options-general.php'),
            ),
        );
        exit;
    }
}
