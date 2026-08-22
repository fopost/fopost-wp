<?php

/**
 * FoPost uninstall.
 *
 * Fired when the plugin is deleted. Removes options, post meta, and transients.
 *
 * @package Fopost\Wp
 */

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if (! class_exists(\Fopost\Wp\Uninstaller::class)) {
    // Minimal fallback cleanup without the autoloader.
    delete_option('fopost_connection');
    delete_option('fopost_db_version');

    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
            '_fopost_%'
        )
    );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_fopost_%',
            '_transient_timeout_fopost_%'
        )
    );

    return;
}

\Fopost\Wp\Uninstaller::uninstall();
