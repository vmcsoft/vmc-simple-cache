<?php
namespace VMC_Simple_Cache;

class Cache_Manager {
    /**
     * Cache expiration time in seconds (default: 1 hour)
     */
    private $expiration = 3600;

    /**
     * Initialize the cache manager
     */
    public function init() {
        // Add cache clearing hooks
        add_action('save_post', array($this, 'clear_cache'));
        add_action('comment_post', array($this, 'clear_cache'));
        add_action('wp_trash_post', array($this, 'clear_cache'));
        add_action('switch_theme', array($this, 'clear_cache'));
    }

    /**
     * Set cache expiration time
     *
     * @param int $seconds Cache expiration time in seconds
     */
    public function set_expiration($seconds) {
        $this->expiration = (int) $seconds;
    }

    /**
     * Get cache expiration time
     *
     * @return int Cache expiration time in seconds
     */
    public function get_expiration() {
        return $this->expiration;
    }

    /**
     * Get cache expiration time for a specific key
     *
     * @param string $key Cache key
     * @return int|false Expiration timestamp or false if not found
     */
    public function get_expiration_time($key) {
        $cache_file = $this->get_cache_file($key);
        
        if (!file_exists($cache_file)) {
            return false;
        }

        $data = file_get_contents($cache_file);
        $cache_data = json_decode($data, true);

        if (!$cache_data || !isset($cache_data['expires'])) {
            return false;
        }

        return $cache_data['expires'];
    }

    /**
     * Get cached content
     *
     * @param string $key Cache key
     * @return mixed|false Cached content or false if not found/expired
     */
    public function get($key) {
        $cache_file = $this->get_cache_file($key);
        
        if (!file_exists($cache_file)) {
            return false;
        }

        $data = file_get_contents($cache_file);
        $cache_data = json_decode($data, true);

        if (!$cache_data || !isset($cache_data['expires']) || !isset($cache_data['content'])) {
            return false;
        }

        if ($cache_data['expires'] < time()) {
            $this->delete($key);
            return false;
        }

        return $cache_data['content'];
    }

    /**
     * Set cache content
     *
     * @param string $key Cache key
     * @param mixed $content Content to cache
     * @return bool Whether the content was cached successfully
     */
    public function set($key, $content) {
        $cache_file = $this->get_cache_file($key);
        $cache_data = array(
            'expires' => time() + $this->expiration,
            'content' => $content
        );

        return file_put_contents($cache_file, json_encode($cache_data)) !== false;
    }

    /**
     * Delete cached content
     *
     * @param string $key Cache key
     * @return bool Whether the cache was deleted successfully
     */
    public function delete($key) {
        $cache_file = $this->get_cache_file($key);
        
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }

        return true;
    }

    /**
     * Clear all cache
     *
     * @return bool Whether the cache was cleared successfully
     */
    public function clear_cache() {
        $files = glob(VMC_SIMPLE_CACHE_CACHE_DIR . '*');
        
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.htaccess') {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get cache file path
     *
     * @param string $key Cache key
     * @return string Cache file path
     */
    private function get_cache_file($key) {
        return VMC_SIMPLE_CACHE_CACHE_DIR . md5($key) . '.cache';
    }
} 