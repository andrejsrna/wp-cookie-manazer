<?php
/*
Plugin Name: GDPR Cookie Manažer
Description: Jednoduchý GDPR plugin pre správu cookies 
Version: 1.0.01
Author: Andrej Srna
License: GPL v2 or later
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

ini_set("log_errors", 1);
ini_set("error_log", plugin_dir_path(__FILE__) . 'error.log');

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'GCC_';
    $base_dir = plugin_dir_path(__FILE__) . 'includes/';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . 'class-gcc-' . strtolower($relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Function to run on plugin activation
function gcc_activate_plugin() {
    // Create database table
    require_once plugin_dir_path(__FILE__) . 'includes/class-gcc-activator.php';
    GCC_Activator::activate();
    
    // Clear any existing caches
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}

// Register activation hook
register_activation_hook(__FILE__, 'gcc_activate_plugin');

// Initialize plugin
function gcc_init() {
    $scanner = new GCC_Scanner();
    new GCC_Admin($scanner);
    new GCC_Frontend($scanner);
}
add_action('plugins_loaded', 'gcc_init');

// Move jQuery check to admin_init hook
function gcc_check_jquery() {
    if (!wp_script_is('jquery', 'registered')) {
        error_log('jQuery is not registered!');
    }
}
add_action('admin_init', 'gcc_check_jquery');

// Debug scripts at the right time
function gcc_debug_scripts() {
    global $wp_scripts;
    error_log('Enqueued Scripts:');
    foreach($wp_scripts->queue as $handle) {
        error_log($handle . ' -> ' . $wp_scripts->registered[$handle]->src);
    }
}
add_action('wp_print_scripts', 'gcc_debug_scripts'); 