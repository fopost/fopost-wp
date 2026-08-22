<?php

declare(strict_types=1);

namespace Fopost\Wp;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin deactivation tasks.
 *
 * The connection is left in place so reactivating restores it. Use
 * Revoke Token on the settings screen to break the pairing instead.
 */
class Deactivator
{
    /**
     * Run on plugin deactivation.
     */
    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('fopost_cleanup');
    }
}
