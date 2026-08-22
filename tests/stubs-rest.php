<?php

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Test stubs intentionally mimic WordPress core functions.
// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound -- Grouped WP core class stubs.

/**
 * WordPress REST + post function stubs for unit testing the FoPost endpoints.
 *
 * Loaded by tests/bootstrap.php. Each stub is only defined when the real
 * WordPress implementation is absent. In-memory post/meta stores live in
 * $GLOBALS so tests can reset and inspect them.
 *
 * @package Fopost\Wp\Tests
 */

$GLOBALS['fopost_test_posts'] = [];
$GLOBALS['fopost_test_meta']  = [];
$GLOBALS['fopost_test_users'] = [1];

if (! class_exists('WP_Error')) {
    class WP_Error
    {
        /** @var array<string, mixed> */
        private array $data = [];

        public function __construct(
            private string $code = '',
            private string $message = '',
            mixed $data = null,
        ) {
            if (is_array($data)) {
                $this->data = $data;
            }
        }

        public function get_error_code(): string
        {
            return $this->code;
        }

        public function get_error_message(): string
        {
            return $this->message;
        }

        public function get_error_data(): mixed
        {
            return $this->data;
        }

        public function add_data(mixed $data): void
        {
            $this->data = is_array($data) ? array_merge($this->data, $data) : $this->data;
        }
    }
}

if (! class_exists('WP_Post')) {
    class WP_Post
    {
        public int $ID           = 0;
        public string $post_status  = 'publish';
        public string $post_type    = 'post';
        public string $post_title   = '';
        public string $post_content = '';

        /**
         * @param array<string, mixed> $data
         */
        public function __construct(array $data = [])
        {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
}

if (! class_exists('WP_REST_Server')) {
    class WP_REST_Server
    {
        public const READABLE  = 'GET';
        public const CREATABLE = 'POST';
        public const DELETABLE = 'DELETE';
    }
}

if (! class_exists('WP_REST_Request')) {
    class WP_REST_Request
    {
        /**
         * @param array<string, mixed>  $params
         * @param array<string, string> $headers
         */
        public function __construct(
            private array $params = [],
            private array $headers = [],
            private string $body = '',
        ) {
            $this->headers = array_change_key_case($headers, CASE_LOWER);
        }

        public function get_param(string $key): mixed
        {
            return $this->params[$key] ?? null;
        }

        public function get_header(string $key): ?string
        {
            return $this->headers[strtolower($key)] ?? null;
        }

        public function get_body(): string
        {
            return $this->body;
        }
    }
}

if (! class_exists('WP_REST_Response')) {
    class WP_REST_Response
    {
        public function __construct(
            public mixed $data = null,
            public int $status = 200,
        ) {
        }

        public function get_data(): mixed
        {
            return $this->data;
        }

        public function get_status(): int
        {
            return $this->status;
        }
    }
}

if (! function_exists('register_rest_route')) {
    function register_rest_route(string $namespace, string $route, array $args = []): bool
    {
        return true;
    }
}

if (! function_exists('get_userdata')) {
    function get_userdata(int $userId): object|false
    {
        return in_array($userId, $GLOBALS['fopost_test_users'], true)
            ? (object) ['ID' => $userId]
            : false;
    }
}

if (! function_exists('post_type_exists')) {
    function post_type_exists(string $postType): bool
    {
        return in_array($postType, ['post', 'page'], true);
    }
}

if (! function_exists('wp_kses_post')) {
    function wp_kses_post(string $content): string
    {
        // Minimal mimic: strip script/style blocks and event-handler attributes.
        $content = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#si', '', $content) ?? '';

        return preg_replace('#\son\w+="[^"]*"#i', '', $content) ?? '';
    }
}

if (! function_exists('wp_slash')) {
    function wp_slash(mixed $value): mixed
    {
        return $value;
    }
}

if (! function_exists('wp_insert_post')) {
    function wp_insert_post(array $postarr, bool $wpError = false): int|WP_Error
    {
        static $nextId = 100;

        $id = $nextId++;

        $GLOBALS['fopost_test_posts'][$id] = new WP_Post([
            'ID'          => $id,
            'post_status' => $postarr['post_status'] ?? 'draft',
            'post_type'   => $postarr['post_type'] ?? 'post',
            'post_title'  => $postarr['post_title'] ?? '',
        ]);
        $GLOBALS['fopost_test_posts'][$id]->post_content = $postarr['post_content'] ?? '';

        foreach (($postarr['meta_input'] ?? []) as $key => $value) {
            $GLOBALS['fopost_test_meta'][$id][$key] = $value;
        }

        return $id;
    }
}

if (! function_exists('get_post')) {
    function get_post(int $id): ?WP_Post
    {
        return $GLOBALS['fopost_test_posts'][$id] ?? null;
    }
}

if (! function_exists('get_post_status')) {
    function get_post_status(int $id): string|false
    {
        return $GLOBALS['fopost_test_posts'][$id]->post_status ?? false;
    }
}

if (! function_exists('get_post_type')) {
    function get_post_type(int $id): string|false
    {
        return $GLOBALS['fopost_test_posts'][$id]->post_type ?? false;
    }
}

if (! function_exists('get_post_meta')) {
    function get_post_meta(int $id, string $key, bool $single = false): mixed
    {
        return $GLOBALS['fopost_test_meta'][$id][$key] ?? '';
    }
}

if (! function_exists('set_post_thumbnail')) {
    function set_post_thumbnail(int $postId, int $attachmentId): bool
    {
        $GLOBALS['fopost_test_meta'][$postId]['_thumbnail_id'] = $attachmentId;

        return true;
    }
}

if (! function_exists('get_permalink')) {
    function get_permalink(int $id): string
    {
        return "https://example.test/?p={$id}";
    }
}

if (! function_exists('admin_url')) {
    function admin_url(string $path = ''): string
    {
        return 'https://example.test/wp-admin/' . $path;
    }
}

if (! function_exists('wp_trash_post')) {
    function wp_trash_post(int $id): WP_Post|false
    {
        $post = $GLOBALS['fopost_test_posts'][$id] ?? null;
        if ($post === null) {
            return false;
        }

        $post->post_status = 'trash';

        return $post;
    }
}

if (! function_exists('wp_delete_post')) {
    function wp_delete_post(int $id, bool $force = false): WP_Post|false
    {
        $post = $GLOBALS['fopost_test_posts'][$id] ?? null;
        if ($post === null) {
            return false;
        }

        unset($GLOBALS['fopost_test_posts'][$id]);

        return $post;
    }
}

if (! function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = ''): string
    {
        return $show === 'version' ? '6.9' : 'Test Site';
    }
}

if (! function_exists('home_url')) {
    function home_url(string $path = ''): string
    {
        return 'https://example.test' . $path;
    }
}

if (! function_exists('get_site_icon_url')) {
    function get_site_icon_url(): string
    {
        return '';
    }
}

if (! function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field(string $str): string
    {
        return trim(strip_tags($str)); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
    }
}

if (! function_exists('sanitize_title')) {
    function sanitize_title(string $title): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9-]+/', '-', $title) ?? '');
    }
}

if (! function_exists('sanitize_file_name')) {
    function sanitize_file_name(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename) ?? '';
    }
}
