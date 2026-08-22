<?php

declare(strict_types=1);

namespace Fopost\Wp;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation tasks.
 */
class Activator
{
    /**
     * Run on plugin activation.
     */
    public static function activate(): void
    {
        (new TokenService())->migrateLegacyConnection();

        if (get_option('fopost_db_version') === false) {
            add_option('fopost_db_version', '1.0.0');
        }
    }
}
