<?php
/**
 * Plugin Name: VMC Simple Cache
 * Plugin URI: https://vmcsoft.com/plugins/vmc-simple-cache
 * Description: A lightweight, file-based caching solution for WordPress. Cache your front page, blog page, and individual posts with configurable TTL and debug mode.
 * Version: 1.0.0
 * Author: VMCSoft
 * Author URI: https://vmcsoft.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vmc-simple-cache
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * GitHub Plugin URI: https://github.com/vmcsoft/vmc-simple-cache
 * Support: https://vmcsoft.com/support
 * Support Email: info@vmcsoft.com
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('VMC_SIMPLE_CACHE_VERSION', '1.0.0');
define('VMC_SIMPLE_CACHE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VMC_SIMPLE_CACHE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VMC_SIMPLE_CACHE_CACHE_DIR', WP_CONTENT_DIR . '/cache/vmc-simple-cache');

// Autoloader
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'VMC_Simple_Cache\\';
    $base_dir = VMC_SIMPLE_CACHE_PLUGIN_DIR . 'includes/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function vmc_simple_cache_init() {
    // Create cache directory if it doesn't exist
    if (!file_exists(VMC_SIMPLE_CACHE_CACHE_DIR)) {
        wp_mkdir_p(VMC_SIMPLE_CACHE_CACHE_DIR);
    }

    // Initialize classes
    $admin = new \VMC_Simple_Cache\Admin();
    $admin->init();

    $post_cache = new \VMC_Simple_Cache\Post_Cache();
    $post_cache->init();
}
add_action('plugins_loaded', 'vmc_simple_cache_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create cache directory
    if (!file_exists(VMC_SIMPLE_CACHE_CACHE_DIR)) {
        wp_mkdir_p(VMC_SIMPLE_CACHE_CACHE_DIR);
    }

    // Add .htaccess rules for cache directory
    $htaccess_file = VMC_SIMPLE_CACHE_CACHE_DIR . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        $htaccess_content = "Order deny,allow\nDeny from all";
        file_put_contents($htaccess_file, $htaccess_content);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clear cache on deactivation
    $cache_manager = new \VMC_Simple_Cache\Cache_Manager();
    $cache_manager->clear_cache();
}); 