<?php if (!defined('ABSPATH')) exit; ?>

<div class="gcc-cookie-banner-content">
    <h3><?php _e('Nastavenia súkromia', 'gdpr-cookie-consent'); ?></h3>
    
    <?php foreach ($cookies_by_category as $category => $cookies): ?>
        <div class="gcc-cookie-category">
            <div class="gcc-cookie-category-header">
                <?php if ($category !== 'necessary'): ?>
                    <input type="checkbox" 
                           id="gcc-<?php echo esc_attr($category); ?>" 
                           name="gcc-<?php echo esc_attr($category); ?>"
                           class="gcc-cookie-checkbox">
                <?php endif; ?>
                
                <label for="gcc-<?php echo esc_attr($category); ?>">
                    <?php echo esc_html(ucfirst($category)); ?>
                </label>
                
                <?php if (!empty($cookies)): ?>
                    <button class="gcc-toggle-details" aria-expanded="false">
                        <?php echo esc_html__('Zobraziť cookies', 'gdpr-cookie-consent'); ?> 
                        <span class="gcc-toggle-icon">▼</span>
                    </button>
                <?php endif; ?>
            </div>

            <p class="gcc-category-description">
                <?php echo esc_html($category_descriptions[$category]); ?>
            </p>

            <?php if (!empty($cookies)): ?>
                <div class="gcc-cookie-details" style="display: none;">
                    <table class="gcc-cookie-table">
                        <thead>
                            <tr>
                                <th><?php _e('Názov', 'gdpr-cookie-consent'); ?></th>
                                <th><?php _e('Doména', 'gdpr-cookie-consent'); ?></th>
                                <th><?php _e('Popis', 'gdpr-cookie-consent'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cookies as $cookie): ?>
                                <tr>
                                    <td><?php echo esc_html($cookie->cookie_name); ?></td>
                                    <td><?php echo esc_html($cookie->cookie_domain); ?></td>
                                    <td><?php echo esc_html($cookie->cookie_description); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="gcc-banner-buttons">
        <button id="gcc-accept-all" class="gcc-btn gcc-btn-primary">
            <?php _e('Prijať všetko', 'gdpr-cookie-consent'); ?>
        </button>
        <button id="gcc-reject-all" class="gcc-btn gcc-btn-secondary">
            <?php _e('Odmietnuť všetko', 'gdpr-cookie-consent'); ?>
        </button>
        <button id="gcc-save-preferences" class="gcc-btn gcc-btn-secondary">
            <?php _e('Uložiť nastavenia', 'gdpr-cookie-consent'); ?>
        </button>
    </div>
</div> 