<?php
class GCC_Scanner {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'gcc_cookies';
    }

    public function scan_cookies() {
        $this->ensure_table_exists();
        
        $site_url = get_site_url();
        $response = wp_remote_get($site_url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $cookies = wp_remote_retrieve_cookies($response);
        
        foreach ($cookies as $cookie) {
            $this->save_cookie($cookie);
        }
        
        return true;
    }

    private function save_cookie($cookie) {
        $cookie_name = $cookie->name;
        $cookie_domain = $cookie->domain;
        $category = $this->determine_category($cookie_name);
        
        $this->wpdb->replace(
            $this->table_name,
            array(
                'cookie_name' => $cookie_name,
                'cookie_domain' => $cookie_domain,
                'cookie_category' => $category,
                'last_detected' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }

    private function determine_category($cookie_name) {
        $cookie_name = strtolower($cookie_name);
        
        // Analytics cookies
        $analytics_patterns = array(
            'ga', 'analytics', '_ga', '_gid', '_gat',
            'statistic', 'stats', '_pk_', 'plausible',
            'matomo', 'piwik', '_clck', 'clarity',
            'hotjar', '_hj', 'optimizely'
        );
        
        // Marketing cookies
        $marketing_patterns = array(
            'ad', 'ads', 'fbp', 'pixel',
            'bing', 'doubleclick', '__gads',
            'facebook', 'linkedin', '_fbp',
            'instagram', 'pinterest', '_pin_',
            'twitter', '_tt_', 'criteo',
            'taboola', 'outbrain', 'adroll'
        );
        
        // Necessary cookies
        $necessary_patterns = array(
            'wordpress', 'wp-', 'wc_',
            'phpsessid', 'csrf', 'xsrf',
            'session', 'cart', 'logged_in',
            'security', 'auth', 'token',
            'woocommerce', 'elementor'
        );

        // Check analytics patterns
        foreach ($analytics_patterns as $pattern) {
            if (strpos($cookie_name, $pattern) !== false) {
                return 'analytics';
            }
        }
        
        // Check marketing patterns
        foreach ($marketing_patterns as $pattern) {
            if (strpos($cookie_name, $pattern) !== false) {
                return 'marketing';
            }
        }
        
        // Check necessary patterns
        foreach ($necessary_patterns as $pattern) {
            if (strpos($cookie_name, $pattern) !== false) {
                return 'necessary';
            }
        }
        
        // Default to others if no match found
        return 'others';
    }

    public function get_all_cookies() {
        $this->ensure_table_exists();
        return $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY cookie_category, cookie_name"
        );
    }

    public function translate_category($category) {
        $translations = array(
            'necessary' => 'Nevyhnutné',
            'analytics' => 'Analytické',
            'marketing' => 'Marketingové',
            'others' => 'Ostatné'
        );
        
        return isset($translations[$category]) ? $translations[$category] : $category;
    }

    private function ensure_table_exists() {
        global $wpdb;
        $table_name = $this->table_name;
        
        // Check if table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );
        
        if (!$table_exists) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                cookie_name varchar(255) NOT NULL,
                cookie_domain varchar(255) NOT NULL,
                cookie_category varchar(50) NOT NULL,
                cookie_description text,
                last_detected datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            dbDelta($sql);
        }
    }
} 