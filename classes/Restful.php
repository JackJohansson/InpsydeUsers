<?php
/**
 * Class used to handle the rest requests.
 *
 * @package Inpsyde
 */

namespace Inpsyde;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You shouldn\'t really be doing this.' );
}

/**
 * Class Restful
 *
 * @package Inpsyde
 */
class Restful {

	/**
	 * Rest namespace.
	 */
	public const REST_NAMESPACE = 'inpsyde';

	/**
	 * Rest path to fetch the users data.
	 */
	public const REST_USERS_ROUTE = 'users';

	/**
	 * Rest path to fetch a single user's details.
	 */
	public const REST_USER_ROUTE = 'user-info';
	/**
	 * Rest route to flush a user's data.
	 */
	public const REST_USER_FLUSH_ROUTE = 'user-flush';
	/**
	 * Rest route to flush all the users' data.
	 */
	public const REST_USERS_FLUSH_ROUTE = 'users-flush';

	/**
	 * Handle the request to list all the users.
	 *
	 * @param \WP_REST_Request $request The request object passed to the callback function.
	 *
	 * @return array An array of data sent back to the user.
	 */
	public static function list_users( \WP_REST_Request $request ): array {
		$users = new Users();

		// List all the users.
		$user_list = $users->list_users();

		// If there's an error.
		if ( is_wp_error( $user_list ) ) {
			return array(
				'success' => false,
				'message' => $user_list->get_error_message(),
			);
		}

		return $user_list;
	}

	/**
	 * Flush a single user's data.
	 *
	 * @param \WP_REST_Request $request The request object passed to the callback function.
	 *
	 * @return array An array of data sent back to the user.
	 */
	public function user_flush( \WP_REST_Request $request ): array {
		$users = new Users();

		$user_id = $request->get_param( 'user_id' );

		if ( null === $user_id ) {
			return array(
				'success' => false,
				'message' => esc_html__( 'The required parameter ID is missing from the request. Please try again later.', 'inpsyde-users' ),
			);
		}

		$flush_result = $users->flush_user( $user_id );

		// If there's an error.
		if ( is_wp_error( $flush_result ) ) {
			return array(
				'success' => false,
				'message' => $flush_result->get_error_message(),
			);
		}

		return array( 'success' => true );
	}

	/**
	 * Handle the request to list the details
	 * for a specific user.
	 *
	 * @param \WP_REST_Request $request The request object passed to the callback function.
	 *
	 * @return array An array of data sent back to the user.
	 */
	public static function user_info( \WP_REST_Request $request ): array {
		$users = new Users();

		// Get user's ID.
		$user_id = $request->get_param( 'id' );

		// If the id is missing.
		if ( null === $user_id ) {
			return array(
				'success' => false,
				'message' => esc_html__( 'The required parameter ID is missing from the request. Please try again later.', 'inpsyde-users' ),
			);
		}

		// Fetch the details.
		$user_info = $users->user_info( $user_id );

		// If there's an error.
		if ( is_wp_error( $user_info ) ) {
			return array(
				'success' => false,
				'message' => $user_info->get_error_message(),
			);
		}

		// Return the results.
		return $user_info;
	}

	/**
	 * Flush all the users' details.
	 *
	 * @param \WP_REST_Request $request The request object passed to the callback function.
	 *
	 * @return array An array of data sent back to the user.
	 */
	public function users_flush( \WP_REST_Request $request ): array {
		$users = new Users();

		$flush_result = $users->flush_all();

		// If there's an error.
		if ( is_wp_error( $flush_result ) ) {
			return array(
				'success' => false,
				'message' => $flush_result->get_error_message(),
			);
		}

		return array( 'success' => true );
	}
}
