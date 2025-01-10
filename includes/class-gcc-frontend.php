<?php
class GCC_Frontend {
    private $scanner;

    public function __construct(GCC_Scanner $scanner) {
        $this->scanner = $scanner;
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_body_open', array($this, 'add_cookie_banner'));
        add_shortcode('cookie_settings', array($this, 'settings_button_shortcode'));
        add_shortcode('test_cookie_banner', array($this, 'test_banner_shortcode'));
        
        // Add filter for script tags
        add_filter('script_loader_tag', array($this, 'filter_scripts'), 10, 3);
    }

    public function enqueue_scripts() {
        // Only enqueue on frontend, not in admin
        if (is_admin()) {
            return;
        }

        $js_path = plugins_url('assets/js/cookie-consent.js', dirname(__FILE__));
        $css_path = plugins_url('assets/css/style.css', dirname(__FILE__));
        
        // Get plugin version for cache busting
        $plugin_data = get_file_data(dirname(dirname(__FILE__)) . '/gdpr-cookie-consent.php', array('Version' => 'Version'));
        $version = $plugin_data['Version'] ? $plugin_data['Version'] : '1.0.0';

        wp_enqueue_style(
            'gcc-styles',
            $css_path,
            array(),
            $version
        );

        wp_enqueue_script(
            'gcc-scripts',
            $js_path,
            array('jquery'),
            $version,
            true
        );

        // Get the actual cookie value and validate it's JSON
        $cookie_value = isset($_COOKIE['gcc_cookie_consent']) ? $_COOKIE['gcc_cookie_consent'] : '';
        $consent_data = null;
        
        if (!empty($cookie_value)) {
            $consent_data = json_decode($cookie_value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $cookie_value = '';
                $consent_data = null;
            }
        }
        
        // Add translations for the JS
        wp_localize_script('gcc-scripts', 'gccData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gcc_nonce'),
            'isConsented' => !empty($consent_data), // Only true if valid JSON
            'cookieValue' => $consent_data,
            'translations' => array(
                'showCookies' => __('Zobraziť cookies', 'gdpr-cookie-consent'),
                'hideCookies' => __('Skryť cookies', 'gdpr-cookie-consent')
            )
        ));
    }

    public function add_cookie_banner() {
        // Debug output
        error_log('Adding cookie banner');
        
        $cookies = $this->scanner->get_all_cookies();
        $cookies_by_category = $this->group_cookies_by_category($cookies);
        
        // Add descriptions for each category
        $category_descriptions = array(
            'necessary' => __('Nevyhnutné cookies sú potrebné pre správne fungovanie stránky.', 'gdpr-cookie-consent'),
            'analytics' => __('Analytické cookies nám pomáhajú pochopiť, ako používate našu stránku.', 'gdpr-cookie-consent'),
            'marketing' => __('Marketingové cookies sa používajú na sledovanie návštevníkov na webových stránkach.', 'gdpr-cookie-consent'),
            'others' => __('Ostatné cookies, ktoré nie sú zaradené do žiadnej kategórie.', 'gdpr-cookie-consent')
        );
        
        // Ensure the banner is visible by default
        echo '<div id="gcc-cookie-banner" style="display: block; position: fixed; bottom: 0; left: 0; right: 0; z-index: 999999;">';
        require_once plugin_dir_path(__FILE__) . '../templates/cookie-banner.php';
        echo '</div>';
    }

    private function group_cookies_by_category($cookies) {
        $grouped = array(
            'necessary' => array(),
            'analytics' => array(),
            'marketing' => array(),
            'others' => array()
        );
        
        foreach ($cookies as $cookie) {
            if (isset($grouped[$cookie->cookie_category])) {
                $grouped[$cookie->cookie_category][] = $cookie;
            } else {
                $grouped['others'][] = $cookie;
            }
        }
        
        return $grouped;
    }

    public function settings_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'class' => '',
            'text' => __('Nastavenia cookies', 'gdpr-cookie-consent')
        ), $atts);

        return sprintf(
            '<button class="consentsettings gcc-btn gcc-btn-secondary %s">%s</button>',
            esc_attr($atts['class']),
            esc_html($atts['text'])
        );
    }

    public function filter_scripts($tag, $handle, $src) {
        // First, explicitly check if this is our cookie consent script
        if ($handle === 'gcc-scripts' || $handle === 'jquery') {
            return $tag; // Return original tag without modification for our script and jQuery
        }

        // List of scripts to be controlled by cookie consent
        $controlled_scripts = array(
            // Analytics scripts
            'analytics' => array(
                'google-analytics', 'gtag', 'analytics',
                'matomo', 'piwik', 'plausible',
                'hotjar', 'clarity', 'optimizely',
                'segment', 'mixpanel', 'statcounter'
            ),
            
            // Marketing scripts
            'marketing' => array(
                'facebook-pixel', 'google-ads', 'fbevents',
                'doubleclick', 'adroll', 'twitter',
                'linkedin', 'pinterest', 'criteo',
                'taboola', 'outbrain', 'bing'
            )
        );
        
        $src_lower = strtolower($src);
        
        // Check each category
        foreach ($controlled_scripts as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($src_lower, strtolower($pattern)) !== false) {
                    return str_replace(
                        '<script',
                        '<script type="text/plain" data-cookiecategory="' . $category . '"',
                        $tag
                    );
                }
            }
        }
        
        // If script doesn't match any controlled categories, return it unmodified
        return $tag;
    }

    public function test_banner_shortcode() {
        return '<button onclick="GCCookieConsent.showBanner()">Test Banner</button>';
    }
} 