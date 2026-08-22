<?php

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Test stubs intentionally mimic WordPress core functions.
// phpcs:disable WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Test stub mirrors core sanitize_text_field().

/**
 * WordPress function stubs for unit testing.
 *
 * Defines minimal WordPress function stubs so that unit tests can run
 * without a full WordPress environment. Each stub is only defined if
 * the function does not already exist.
 *
 * @package Fopost\Wp\Tests
 */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/../' );
}

require_once __DIR__ . '/../vendor/autoload.php';

if (! function_exists('is_wp_error')) {
    function is_wp_error(mixed $thing): bool
    {
        return $thing instanceof WP_Error;
    }
}

if (! function_exists('do_action')) {
    function do_action(string $hookName, mixed ...$args): void
    {
        // No-op stub.
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters(string $hookName, mixed $value, mixed ...$args): mixed
    {
        return $value;
    }
}

// Stateful option store so services that persist options can be unit-tested.
$GLOBALS['fopost_test_options'] = [];

if (! function_exists('get_option')) {
    function get_option(string $option, mixed $default = false): mixed
    {
        return $GLOBALS['fopost_test_options'][$option] ?? $default;
    }
}

if (! function_exists('update_option')) {
    function update_option(string $option, mixed $value, string|bool $autoload = 'yes'): bool
    {
        $GLOBALS['fopost_test_options'][$option] = $value;

        return true;
    }
}

if (! function_exists('add_option')) {
    function add_option(string $option, mixed $value = '', string $deprecated = '', string|bool $autoload = 'yes'): bool
    {
        if (array_key_exists($option, $GLOBALS['fopost_test_options'])) {
            return false;
        }

        $GLOBALS['fopost_test_options'][$option] = $value;

        return true;
    }
}

if (! function_exists('delete_option')) {
    function delete_option(string $option): bool
    {
        unset($GLOBALS['fopost_test_options'][$option]);

        return true;
    }
}

if (! function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $str): string
    {
        return trim(strip_tags($str));
    }
}

if (! function_exists('sanitize_key')) {
    function sanitize_key(string $key): string
    {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }
}

if (! function_exists('absint')) {
    function absint(mixed $maybeint): int
    {
        return abs((int) $maybeint);
    }
}

if (! function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (! function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('wp_json_encode')) {
    function wp_json_encode(mixed $data, int $options = 0, int $depth = 512): string|false
    {
        return json_encode($data, $options, $depth);
    }
}

if (! function_exists('add_query_arg')) {
    function add_query_arg(array $args, string $url): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($args);
    }
}

if (! function_exists('get_current_user_id')) {
    function get_current_user_id(): int
    {
        return $GLOBALS['fopost_test_current_user'] ?? 0;
    }
}

require_once __DIR__ . '/stubs-rest.php';
