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
            'cache_archive' => false,
            'cache_page' => false,
            'debug_mode' => false,
            'cache_ttl' => 3600
        ));
        
        // Set initial TTL
        $this->cache_manager->set_expiration($this->options['cache_ttl']);
    }

    public function init() {
        // Add output buffering for caching
        add_action('template_redirect', array($this, 'start_cache'), 0);
        add_action('shutdown', array($this, 'end_cache'), 0);

        // Clear cache on post update
        add_action('save_post', array($this, 'clear_post_cache'), 10, 3);
        add_action('comment_post', array($this, 'clear_post_cache_by_id'), 10, 3);
        add_action('trash_post', array($this, 'clear_post_cache_by_id'), 10, 1);
    }

    public function start_cache() {
        // Don't cache for logged-in users
        if (is_user_logged_in()) {
            if ($this->options['debug_mode']) {
                echo $this->get_debug_info('Cache disabled for logged-in users');
            }
            return;
        }

        // Check if we should cache this page
        if (!$this->should_cache()) {
            if ($this->options['debug_mode']) {
                echo $this->get_debug_info('Page not configured for caching');
            }
            return;
        }

        // Start output buffering
        ob_start();
    }

    public function end_cache() {
        // If we didn't start caching, return
        if (!ob_get_level()) {
            return;
        }

        // Get the buffered content
        $content = ob_get_clean();

        // Don't cache empty content
        if (empty($content)) {
            return;
        }

        // Get cache key
        $key = $this->get_cache_key();

        // Cache the content
        $this->cache_manager->set($key, $content);

        // Output the content
        echo $content;

        // Add debug info if enabled
        if ($this->options['debug_mode']) {
            echo $this->get_debug_info('Cache Miss', $key);
        }
    }

    private function should_cache() {
        // Check if we're on a page type that should be cached
        if (is_front_page() && $this->options['cache_home']) {
            return true;
        }

        if (is_home() && $this->options['cache_blog']) {
            return true;
        }

        if (is_single() && !is_front_page() && !is_home()) {
            return true;
        }

        // Check for archive pages
        if ($this->options['cache_archive'] && $this->is_archive_page()) {
            return true;
        }

        // Check for regular pages
        if ($this->options['cache_page'] && is_page() && !is_front_page()) {
            return true;
        }

        return false;
    }

    private function is_archive_page() {
        return (
            is_archive() || 
            is_category() || 
            is_tag() || 
            is_author() || 
            is_date() || 
            is_tax() || 
            is_post_type_archive()
        );
    }

    private function get_cache_key() {
        global $wp_query;

        // Base key
        $key = 'vmc_cache_';

        // Add specific key based on page type
        if (is_front_page()) {
            $key .= 'home';
        } elseif (is_home()) {
            $key .= 'blog';
        } elseif (is_single()) {
            $key .= 'post_' . get_the_ID();
        } elseif ($this->is_archive_page()) {
            if (is_category()) {
                $key .= 'category_' . get_queried_object_id();
            } elseif (is_tag()) {
                $key .= 'tag_' . get_queried_object_id();
            } elseif (is_author()) {
                $key .= 'author_' . get_queried_object_id();
            } elseif (is_date()) {
                $key .= 'date_' . get_query_var('year');
                if (get_query_var('monthnum')) {
                    $key .= '_' . get_query_var('monthnum');
                }
                if (get_query_var('day')) {
                    $key .= '_' . get_query_var('day');
                }
            } elseif (is_tax()) {
                $term = get_queried_object();
                $key .= 'tax_' . $term->taxonomy . '_' . $term->term_id;
            } elseif (is_post_type_archive()) {
                $key .= 'archive_' . get_post_type();
            } else {
                $key .= 'archive';
            }
        } elseif (is_page()) {
            $key .= 'page_' . get_the_ID();
        }

        // Add pagination if needed
        if (get_query_var('paged') > 1) {
            $key .= '_page_' . get_query_var('paged');
        }

        return $key;
    }

    public function clear_post_cache($post_id, $post = null, $update = true) {
        if (!$update) {
            return;
        }

        // Clear the specific post cache
        $this->cache_manager->delete('vmc_cache_post_' . $post_id);

        // Clear related caches
        $this->clear_related_caches($post_id);
    }

    public function clear_post_cache_by_id($post_id) {
        $this->clear_post_cache($post_id);
    }

    private function clear_related_caches($post_id) {
        // Get post type
        $post_type = get_post_type($post_id);

        // Clear front page cache if this is a post
        if ($post_type === 'post' && $this->options['cache_home']) {
            $this->cache_manager->delete('vmc_cache_home');
        }

        // Clear blog page cache if this is a post
        if ($post_type === 'post' && $this->options['cache_blog']) {
            $this->cache_manager->delete('vmc_cache_blog');
        }

        // Clear archive caches if enabled
        if ($this->options['cache_archive']) {
            // Clear category archives
            $categories = wp_get_post_categories($post_id);
            foreach ($categories as $category_id) {
                $this->cache_manager->delete('vmc_cache_category_' . $category_id);
            }

            // Clear tag archives
            $tags = wp_get_post_tags($post_id);
            foreach ($tags as $tag_id) {
                $this->cache_manager->delete('vmc_cache_tag_' . $tag_id);
            }

            // Clear author archive
            $author_id = get_post_field('post_author', $post_id);
            $this->cache_manager->delete('vmc_cache_author_' . $author_id);

            // Clear date archives
            $post_date = get_post_field('post_date', $post_id);
            $year = date('Y', strtotime($post_date));
            $month = date('m', strtotime($post_date));
            $day = date('d', strtotime($post_date));

            $this->cache_manager->delete('vmc_cache_date_' . $year);
            $this->cache_manager->delete('vmc_cache_date_' . $year . '_' . $month);
            $this->cache_manager->delete('vmc_cache_date_' . $year . '_' . $month . '_' . $day);

            // Clear taxonomy archives
            $taxonomies = get_object_taxonomies($post_type, 'objects');
            foreach ($taxonomies as $taxonomy) {
                $terms = wp_get_post_terms($post_id, $taxonomy->name);
                foreach ($terms as $term) {
                    $this->cache_manager->delete('vmc_cache_tax_' . $taxonomy->name . '_' . $term->term_id);
                }
            }

            // Clear post type archive
            $this->cache_manager->delete('vmc_cache_archive_' . $post_type);
        }
    }

    public function update_options($options) {
        $this->options = wp_parse_args($options, $this->options);
        update_option('vmc_simple_cache_options', $this->options);
        
        // Update cache manager TTL
        $this->cache_manager->set_expiration($this->options['cache_ttl']);
    }

    private function get_debug_info($status, $key = '') {
        $info = array(
            'Status' => $status,
            'Page' => $this->get_current_page_info(),
            'User' => is_user_logged_in() ? 'Logged In' : 'Not Logged In'
        );

        if (!empty($key)) {
            $info['Key'] = $key;
            $info['Expires'] = date('Y-m-d H:i:s', time() + $this->options['cache_ttl']);
        }

        return "\n<!-- VMC Simple Cache Debug Info:\n" . $this->format_debug_info($info) . "\n-->\n";
    }

    private function format_debug_info($info) {
        $output = '';
        foreach ($info as $key => $value) {
            $output .= $key . ': ' . $value . "\n";
        }
        return $output;
    }

    private function get_current_page_info() {
        if (is_front_page()) {
            return 'Front Page';
        } elseif (is_home()) {
            return 'Blog Page';
        } elseif (is_single()) {
            return 'Single Post: ' . get_the_title();
        } elseif (is_archive()) {
            if (is_category()) {
                return 'Category Archive: ' . single_cat_title('', false);
            } elseif (is_tag()) {
                return 'Tag Archive: ' . single_tag_title('', false);
            } elseif (is_author()) {
                return 'Author Archive: ' . get_the_author();
            } elseif (is_date()) {
                return 'Date Archive: ' . get_the_date();
            } elseif (is_tax()) {
                $term = get_queried_object();
                return 'Taxonomy Archive: ' . $term->name . ' (' . $term->taxonomy . ')';
            } elseif (is_post_type_archive()) {
                return 'Post Type Archive: ' . get_post_type_object(get_post_type())->labels->name;
            } else {
                return 'Archive Page';
            }
        } elseif (is_page()) {
            return 'Page: ' . get_the_title();
        } else {
            return 'Unknown Page Type';
        }
    }
} 