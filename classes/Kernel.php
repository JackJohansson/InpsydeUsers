<?php
/**
 * Kernel class used to register hooks, translations,
 * etc.
 *
 * @package Inpsyde
 */

namespace Inpsyde;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You shouldn\'t really be doing this.' );
}

/**
 * Class Kernel
 *
 * @package Inpsyde
 */
class Kernel {

	/**
	 * Initialize the requirements.
	 */
	public static function init() {

		// Register autoload.
		spl_autoload_register( array( __CLASS__, 'register_autoload' ) );

		// Register the hooks.
		self::register_hooks();

		// Register the filters.
		self::register_filters();

		// Register activation hook.
		self::register_activation();

		// Register uninstall hook.
		self::register_uninstall();
	}

	/**
	 * Callback method for registering the activation
	 * hook.
	 */
	public static function register_activation() {
		// Both perform the same action.
		register_activation_hook( PLUGIN_FILE, array( __CLASS__, 'activation_callback' ) );
		register_deactivation_hook( PLUGIN_FILE, array( __CLASS__, 'activation_callback' ) );
	}

	/**
	 * Perform uninstallation tasks.
	 */
	public static function register_uninstall() {
	}

	/**
	 * Register the default hooks.
	 */
	private static function register_hooks() {

		// We will add our rewrite rules after the plugin has been loaded,
		// so we can avoid an unnecessary flag to flush the rewrite rules.
		$activation_hook = plugin_basename( PLUGIN_FILE );

		// An array of hooks and their callbacks.
		$hooks = array(
			array(
				'name'     => "activate_{$activation_hook}",
				'callback' => array( __CLASS__, 'add_rewrite_rules' ),
				'priority' => 1,
				'args'     => 1,
			),
			array(
				'name'     => 'init',
				'callback' => array( __CLASS__, 'load_text_domain' ),
				'priority' => 10,
				'args'     => 1,
			),
			array(
				'name'     => 'rest_api_init',
				'callback' => array( __CLASS__, 'add_rest_rules' ),
				'priority' => 10,
				'args'     => 1,
			),
			array(
				'name'     => 'template_redirect',
				'callback' => array( __CLASS__, 'template_redirect' ),
				'priority' => 10,
				'args'     => 1,
			),
			array(
				'name'     => 'wp_enqueue_scripts',
				'callback' => array( __CLASS__, 'enqueue_scripts' ),
				'priority' => 999,
				'args'     => 1,
			),
		);

		foreach ( $hooks as $hook ) {
			add_action( $hook['name'], $hook['callback'], $hook['priority'], $hook['args'] );
		}
	}

	/**
	 * Callback method to register the filters.
	 */
	private static function register_filters() {
		// An array of filters and their callbacks.
		$filters = array(
			array(
				'name'     => 'query_vars',
				'callback' => array( __CLASS__, 'add_query_vars' ),
				'priority' => 10,
				'args'     => 1,
			),
			array(
				'name'     => 'template_include',
				'callback' => array( __CLASS__, 'template_include' ),
				'priority' => 10,
				'args'     => 1,
			),
		);

		foreach ( $filters as $filter ) {
			add_filter( $filter['name'], $filter['callback'], $filter['priority'], $filter['args'] );
		}
	}

