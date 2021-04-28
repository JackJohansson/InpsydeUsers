<?php

/**
 * Kernel class used to register hooks, translations,
 * etc.
 *
 * @package Inpsyde
 */

declare(strict_types=1);

namespace Inpsyde;

/**
 * Class Kernel
 *
 * @package Inpsyde
 */
class Kernel
{

    /**
     * Initialize the requirements.
     */
    public static function init()
    {

        // Register autoload.
        spl_autoload_register([ __CLASS__, 'registerAutoload' ]);

        // Register the hooks.
        self::registerHooks();

        // Register the filters.
        self::registerFilters();

        // Register activation hook.
        self::registerActivation();

        // Register uninstall hook.
        self::registerUninstall();
    }

    /**
     * Callback method for registering the activation
     * hook.
     */
    public static function registerActivation()
    {
        // Both perform the same action.
        register_activation_hook(PLUGIN_FILE, [ __CLASS__, 'activationCallback' ]);
        register_deactivation_hook(PLUGIN_FILE, [ __CLASS__, 'activationCallback' ]);
    }

    /**
     * Perform uninstallation tasks.
     */
    public static function registerUninstall()
    {
    }

    /**
     * Register the default hooks.
     */
    private static function registerHooks()
    {

        // We will add our rewrite rules after the plugin has been loaded,
        // so we can avoid an unnecessary flag to flush the rewrite rules.
        $activationHook = plugin_basename(PLUGIN_FILE);

        // An array of hooks and their callbacks.
        $hooks = [
            [
                'name' => "activate_{$activationHook}",
                'callback' => [ __CLASS__, 'addRewriteRules' ],
                'priority' => 1,
                'args' => 1,
            ],
            [
                'name' => 'init',
                'callback' => [ __CLASS__, 'loadTextDomain' ],
                'priority' => 10,
                'args' => 1,
            ],
            [
                'name' => 'rest_api_init',
                'callback' => [ __CLASS__, 'addRestRules' ],
                'priority' => 10,
                'args' => 1,
            ],
            [
                'name' => 'templateRedirect',
                'callback' => [ __CLASS__, 'templateRedirect' ],
                'priority' => 10,
                'args' => 1,
            ],
            [
                'name' => 'wp_enqueue_scripts',
                'callback' => [ __CLASS__, 'enqueueScripts' ],
                'priority' => 999,
                'args' => 1,
            ],
        ];

        foreach ($hooks as $hook) {
            add_action($hook['name'], $hook['callback'], $hook['priority'], $hook['args']);
        }
    }

    /**
     * Callback method to register the filters.
     */
    private static function registerFilters()
    {
        // An array of filters and their callbacks.
        $filters = [
            [
                'name' => 'query_vars',
                'callback' => [ __CLASS__, 'addQueryVars' ],
                'priority' => 10,
                'args' => 1,
            ],
            [
                'name' => 'template_include',
                'callback' => [ __CLASS__, 'templateInclude' ],
                'priority' => 10,
                'args' => 1,
            ],
        ];

        foreach ($filters as $filter) {
            add_filter($filter['name'], $filter['callback'], $filter['priority'], $filter['args']);
        }
    }

    /**
     * Callback function to be executed after the
     * plugin is activated.
     */
    public static function activationCallback()
    {
        // Perform a flush on rewrite rules.
        flush_rewrite_rules();
    }

    /**
     * Inject the custom query vars into the environment.
     *
     * @param array $queryVars
     *
     * @return array The modified query var array.
     */
    public static function addQueryVars(array $queryVars): array
    {

        $queryVars[] = 'inpsyde-page';

        return $queryVars;
    }

