<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all cache files
$cache_dir = plugin_dir_path(__FILE__) . 'cache/';
$files = glob($cache_dir . '*');

foreach ($files as $file) {
    if (is_file($file) && basename($file) !== '.htaccess') {
        unlink($file);
    }
}

// Delete plugin options
delete_option('vmc_simple_cache_version');
delete_option('vmc_simple_cache_expiration'); 