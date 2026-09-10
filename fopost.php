<?php

/**
 * Plugin Name:       FoPost
 * Plugin URI:        https://fopost.com/docs/sdks/wordpress
 * Description:       Connect this site to FoPost so posts you compose there are published into WordPress.
 * Version:           0.1.1
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            FoPost
 * Author URI:        https://fopost.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fopost
 * Domain Path:       /languages
 */

declare(strict_types=1);

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

// Plugin constants.
define('FOPOST_VERSION', '0.1.1');
define('FOPOST_FILE', __FILE__);
define('FOPOST_DIR', plugin_dir_path(__FILE__));
define('FOPOST_URL', plugin_dir_url(__FILE__));
define('FOPOST_BASENAME', plugin_basename(__FILE__));

// Require Composer autoloader.
if (! file_exists(FOPOST_DIR . 'vendor/autoload.php')) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('FoPost requires Composer dependencies. Please run "composer install" in the plugin directory.', 'fopost');
        echo '</p></div>';
    });

    return;
}

require_once FOPOST_DIR . 'vendor/autoload.php';

$fopost_plugin = \Fopost\Wp\Plugin::instance();

register_activation_hook(__FILE__, [\Fopost\Wp\Activator::class, 'activate']);
register_deactivation_hook(__FILE__, [\Fopost\Wp\Deactivator::class, 'deactivate']);

add_action('plugins_loaded', [$fopost_plugin, 'boot']);
