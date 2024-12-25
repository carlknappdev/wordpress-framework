<?php

namespace App;

class PreventUserEnum
{
    private function __construct() {}

    public static function init()
    {
        add_filter('login_errors', [self::class, 'modify_login_errors']);
        add_action('init', [self::class, 'prevent_author_requests']);
        add_action('rest_authentication_errors', [self::class, 'only_allow_logged_in_rest_access_to_users']);
        add_filter('wp_sitemaps_add_provider', [self::class, 'remove_authors_from_sitemap'], 10, 2);
        add_filter('oembed_response_data', [self::class, 'remove_author_from_oembed']);
        add_action('template_redirect', [self::class, 'redirect_author_archives']);
        add_filter('the_author_posts_link', [self::class, 'modify_the_author_posts_link']);
    }

    public static function modify_login_errors()
    {
        return 'An error occurred. Try again or if you are a bot, please don\'t.';
    }

    public static function prevent_author_requests()
    {
        if (
            isset($_REQUEST['author'])
            && self::string_contains_numbers($_REQUEST['author'])
            && ! is_user_logged_in()
        ) {
            wp_die('forbidden - number in author name not allowed = ' . esc_html($_REQUEST['author']));
        }
    }

    public static function only_allow_logged_in_rest_access_to_users($access)
    {
        if (is_user_logged_in()) {
            return $access;
        }

        if ((preg_match('/users/i', $_SERVER['REQUEST_URI']) !== 0)
            || (isset($_REQUEST['rest_route']) && (preg_match('/users/i', $_REQUEST['rest_route']) !== 0))
        ) {
            return new \WP_Error(
                'rest_cannot_access',
                'Only authenticated users can access the User endpoint REST API.',
                [
                    'status' => rest_authorization_required_code()
                ]
            );
        }

        return $access;
    }

    private static function string_contains_numbers($string): bool
    {
        return preg_match('/\\d/', $string) > 0;
    }

    public static function remove_authors_from_sitemap($provider, $name)
    {
        if ('users' === $name) {
            return false;
        }

        return $provider;
    }

    public static function remove_author_from_oembed($data)
    {
        unset($data['author_url']);
        unset($data['author_name']);

        return $data;
    }

    public static function redirect_author_archives()
    {
        if (is_author() || isset($_GET['author'])) {
            wp_safe_redirect(esc_url(home_url('/')), 301);
        }
    }

    public static function modify_the_author_posts_link($link)
    {
        if (! is_admin()) {
            return get_the_author();
        }
        return $link;
    }
}
