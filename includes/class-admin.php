<?php
namespace VMC_Simple_Cache;

class Admin {
    private $cache_manager;
    private $post_cache;
    private $options;

    public function __construct() {
        $this->cache_manager = new Cache_Manager();
        $this->post_cache = new Post_Cache();
        $this->options = get_option('vmc_simple_cache_options', array(
            'cache_home' => false,
            'cache_blog' => false,
            'cache_archive' => false,
            'cache_page' => false,
            'debug_mode' => false,
            'cache_ttl' => 3600
        ));
    }

    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add admin bar menu
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 100);
        
        // Handle cache clearing
        add_action('admin_post_vmc_clear_cache', array($this, 'handle_clear_cache'));
        
        // Handle settings save
        add_action('admin_post_vmc_save_settings', array($this, 'handle_save_settings'));
    }

    public function add_admin_menu() {
        add_options_page(
            'VMC Simple Cache',
            'VMC Simple Cache',
            'manage_options',
            'vmc-simple-cache',
            array($this, 'render_admin_page')
        );
    }

    public function add_admin_bar_menu($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $wp_admin_bar->add_node(array(
            'id'    => 'vmc-clear-cache',
            'title' => 'Clear Cache',
            'href'  => wp_nonce_url(admin_url('admin-post.php?action=vmc_clear_cache'), 'vmc_clear_cache'),
            'meta'  => array(
                'title' => 'Clear VMC Simple Cache',
                'class' => 'vmc-clear-cache'
            )
        ));
    }

    public function handle_clear_cache() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('vmc_clear_cache');

        $this->cache_manager->clear_cache();

        wp_redirect(add_query_arg('cache-cleared', '1', wp_get_referer()));
        exit;
    }

    public function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('vmc_save_settings');

        // Validate and sanitize TTL
        $ttl = isset($_POST['cache_ttl']) ? absint($_POST['cache_ttl']) : 3600;
        $ttl = max(60, min(86400, $ttl)); // Ensure TTL is between 60 seconds and 1 day

        $options = array(
            'cache_home' => isset($_POST['cache_home']),
            'cache_blog' => isset($_POST['cache_blog']),
            'cache_archive' => isset($_POST['cache_archive']),
            'cache_page' => isset($_POST['cache_page']),
            'debug_mode' => isset($_POST['debug_mode']),
            'cache_ttl' => $ttl
        );

        $this->post_cache->update_options($options);
        $this->options = $options;
        $this->cache_manager->set_expiration($ttl);

        wp_redirect(add_query_arg('settings-saved', '1', wp_get_referer()));
        exit;
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>VMC Simple Cache Settings</h1>
            
            <?php if (isset($_GET['cache-cleared'])): ?>
                <div class="notice notice-success">
                    <p>Cache cleared successfully!</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['settings-saved'])): ?>
                <div class="notice notice-success">
                    <p>Settings saved successfully!</p>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>Cache Status</h2>
                <p>Cache is <?php echo is_user_logged_in() ? 'disabled' : 'enabled'; ?> for logged-in users.</p>
                <p>Cache files are stored in: <code><?php echo esc_html(VMC_SIMPLE_CACHE_CACHE_DIR); ?></code></p>
                
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('vmc_save_settings'); ?>
                    <input type="hidden" name="action" value="vmc_save_settings">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Front Page Caching</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cache_home" value="1" <?php checked($this->options['cache_home']); ?>>
                                    Enable caching for the front page
                                </label>
                                <p class="description">When enabled, the front page will be cached for non-logged-in users.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Blog Page Caching</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cache_blog" value="1" <?php checked($this->options['cache_blog']); ?>>
                                    Enable caching for the blog page
                                </label>
                                <p class="description">When enabled, the blog page (posts page) will be cached for non-logged-in users.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Archive Page Caching</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cache_archive" value="1" <?php checked($this->options['cache_archive']); ?>>
                                    Enable caching for archive pages
                                </label>
                                <p class="description">When enabled, category, tag, author, date, and custom taxonomy archives will be cached for non-logged-in users.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Page Caching</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cache_page" value="1" <?php checked($this->options['cache_page']); ?>>
                                    Enable caching for regular pages
                                </label>
                                <p class="description">When enabled, all WordPress pages (except the front page) will be cached for non-logged-in users.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Cache TTL</th>
                            <td>
                                <input type="number" name="cache_ttl" value="<?php echo esc_attr($this->options['cache_ttl']); ?>" min="60" max="86400" step="60" class="small-text">
                                <p class="description">Time in seconds before cached content expires (minimum: 60, maximum: 86400).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Debug Mode</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="debug_mode" value="1" <?php checked($this->options['debug_mode']); ?>>
                                    Enable debug mode
                                </label>
                                <p class="description">When enabled, adds hidden HTML comments with cache information to help with debugging.</p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Save Settings">
                    </p>
                </form>

                <hr>

                <h3>Clear Cache</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('vmc_clear_cache'); ?>
                    <input type="hidden" name="action" value="vmc_clear_cache">
                    <p>
                        <input type="submit" class="button button-secondary" value="Clear Cache">
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
} 