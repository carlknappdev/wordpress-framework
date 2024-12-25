<?php

namespace App;

use Timber\Site;

class Init extends Site
{
    public array $assets_map;

    public function __construct()
    {
        $this->assets_map = $this->get_assets_map();

        add_action('after_setup_theme', array($this, 'theme_supports'));
        add_filter('init', [$this, 'register_post_types']);
        add_filter('init', [$this, 'register_taxonomies']);
        add_filter('init', [$this, 'register_nav_menus']);

        // Disable unused Wordpress features.
        add_filter('admin_init', [$this, 'remove_admin_comments']);
        add_filter('admin_menu', function () {
            remove_menu_page('edit-comments.php');
        });
        add_filter('comments_array', '__return_empty_array', 10, 2);
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        add_filter('show_admin_bar', '__return_false');
        add_filter('the_generator', '__return_empty_string');
        add_filter('xmlrpc_enabled', '__return_false');

        // Remove standard WP header links.
        remove_filter('wp_head', 'rsd_link');
        remove_filter('wp_head', 'wlwmanifest_link');
        remove_filter('wp_head', 'wp_generator');
        add_filter('wpseo_hide_version', '__return_true');

        // Set gallery defaults.
        add_filter('media_view_settings', [$this, 'gallery_defaults']);

        // Frontend scripts.
        add_filter('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_filter('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Admin styles.
        add_action('login_enqueue_scripts', [$this, 'enqueue_login_styles']);
        add_filter('login_headerurl', [$this, 'custom_login_logo_url']);

        // Post excerpt settings.
        add_filter('excerpt_length', [$this, 'excerptLength'], 999);
        add_filter('excerpt_more', [$this, 'excerptMore'], 999);

        // Check for staging environment.
        add_action('login_body_class', [$this, 'add_environment_class']);
        add_filter('body_class', [$this, 'add_environment_class']);

        // Add Timber templates path.
        add_filter('timber/locations', function ($paths) {
            $paths[] = [ROOTPATH . '/templates'];

            return $paths;
        });

        // Does what it says on the tin.
        PreventUserEnum::init();

        parent::__construct();
    }

    private function get_assets_map()
    {
        $assets_map_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';

        if (file_exists($assets_map_path)) {
            return json_decode(file_get_contents($assets_map_path), true);
        }

        return [];
    }

    public function theme_supports()
    {
        add_theme_support('html5', ['gallery', 'caption']);
        add_theme_support('post-thumbnails');
    }

    public function register_post_types() {}

    public function register_taxonomies() {}

    public function register_nav_menus()
    {
        register_nav_menu('main-menu', 'Primary Menu');
        register_nav_menu('category-menu', 'Category Menu');
        register_nav_menu('footer-menu', 'Footer Menu');
    }

    public function remove_admin_comments()
    {
        // Redirect any user trying to access comments page.
        global $pagenow;

        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }

        // Remove comments metabox from dashboard.
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        // Remove comments links from admin bar.
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }

        // Prevent subscribers viewing dashboard profile.
        if (is_admin() && !defined('DOING_AJAX') && (current_user_can('subscriber'))) {
            wp_redirect(home_url());
            exit;
        }

        // Disable support for comments and trackbacks in post types.
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    public function gallery_defaults($settings)
    {
        $settings['galleryDefaults']['columns'] = 2;
        $settings['galleryDefaults']['link'] = 'none';
        $settings['galleryDefaults']['size'] = 'full';

        return $settings;
    }

    public function enqueue_scripts()
    {
        // wp_enqueue_script('jquery');

        if (!$this->isViteHMRAvailable()) {
            if (array_key_exists('assets/index.js', $this->assets_map)) {
                wp_enqueue_script(
                    'custom-script',
                    get_stylesheet_directory_uri() . '/dist/' . $this->assets_map['assets/index.js']["file"],
                    [],
                    null,
                    []
                );
                $this->loadJSScriptAsESModule('custom-script');
            }
        } else {
            $theme_path = parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH);

            wp_enqueue_script(
                'vite-client',
                $this->getViteDevServerAddress() . $theme_path . '/dist/@vite/client',
                [],
                null,
                []
            );
            $this->loadJSScriptAsESModule('vite-client');

            wp_enqueue_script(
                'vite-script',
                $this->getViteDevServerAddress() . $theme_path . '/dist/assets/index.js',
                [],
                null,
                []
            );
            $this->loadJSScriptAsESModule('vite-script');
        }
    }

    public function enqueue_styles()
    {
        if (
            !$this->isViteHMRAvailable() &&
            array_key_exists('assets/index.js', $this->assets_map) &&
            array_key_exists('css', $this->assets_map['assets/index.js'])
        ) {
            foreach ($this->assets_map['assets/index.js']["css"] as $style_path) {
                wp_enqueue_style(
                    'core',
                    get_stylesheet_directory_uri() . '/dist/' . $style_path,
                    [],
                    false,
                    'all'
                );
            }
        }
    }

    public function add_environment_class($classes = '')
    {
        $environment = wp_get_environment_type();

        if ($environment !== 'production') {
            $classes[] = 'env-' . $environment;
        }

        return $classes;
    }

    public function enqueue_login_styles()
    {
        if (
            array_key_exists('assets/login.js', $this->assets_map) &&
            array_key_exists('css', $this->assets_map['assets/login.js'])
        ) {
            foreach ($this->assets_map['assets/login.js']["css"] as $style_path) {
                wp_enqueue_style(
                    'admin-styles',
                    get_stylesheet_directory_uri() . '/dist/' . $style_path,
                    [],
                    false,
                    'all'
                );
            }
        }
    }

    public function custom_login_logo_url()
    {
        return site_url();
    }

    public function excerptLength()
    {
        return 20;
    }

    public function excerptMore()
    {
        return '&hellip;';
    }

    public function loadJSScriptAsESModule($script_handle)
    {
        add_filter(
            'script_loader_tag',
            function ($tag, $handle, $src) use ($script_handle) {
                if ($script_handle === $handle) {
                    return sprintf(
                        '<script type="module" src="%s"></script>',
                        esc_url($src)
                    );
                }
                return $tag;
            },
            10,
            3
        );
    }

    public function getViteDevServerAddress()
    {
        if (defined('VITE_DEV_SERVER_URL')) {
            return \VITE_DEV_SERVER_URL;
        }

        return '';
    }

    public function isViteHMRAvailable()
    {
        return !empty($this->getViteDevServerAddress()) &&
            defined('WP_ENVIRONMENT_TYPE') &&
            \WP_ENVIRONMENT_TYPE === 'local';
    }
}
