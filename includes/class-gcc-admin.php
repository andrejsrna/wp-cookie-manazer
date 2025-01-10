<?php
class GCC_Admin {
    private $scanner;

    public function __construct(GCC_Scanner $scanner) {
        $this->scanner = $scanner;
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_options_page(
            __('GDPR Skener cookies', 'gdpr-cookie-consent'),
            __('Skener cookies', 'gdpr-cookie-consent'),
            'manage_options',
            'gcc-cookie-scanner',
            array($this, 'render_scanner_page')
        );
    }

    public function render_scanner_page() {
        if (isset($_POST['gcc_scan_cookies']) && check_admin_referer('gcc_scan_cookies')) {
            $this->scanner->scan_cookies();
            echo '<div class="notice notice-success"><p>' . 
                 __('Cookie scan completed!', 'gdpr-cookie-consent') . 
                 '</p></div>';
        }
        
        $cookies = $this->scanner->get_all_cookies();
        require_once plugin_dir_path(__FILE__) . '../templates/admin-scanner.php';
    }
} 