    /**
     * Register the required rest routes.
     */
    public static function addRestRules()
    {
        // List all users.
        register_rest_route(
            apply_filters('inpsyde_rest_namespace', Restful::REST_NAMESPACE),
            apply_filters('inpsyde_rest_users', Restful::REST_USERS_ROUTE),
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [ 'Inpsyde\Restful', 'listUsers' ],
                'permission_callback' => '__return_true',
            ]
        );
        // List single user.
        register_rest_route(
            apply_filters('inpsyde_rest_namespace', Restful::REST_NAMESPACE),
            apply_filters('inpsyde_rest_user_details', Restful::REST_USER_ROUTE),
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [ 'Inpsyde\Restful', 'userInfo' ],
                'permission_callback' => '__return_true',
            ]
        );
        // Flush all users' data.
        register_rest_route(
            apply_filters('inpsyde_rest_namespace', Restful::REST_NAMESPACE),
            apply_filters('inpsyde_rest_users_flush', Restful::REST_USERS_FLUSH_ROUTE),
            [
                'methods' => \WP_REST_Server::DELETABLE,
                'callback' => [ 'Inpsyde\Restful', 'usersFlush' ],
                'permission_callback' => '__return_true',
            ]
        );
        // Flush a user's data.
        register_rest_route(
            apply_filters('inpsyde_rest_namespace', Restful::REST_NAMESPACE),
            apply_filters('inpsyde_rest_user_flush', Restful::REST_USER_FLUSH_ROUTE),
            [
                'methods' => \WP_REST_Server::DELETABLE,
                'callback' => [ 'Inpsyde\Restful', 'userFlush' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Callback method to add the rewrite rule.
     */
    public static function addRewriteRules()
    {

        add_rewrite_rule(
            apply_filters('inpsyde_rewrite_rule', '(this-is-a-totally-fake-permalink)([/].*)?$'),
            'index.php?inpsyde-page=yes',
            'top'
        );
    }

    /**
     * Enqueue the registered scripts and styles.
     */
    public static function enqueueScripts()
    {

        // Only on our custom page.
        if (! get_query_var('inpsyde-page')) {
            return;
        }
        // Register a list of scripts.
        $scripts = self::registerScripts();

        // Enqueue the registered scripts.
        foreach ($scripts as $handle) {
            wp_enqueue_script($handle);
        }

        // Get a list of registered styles.
        $styles = self::registerStyles();

        // Enqueue them.
        foreach ($styles as $handle) {
            wp_enqueue_style($handle);
        }
    }

    /**
     * Register a list of scripts to be enqueued
     * later.
     */
    private static function registerScripts(): array
    {

        $scripts = [
            // Sweet alert.
            'inpsyde-swal' => [
                'src' => PLUGIN_URL . 'assets/scripts/vendor/sweetalert2.all.min.js',
                'deps' => [ 'jquery' ],
                'ver' => '8.8.1',
                'footer' => true,
            ],
            // Datatable.
            'inpsyde-datatable' => [
                'src' => PLUGIN_URL . 'assets/scripts/vendor/datatables.bundle.js',
                'deps' => [ 'jquery', 'inpsyde-swal' ],
                'ver' => '1.10.19',
                'footer' => true,
            ],
            // BlockUI.
            'inpsyde-blockui' => [
                'src' => PLUGIN_URL . 'assets/scripts/vendor/jquery.blockUI.js',
                'deps' => [ 'jquery', 'inpsyde-datatable' ],
                'ver' => '1.10.19',
                'footer' => true,
            ],
            // Main scripts.
            'inpsyde-scripts' => [
                'src' => PLUGIN_URL . 'assets/scripts/global.js',
                'deps' => [ 'inpsyde-swal', 'inpsyde-datatable', 'inpsyde-blockui', 'jquery' ],
                'ver' => '1.0.0',
                'footer' => true,
            ],
        ];

        foreach ($scripts as $handle => $scr) {
            wp_register_script($handle, $scr['src'], $scr['deps'], $scr['ver'], $scr['footer']);
        }

        // Inject our custom JS data into the footer to be use in global.js file.
        $localization = [
            'rest_url' => [
                'base' => get_rest_url(),
                'users' => Restful::REST_NAMESPACE . '/' . Restful::REST_USERS_ROUTE,
                'user_details' => Restful::REST_NAMESPACE . '/' . Restful::REST_USER_ROUTE,
                'user_flush' => Restful::REST_NAMESPACE . '/' . Restful::REST_USER_FLUSH_ROUTE,
                'users_flush' => Restful::REST_NAMESPACE . '/' . Restful::REST_USERS_FLUSH_ROUTE,
            ],
            'i18n' => [
                'view' => esc_html__('View', 'inpsyde-users'),
                'error' => esc_html__('Error!', 'inpsyde-users'),
                'processing' => esc_html__('Processing...', 'inpsyde-users'),
                'inv_id' => esc_html__('Invalid user ID.Please refresh the page.', 'inpsyde-users'),
            ],
            'nonce' => wp_create_nonce('wp_rest'),
        ];

        // Apply user filters.
        $localization = apply_filters('inpsyde_localization', $localization);

        wp_localize_script('inpsyde-scripts', 'inpsyde', $localization);
        // Return the script handles.
        return array_keys($scripts);
    }

    /**
     * Register a list of styles to be enqueued
     * later.
     */
    private static function registerStyles(): array
    {

        $styles = [
            // Google fonts.
            'inpsyde-fonts' => [
                'src' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;600&family=Poppins:wght@100;200;300;400;500;600&display=swap',
                'deps' => null,
                'ver' => '1.0.0',
            ],
            // Flaticons.
            'inpsyde-flaticon' => [
                'src' => PLUGIN_URL . 'assets/fonts/flaticon/flaticon.css',
                'deps' => null,
                'ver' => '1.0.0',
            ],
            // Datatable.
            'inpsyde-datatable' => [
                'src' => PLUGIN_URL . 'assets/styles/vendor/datatables.bundle.min.css',
                'deps' => null,
                'ver' => '1.10.19',
            ],
            // Sweet alert.
            'inpsyde-swal' => [
                'src' => PLUGIN_URL . 'assets/styles/vendor/sweetalert2.min.css',
                'deps' => null,
                'ver' => '8.8.1',
            ],
            // Main style.
            'inpsyde-styles' => [
                'src' => PLUGIN_URL . 'assets/styles/global.css',
                'deps' => [ 'inpsyde-datatable', 'inpsyde-flaticon' ],
                'ver' => '1.0.0',
            ],
        ];

        foreach ($styles as $handle => $style) {
            wp_register_style($handle, $style['src'], $style['deps'], $style['ver']);
        }

        return array_keys($styles);
    }

    /**
     * Load the translation file.
     */
    public static function loadTextDomain()
    {

        load_plugin_textdomain(
            'inpsyde-users',
            false,
            PLUGIN_DIR . DIRECTORY_SEPARATOR . 'languages'
        );
    }

    /**
     * Autoloader callback, in case composer is not used.
     *
     * @param string $class The name of the class to load.
     */
    public static function registerAutoload(string $class)
    {

        // Only if class belongs to the Inpsyde namespace.
        if (0 === strpos($class, 'Inpsyde\\')) {
            $name = str_replace('\\', '/', substr($class, 8)) . '.php';
            $file = PLUGIN_DIR . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $name ;

            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    /**
     * Inject our custom template for the custom endpoint.
     *
     * @param string $template The current template.
     *
     * @return string The new template to load.
     */
    public static function templateInclude(string $template): string
    {
        // If this is our custom endpoint indeed.
        if ('yes' === get_query_var('inpsyde-page')) {
            return ( PLUGIN_DIR . '/assets/templates/users.php' );
        }

        // Original template.
        return $template;
    }

    /**
     * For further extension, maybe. templateInclude is enough
     * unless we want to add extra functionality.
     */
    public static function templateRedirect()
    {
    }
}
