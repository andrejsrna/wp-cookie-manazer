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
        wp_enqueue_style(
            'gcc-styles',
            plugins_url('assets/css/style.css', dirname(__FILE__)),
            array(),
            time() // Force cache refresh during development
        );

        wp_enqueue_script(
            'gcc-scripts',
            plugins_url('assets/js/cookie-consent.js', dirname(__FILE__)),
            array('jquery'),
            time(), // Force cache refresh during development
            true
        );

        wp_localize_script('gcc-scripts', 'gccData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gcc_nonce'),
            'isConsented' => !empty($_COOKIE['gcc_cookie_consent']),
            'cookieValue' => isset($_COOKIE['gcc_cookie_consent']) ? $_COOKIE['gcc_cookie_consent'] : null
        ));
    }

    public function add_cookie_banner() {
        $cookies = $this->scanner->get_all_cookies();
        $cookies_by_category = $this->group_cookies_by_category($cookies);
        require_once plugin_dir_path(__FILE__) . '../templates/cookie-banner.php';
    }

    private function group_cookies_by_category($cookies) {
        $grouped = array();
        foreach ($cookies as $cookie) {
            $grouped[$cookie->cookie_category][] = $cookie;
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
            ),
            
            // Necessary scripts
            'necessary' => array(
                'jquery', 'wp-', 'wordpress',
                'woocommerce', 'elementor', 'contact-form',
                'recaptcha', 'captcha', 'security'
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
        
        // If script doesn't match any known category and isn't our own script
        if (!strpos($src_lower, 'gcc-scripts')) {
            return str_replace(
                '<script',
                '<script type="text/plain" data-cookiecategory="others"',
                $tag
            );
        }
        
        return $tag;
    }

    public function test_banner_shortcode() {
        return '<button onclick="GCCookieConsent.showBanner()">Test Banner</button>';
    }
} 