	/**
	 * Callback function to be executed after the
	 * plugin is activated.
	 */
	public static function activation_callback() {
		// Perform a flush on rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Inject the custom query vars into the environment.
	 *
	 * @param array $query_vars An array of existing query vars.
	 *
	 * @return array The modified query var array.
	 */
	public static function add_query_vars( array $query_vars ): array {
		$query_vars[] = 'inpsyde-page';

		return $query_vars;
	}

	/**
	 * Register the required rest routes.
	 */
	public static function add_rest_rules() {
		// List all users.
		register_rest_route(
			apply_filters( 'inpsyde_rest_namespace', Restful::REST_NAMESPACE ),
			apply_filters( 'inpsyde_rest_users', Restful::REST_USERS_ROUTE ),
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( 'Inpsyde\Restful', 'list_users' ),
				'permission_callback' => '__return_true',
			)
		);
		// List single user.
		register_rest_route(
			apply_filters( 'inpsyde_rest_namespace', Restful::REST_NAMESPACE ),
			apply_filters( 'inpsyde_rest_user_details', Restful::REST_USER_ROUTE ),
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( 'Inpsyde\Restful', 'user_info' ),
				'permission_callback' => '__return_true',
			)
		);
		// Flush all users' data.
		register_rest_route(
			apply_filters( 'inpsyde_rest_namespace', Restful::REST_NAMESPACE ),
			apply_filters( 'inpsyde_rest_users_flush', Restful::REST_USERS_FLUSH_ROUTE ),
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( 'Inpsyde\Restful', 'users_flush' ),
				'permission_callback' => '__return_true',
			)
		);
		// Flush a user's data.
		register_rest_route(
			apply_filters( 'inpsyde_rest_namespace', Restful::REST_NAMESPACE ),
			apply_filters( 'inpsyde_rest_user_flush', Restful::REST_USER_FLUSH_ROUTE ),
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( 'Inpsyde\Restful', 'user_flush' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Callback method to add the rewrite rule.
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule(
			apply_filters( 'inpsyde_rewrite_rule', '(this-is-a-totally-fake-permalink)([/].*)?$' ),
			'index.php?inpsyde-page=yes',
			'top'
		);
	}

	/**
	 * Enqueue the registered scripts and styles.
	 */
	public static function enqueue_scripts() {

		// Only on our custom page.
		if ( ! get_query_var( 'inpsyde-page' ) ) {
			return;
		}
		// Register a list of scripts.
		$scripts = self::register_scripts();

		// Enqueue the registered scripts.
		foreach ( $scripts as $handle ) {
			wp_enqueue_script( $handle );
		}

		// Get a list of registered styles.
		$styles = self::register_styles();

		// Enqueue them.
		foreach ( $styles as $handle ) {
			wp_enqueue_style( $handle );
		}
	}

	/**
	 * Register a list of scripts to be enqueued
	 * later.
	 */
	private static function register_scripts(): array {
		$scripts = array(
			// Sweet alert.
			'inpsyde-swal'      => array(
				'src'    => PLUGIN_URL . 'assets/scripts/vendor/sweetalert2.all.min.js',
				'deps'   => array( 'jquery' ),
				'ver'    => '8.8.1',
				'footer' => true,
			),
			// Datatable.
			'inpsyde-datatable' => array(
				'src'    => PLUGIN_URL . 'assets/scripts/vendor/datatables.bundle.js',
				'deps'   => array( 'jquery', 'inpsyde-swal' ),
				'ver'    => '1.10.19',
				'footer' => true,
			),
			// BlockUI.
			'inpsyde-blockui'   => array(
				'src'    => PLUGIN_URL . 'assets/scripts/vendor/jquery.blockUI.js',
				'deps'   => array( 'jquery', 'inpsyde-datatable' ),
				'ver'    => '1.10.19',
				'footer' => true,
			),
			// Main scripts.
			'inpsyde-scripts'   => array(
				'src'    => PLUGIN_URL . 'assets/scripts/global.js',
				'deps'   => array( 'inpsyde-swal', 'inpsyde-datatable', 'inpsyde-blockui', 'jquery' ),
				'ver'    => '1.0.0',
				'footer' => true,
			),
		);

		foreach ( $scripts as $handle => $script ) {
			wp_register_script( $handle, $script['src'], $script['deps'], $script['ver'], $script['footer'] );
		}

		// Inject our custom JS data into the footer to be use in global.js file.
		$localization = array(
			'rest_url' => array(
				'users'        => get_rest_url( null, '/' . Restful::REST_NAMESPACE . '/' . Restful::REST_USERS_ROUTE ),
				'user_details' => get_rest_url( null, '/' . Restful::REST_NAMESPACE . '/' . Restful::REST_USER_ROUTE ),
				'user_flush'   => get_rest_url( null, '/' . Restful::REST_NAMESPACE . '/' . Restful::REST_USER_FLUSH_ROUTE ),
				'users_flush'  => get_rest_url( null, '/' . Restful::REST_NAMESPACE . '/' . Restful::REST_USERS_FLUSH_ROUTE ),
			),
			'i18n'     => array(
				'view'       => esc_html__( 'View', 'inpsyde-users' ),
				'error'      => esc_html__( 'Error!', 'inpsyde-users' ),
				'processing' => esc_html__( 'Processing...', 'inpsyde-users' ),
				'invalid_id' => esc_html__( 'Invalid user id. Please refresh the page and try again.', 'inpsyde-users' ),
			),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		);

		wp_localize_script(
			'inpsyde-scripts',
			'inpsyde',
			apply_filters( 'inpsyde_localization', $localization )
		);

		return array_keys( $scripts );
	}

	/**
	 * Register a list of styles to be enqueued
	 * later.
	 */
	private static function register_styles(): array {
		$styles = array(
			// Google fonts.
			'inpsyde-fonts'     => array(
				'src'  => 'https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;600&family=Poppins:wght@100;200;300;400;500;600&display=swap',
				'deps' => null,
				'ver'  => '1.0.0',
			),
			// Flaticons.
			'inpsyde-flaticon'  => array(
				'src'  => PLUGIN_URL . 'assets/fonts/flaticon/flaticon.css',
				'deps' => null,
				'ver'  => '1.0.0',
			),
			// Datatable.
			'inpsyde-datatable' => array(
				'src'  => PLUGIN_URL . 'assets/styles/vendor/datatables.bundle.min.css',
				'deps' => null,
				'ver'  => '1.10.19',
			),
			// Sweet alert.
			'inpsyde-swal'      => array(
				'src'  => PLUGIN_URL . 'Inpsyde/Kernel.php',
				'deps' => null,
				'ver'  => '8.8.1',
			),
			// Main style.
			'inpsyde-styles'    => array(
				'src'  => PLUGIN_URL . 'assets/styles/global.css',
				'deps' => array( 'inpsyde-datatable', 'inpsyde-flaticon' ),
				'ver'  => '1.0.0',
			),
		);

		foreach ( $styles as $handle => $style ) {
			wp_register_style( $handle, $style['src'], $style['deps'], $style['ver'] );
		}

		return array_keys( $styles );
	}

	/**
	 * Load the translation file.
	 */
	public static function load_text_domain() {
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
	public static function register_autoload( string $class ) {

		// Only if class belongs to the Inpsyde namespace.
		if ( 0 === strpos( $class, 'Inpsyde\\' ) ) {
			$path = substr( $class, 8 );
			$file = PLUGIN_DIR . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . str_replace( '\\', '/', $path ) . '.php';

			if ( file_exists( $file ) ) {
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
	public static function template_include( string $template ): string {
		// If this is our custom endpoint indeed.
		if ( 'yes' === get_query_var( 'inpsyde-page' ) ) {
			return ( PLUGIN_DIR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'users.php' );
		}

		// Original template.
		return $template;
	}

	/**
	 * For further extension, maybe. template_include is enough
	 * unless we want to add extra functionality.
	 */
	public static function template_redirect() {
	}
}
