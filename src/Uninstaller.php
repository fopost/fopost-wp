<?php

declare(strict_types=1);

namespace Fopost\Wp;

defined( 'ABSPATH' ) || exit;

/**
 * Removes every trace of the plugin: options, post meta, transients.
 */
class Uninstaller
{
    /**
     * Run on plugin uninstall.
     */
    public static function uninstall(): void
    {
        self::removeOptions();
        self::removePostMeta();
        self::clearTransients();
    }

    private static function removeOptions(): void
    {
        delete_option(TokenService::OPTION_KEY);
        delete_option('fopost_db_version');
    }

    private static function removePostMeta(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
                '_fopost_%'
            )
        );
    }

    private static function clearTransients(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_fopost_%',
                '_transient_timeout_fopost_%'
            )
        );
    }
}
