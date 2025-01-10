<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Add ID for debugging -->
<div id="gcc-cookie-banner-debug"></div>
<script>
    console.log('Banner template loaded');
</script>
<div id="gcc-cookie-banner" class="gcc-overlay" style="display: none;" role="dialog" aria-labelledby="cookie-consent-title">
    <div class="gcc-modal">
        <h2 id="cookie-consent-title"><?php _e('Nastavenia cookies', 'gdpr-cookie-consent'); ?></h2>
        <div class="gcc-cookie-content">
            <p><?php _e('Vážime si vaše súkromie. Vyberte si, ako chcete, aby sme používali cookies na zlepšenie vašich skúseností.', 'gdpr-cookie-consent'); ?></p>
            
            <div class="gcc-cookie-options" role="group" aria-label="Možnosti cookies">
                <?php 
                $categories = array(
                    'necessary' => 'Nevyhnutné',
                    'analytics' => 'Analytické',
                    'marketing' => 'Marketingové',
                    'others' => 'Ostatné'
                );
                
                $descriptions = array(
                    'necessary' => 'Potrebné pre správne fungovanie webstránky. Nie je možné ich vypnúť.',
                    'analytics' => 'Pomáhajú nám pochopiť, ako návštevníci používajú našu webstránku.',
                    'marketing' => 'Používané na zobrazovanie personalizovaných reklám.',
                    'others' => 'Cookies, ktoré nebolo možné zaradiť do iných kategórií.'
                );
                ?>
                
                <?php foreach ($categories as $category_key => $category_name): ?>
                    <div class="gcc-cookie-option">
                        <label>
                            <input type="checkbox" 
                                   id="gcc-<?php echo esc_attr($category_key); ?>" 
                                   <?php echo $category_key === 'necessary' ? 'checked disabled' : ''; ?>
                                   aria-describedby="<?php echo esc_attr($category_key); ?>-desc">
                            <span><?php echo esc_html($category_name); ?> cookies</span>
                        </label>
                        <p id="<?php echo esc_attr($category_key); ?>-desc" class="gcc-description">
                            <?php echo esc_html($descriptions[$category_key]); ?>
                        </p>
                        
                        <?php if (!empty($cookies_by_category[$category_key])): ?>
                            <button class="gcc-toggle-details" 
                                    aria-expanded="false" 
                                    aria-controls="details-<?php echo esc_attr($category_key); ?>">
                                <?php _e('Zobraziť cookies', 'gdpr-cookie-consent'); ?> 
                                <span class="gcc-toggle-icon">▼</span>
                            </button>
                            <div id="details-<?php echo esc_attr($category_key); ?>" 
                                 class="gcc-cookie-details" 
                                 style="display: none;">
                                <ul class="gcc-cookie-list">
                                    <?php foreach ($cookies_by_category[$category_key] as $cookie): ?>
                                        <li>
                                            <strong><?php echo esc_html($cookie->cookie_name); ?></strong>
                                            <?php if (!empty($cookie->cookie_description)): ?>
                                                <div class="gcc-cookie-description">
                                                    <?php echo esc_html($cookie->cookie_description); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($cookie->cookie_duration)): ?>
                                                <div class="gcc-cookie-duration">
                                                    <?php _e('Doba trvania:', 'gdpr-cookie-consent'); ?> 
                                                    <?php echo esc_html($cookie->cookie_duration); ?>
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="gcc-buttons">
                <button id="gcc-accept-all" class="gcc-btn gcc-btn-primary"><?php _e('Prijať všetko', 'gdpr-cookie-consent'); ?></button>
                <button id="gcc-save-preferences" class="gcc-btn gcc-btn-secondary"><?php _e('Uložiť nastavenia', 'gdpr-cookie-consent'); ?></button>
                <button id="gcc-reject-all" class="gcc-btn gcc-btn-secondary"><?php _e('Odmietnuť všetko', 'gdpr-cookie-consent'); ?></button>
            </div>
        </div>
    </div>
</div> 