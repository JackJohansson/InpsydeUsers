<?php
/**
 * Plugin Name:       InpsydeUsers
 * Plugin URI:        https://github.com/JackJohansson/Inpsyde-Project
 * Description:       A sample project assigned by Inpsyde gmbh
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Jack Johansson
 * Author URI:        https://jackjohansson.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       inpsyde-users
 * Domain Path:       /languages/
 *
 * @package Inpsyde
 */

// Plugin path and file.
define( 'Inpsyde\PLUGIN_DIR', __DIR__ );
define( 'Inpsyde\PLUGIN_FILE', __FILE__ );
define( 'Inpsyde\PLUGIN_URL', plugin_dir_url( Inpsyde\PLUGIN_FILE ) );
define( 'Inpsyde\API_ROOT', 'https://jsonplaceholder.typicode.com/' );

// Require the main class.
if ( file_exists( \Inpsyde\PLUGIN_DIR . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Kernel.php' ) ) {
	// Load the kernel.
	require_once \Inpsyde\PLUGIN_DIR . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Kernel.php';
	// Init the plugin.
	Inpsyde\Kernel::init();
}
