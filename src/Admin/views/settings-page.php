<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/** @var bool $isPaired */
/** @var ?string $revealedToken */
/** @var array{hint: string, created_at: ?int, created_by: ?int, last_used_at: ?int} $tokenInfo */
/** @var \Fopost\Wp\Settings $settings */

$fopost_notice = isset($_GET['fopost-notice']) ? sanitize_key(wp_unslash($_GET['fopost-notice'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="wrap fopost-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if ($fopost_notice === 'token-revoked'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Site token revoked. FoPost can no longer publish to this site.', 'fopost'); ?></p>
        </div>
    <?php elseif ($fopost_notice === 'settings-saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'fopost'); ?></p>
        </div>
    <?php endif; ?>

    <p>
        <?php esc_html_e('Connect this site to FoPost to receive posts you compose there. Generate a site token below, then paste it together with your site URL into FoPost when connecting WordPress. The token only allows FoPost to create posts, upload images, and remove posts it created, nothing else.', 'fopost'); ?>
    </p>

    <?php if (! $isPaired): ?>
        <div class="notice notice-info">
            <h2><?php esc_html_e('Do not have a FoPost account yet?', 'fopost'); ?></h2>
            <p>
                <?php esc_html_e('FoPost is the hosted dashboard this token pairs with. Compose once, schedule ahead on a shared calendar, and see what performed. WordPress becomes one destination among many, so a single post can reach this site and your social accounts at once.', 'fopost'); ?>
            </p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url(\Fopost\Wp\Admin\SettingsPage::url('/register', 'settings-page')); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Start a free trial', 'fopost'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(\Fopost\Wp\Admin\SettingsPage::url('/platforms', 'settings-page-platforms')); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('See all platforms', 'fopost'); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <h2><?php esc_html_e('Site Token', 'fopost'); ?></h2>

    <?php if (is_string($revealedToken)): ?>
        <div class="notice notice-success">
            <p><strong><?php esc_html_e('Your new site token, copy it now, it will not be shown again:', 'fopost'); ?></strong></p>
            <p><code style="font-size:14px;user-select:all;word-break:break-all;"><?php echo esc_html($revealedToken); ?></code></p>
        </div>
    <?php endif; ?>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><?php esc_html_e('Status', 'fopost'); ?></th>
            <td>
                <?php if ($isPaired): ?>
                    <strong><?php esc_html_e('Token active', 'fopost'); ?></strong>
                    <?php if ($tokenInfo['hint'] !== ''): ?>
                        <code><?php echo esc_html($tokenInfo['hint']); ?>…</code>
                    <?php endif; ?>
                    <?php if ($tokenInfo['created_at'] !== null): ?>
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: date the token was generated */
                                esc_html__('Generated on %s.', 'fopost'),
                                esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $tokenInfo['created_at'])),
                            );
                            if ($tokenInfo['last_used_at'] !== null) {
                                echo ' ';
                                printf(
                                    /* translators: %s: date the token was last used */
                                    esc_html__('Last used on %s.', 'fopost'),
                                    esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $tokenInfo['last_used_at'])),
                                );
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <strong><?php esc_html_e('Not connected', 'fopost'); ?></strong>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:8px;">
        <?php wp_nonce_field('fopost_generate'); ?>
        <input type="hidden" name="action" value="fopost_generate" />
        <?php submit_button($isPaired ? __('Regenerate Token', 'fopost') : __('Generate Token', 'fopost'), 'primary', 'submit', false); ?>
    </form>

    <?php if ($isPaired): ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;">
            <?php wp_nonce_field('fopost_revoke'); ?>
            <input type="hidden" name="action" value="fopost_revoke" />
            <?php submit_button(__('Revoke Token', 'fopost'), 'delete', 'submit', false); ?>
        </form>

        <p class="description" style="margin-top:12px;">
            <?php esc_html_e('Regenerating or revoking the token immediately disconnects FoPost until the new token is saved there.', 'fopost'); ?>
        </p>
    <?php endif; ?>

    <h2><?php esc_html_e('Incoming Content', 'fopost'); ?></h2>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('fopost_settings'); ?>
        <input type="hidden" name="action" value="fopost_settings" />

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="fopost-policy"><?php esc_html_e('Post Status', 'fopost'); ?></label>
                </th>
                <td>
                    <select id="fopost-policy" name="post_status_policy">
                        <option value="honor" <?php selected($settings->statusPolicy(), 'honor'); ?>>
                            <?php esc_html_e('Use the status FoPost requests', 'fopost'); ?>
                        </option>
                        <option value="draft" <?php selected($settings->statusPolicy(), 'draft'); ?>>
                            <?php esc_html_e('Always save as draft', 'fopost'); ?>
                        </option>
                        <option value="publish" <?php selected($settings->statusPolicy(), 'publish'); ?>>
                            <?php esc_html_e('Always publish immediately', 'fopost'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Choose "Always save as draft" to review every post in WordPress before it goes live.', 'fopost'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="fopost-author"><?php esc_html_e('Post Author', 'fopost'); ?></label>
                </th>
                <td>
                    <?php
                    wp_dropdown_users([
                        'id'       => 'fopost-author',
                        'name'     => 'default_author',
                        'selected' => $settings->defaultAuthor(),
                        'who'      => 'authors',
                    ]);
                    ?>
                    <p class="description"><?php esc_html_e('Posts from FoPost are attributed to this user.', 'fopost'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="fopost-post-type"><?php esc_html_e('Post Type', 'fopost'); ?></label>
                </th>
                <td>
                    <select id="fopost-post-type" name="post_type">
                        <?php foreach (get_post_types(['public' => true], 'objects') as $fopost_type): ?>
                            <?php if ($fopost_type->name === 'attachment') { continue; } ?>
                            <option value="<?php echo esc_attr($fopost_type->name); ?>" <?php selected($settings->postType(), $fopost_type->name); ?>>
                                <?php echo esc_html($fopost_type->labels->singular_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Settings', 'fopost')); ?>
    </form>
</div>
