<?php
class GCC_Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gcc_cookies';
        
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

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
} 