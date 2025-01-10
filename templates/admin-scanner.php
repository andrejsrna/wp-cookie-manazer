<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Skener cookies', 'gdpr-cookie-consent'); ?></h1>
    
    <form method="post">
        <?php wp_nonce_field('gcc_scan_cookies'); ?>
        <input type="submit" name="gcc_scan_cookies" class="button button-primary" value="<?php _e('Spustiť skenovanie', 'gdpr-cookie-consent'); ?>">
    </form>
    
    <h2><?php _e('Nájdené cookies', 'gdpr-cookie-consent'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Názov cookie', 'gdpr-cookie-consent'); ?></th>
                <th><?php _e('Doména', 'gdpr-cookie-consent'); ?></th>
                <th><?php _e('Kategória', 'gdpr-cookie-consent'); ?></th>
                <th><?php _e('Naposledy detekované', 'gdpr-cookie-consent'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cookies)): ?>
                <?php foreach ($cookies as $cookie): ?>
                <tr>
                    <td><?php echo esc_html($cookie->cookie_name); ?></td>
                    <td><?php echo esc_html($cookie->cookie_domain); ?></td>
                    <td><?php echo esc_html($this->translate_category($cookie->cookie_category)); ?></td>
                    <td><?php echo esc_html($cookie->last_detected); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4"><?php _e('Zatiaľ neboli nájdené žiadne cookies. Spustite skenovanie pre ich detekciu.', 'gdpr-cookie-consent'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div> 