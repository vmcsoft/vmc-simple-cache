<?php
namespace VMC_Simple_Cache;

class Post_Cache {
    private $cache_manager;
    private $options;

    public function __construct() {
        $this->cache_manager = new Cache_Manager();
        $this->options = get_option('vmc_simple_cache_options', array(
            'cache_home' => false,
            'cache_blog' => false,
            'debug_mode' => false,
            'cache_ttl' => 3600
        ));
        
        // Set initial TTL
        $this->cache_manager->set_expiration($this->options['cache_ttl']);
    }

    public function init() {
        // Cache content when viewing pages
        add_action('template_include', array($this, 'maybe_cache_content'));
    }

    public function maybe_cache_content($template) {
        // Don't cache for logged-in users
        if (is_user_logged_in()) {
            if ($this->options['debug_mode']) {
                echo $this->get_debug_info('Cache disabled for logged-in user');
            }
            return $template;
        }

        // Check if we should cache this page
        if (!$this->should_cache_page()) {
            if ($this->options['debug_mode']) {
                echo $this->get_debug_info('Page not configured for caching');
            }
            return $template;
        }

        // Generate cache key
        $cache_key = $this->get_cache_key();

        // Try to get cached content
        $cached_content = $this->cache_manager->get($cache_key);
        if ($cached_content !== false) {
            // If we have cached content, output it and return
            if ($this->options['debug_mode']) {
                echo $this->get_debug_info('Serving cached content', array(
                    'key' => $cache_key,
                    'expires' => $this->cache_manager->get_expiration_time($cache_key)
                ));
            }
            echo $cached_content;
            return;
        }

        // If no cache, capture the output
        ob_start();
        include $template;
        $content = ob_get_clean();

        // Cache the content
        $result = $this->cache_manager->set($cache_key, $content);

        if ($this->options['debug_mode']) {
            echo $this->get_debug_info('Caching content', array(
                'key' => $cache_key,
                'success' => $result,
                'expires' => time() + $this->cache_manager->get_expiration()
            ));
        }

        // Output the content
        echo $content;
    }

    private function should_cache_page() {
        // Check single post pages
        if (is_single()) {
            return true;
        }

        // Check front page (home page)
        if (is_front_page()) {
            return $this->options['cache_home'];
        }

        // Check blog page (posts page)
        if (is_home() && !is_front_page()) {
            return $this->options['cache_blog'];
        }

        return false;
    }

    private function get_cache_key() {
        if (is_single()) {
            $post = get_post();
            return 'post_' . $post->ID;
        }

        if (is_front_page()) {
            return 'front_page';
        }

        if (is_home() && !is_front_page()) {
            return 'blog_page';
        }

        return '';
    }

    private function get_debug_info($message, $data = array()) {
        $debug_info = array(
            'message' => $message,
            'timestamp' => current_time('mysql'),
            'page' => $this->get_current_page_info(),
            'data' => $data
        );

        return "\n<!-- VMC Simple Cache Debug Info:\n" . 
               print_r($debug_info, true) . 
               "\n-->\n";
    }

    private function get_current_page_info() {
        $info = array(
            'is_single' => is_single(),
            'is_front_page' => is_front_page(),
            'is_home' => is_home(),
            'template' => get_page_template_slug()
        );

        if (is_single()) {
            $post = get_post();
            $info['post_id'] = $post->ID;
            $info['post_type'] = $post->post_type;
        }

        return $info;
    }

    public function update_options($options) {
        $this->options = wp_parse_args($options, $this->options);
        update_option('vmc_simple_cache_options', $this->options);
        
        // Update cache manager TTL
        $this->cache_manager->set_expiration($this->options['cache_ttl']);
    }
} 