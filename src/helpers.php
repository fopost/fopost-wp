<?php

declare(strict_types=1);

// Prevent direct access. `return` (not `exit`) so the Composer `files`
// autoload does not kill CLI tools like PHPUnit that load outside WordPress.
if (! defined('ABSPATH')) {
    return;
}

use Fopost\Wp\Plugin;

if (! function_exists('fopost_is_connected')) {
    /**
     * Whether a FoPost site token has been generated for this site.
     */
    function fopost_is_connected(): bool
    {
        return Plugin::instance()->tokens()->isPaired();
    }
}
