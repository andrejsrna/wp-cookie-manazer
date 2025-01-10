<?php
/*
Plugin Name: GDPR Cookie Manažer
Description: Jednoduchý GDPR plugin pre správu cookies 
Version: 1.0.0
Author: Andrej Srna
License: GPL v2 or later
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

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