<?php

declare(strict_types=1);

namespace Fopost\Wp;

defined( 'ABSPATH' ) || exit;

use Fopost\Wp\Admin\SettingsPage;
use Fopost\Wp\Rest\RestController;

/**
 * Main plugin class. Wires the site-token connection and registers hooks.
 */
class Plugin
{
    private static ?self $instance = null;

    private ?TokenService $tokens = null;
    private ?Settings $settings = null;

    private bool $booted = false;

    private function __construct()
    {
        // Singleton, use Plugin::instance().
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Boot the plugin: register hooks.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        if (is_admin()) {
            $page = new SettingsPage($this->tokens(), $this->settings());
            add_action('admin_menu', [$page, 'register']);
            $page->registerActions();
        }

        add_action('rest_api_init', [RestController::class, 'register']);
    }

    public function tokens(): TokenService
    {
        if ($this->tokens === null) {
            $this->tokens = new TokenService();
        }

        return $this->tokens;
    }

    public function settings(): Settings
    {
        if ($this->settings === null) {
            $this->settings = new Settings($this->tokens());
        }

        return $this->settings;
    }

    private function __clone()
    {
    }
}
