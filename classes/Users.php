<?php
	/**
	 * Class used to handle the user's data.
	 *
	 * @package Inpsyde
	 */

	namespace Inpsyde;

	// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You shouldn\'t really be doing this.' );
}

	/**
	 * Class Users
	 *
	 * @package Inpsyde
	 */
class Users {

	/**
	 * Flush all the users from the cache.
	 */
	public function flush_all() {
		$flush_users   = delete_transient( 'inpsyde_users' );
		$flush_details = delete_transient( 'inpsyde_user_details' );

		if ( ! $flush_users ) {
			return new \WP_Error( 'inpsyde_flush_failed', esc_html__( 'Could not flush the user cache. This is mostly because the results were already flushed.', 'inpsyde-users' ) );
		}

		return true;
	}

	/**
	 * Remove a single user from the cache.
	 *
	 * @param int $user_id The id of user which we want its data flushed.
	 *
	 * @return bool|\WP_Error The result of flushing, or an error object
	 */
	public function flush_user( int $user_id ) {
		$cache = get_transient( 'inpsyde_user_details' );

		if ( false === $cache || ! isset( $cache[ $user_id ] ) ) {
			return new \WP_Error( 'inpsyde_flush_failed', esc_html__( 'This user ID is not cached yet.', 'inpsyde-users' ) );
		}

		// Remove the current user.
		unset( $cache[ $user_id ] );

		// Save.
		$save_result = set_transient( 'inpsyde_user_details', $cache, absint( apply_filters( 'inpsyde_transient', 3600 ) ) );

		// If saving has failed.
		if ( ! $save_result ) {
			return new \WP_Error( 'inpsyde_flush_failed', esc_html__( 'Could not save the cache into database. Please try again later.', 'inpsyde-users' ) );
		}

		return true;
	}

	/**
	 * List all the users and their information.
	 *
	 * @return \WP_Error|array An instance of WP_Error on failure, or an array of user data.
	 */
	public function list_users() {

		// Check the cache first.
		$cached_users = get_transient( 'inpsyde_users' );

		// This is already properly formatted.
		if ( false !== $cached_users ) {
			return $cached_users;
		}

		// Make a new request to the API.
		$request_decoded = $this->get_results( Restful::REST_USERS_ROUTE );

		// An error occurred!
		if ( is_wp_error( $request_decoded ) ) {
			return $request_decoded;
		}

		$response = array(
			'meta' => array(
				'page'    => 1,
				'pages'   => max( absint( count( $request_decoded ) / 10 ), 1 ),
				'perpage' => - 1,
				'total'   => count( $request_decoded ),
				'sort'    => 'asc',
				'field'   => 'ID',
			),
		);

		// Construct the response.
		foreach ( $request_decoded as $user ) {
			$response['data'][] = array(
				'ID'       => $user['id'] ?? 0,
				'Name'     => $user['name'] ?? '',
				'Username' => $user['username'] ?? '',
				'Email'    => $user['email'] ?? '',
				'Phone'    => $user['phone'] ?? '',
				'Website'  => $user['website'] ?? '',
				'Actions'  => $user['id'] ?? 0,
			);
		}

		// Cache for an hour. let's not store the entire response so we can write more unnecessary code for demonstration purposes ONLY.
		set_transient( 'inpsyde_users', $response, absint( apply_filters( 'inpsyde_transient', 3600 ) ) );

		// Return the results.
		return $response;
	}

	/**
	 * Fetch the results from an specific endpoint, and
	 * decode them.
	 *
	 * @param string $endpoint The api endpoint used to fetch data from.
	 *
	 * @return array|\WP_Error An array of user data, or an object of WP_Error on failure.
	 */
	public function get_results( string $endpoint ) {

		// Make the request to the API.
		$request_body = $this->make_request( $endpoint );

		// if the result is empty.
		if ( empty( $request_body ) ) {
			return new \WP_Error( 'inpsyde_empty_response', esc_html__( 'The API has returned an empty response. Please try again later.', 'inpsyde-users' ) );
		}

		// Decode the json.
		try {
			$request_decoded = json_decode( $request_body, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			// Return the json error.
			return new \WP_Error( 'inpsyde_json_error', $e->getMessage() );
		}

		return $request_decoded;
	}

	/**
	 * Make a request to the external API, and return the string.
	 *
	 * @param string $endpoint The endpoint to connect to.
	 *
	 * @return string|\WP_Error The response body.
	 */
	public function make_request( string $endpoint ) {

		// Make a new request to the API.
		$wp_request = wp_safe_remote_get( API_ROOT . $endpoint );

		// Let the API handle the error.
		if ( is_wp_error( $wp_request ) ) {
			return $wp_request;
		}

		return wp_remote_retrieve_body( $wp_request );
	}

	/**
	 * Get a user's detailed information from its ID.
	 *
	 * @param int $user_id The user id of whom we trying to get data.
	 *
	 * @return \WP_Error|array An array of user data, or an object of WP_Error on failure.
	 */
	public function user_info( int $user_id ) {
		/**
		 * We could have simply stored the entire JSON response, but for the
		 * sake of the demonstration, let's make a new request for each
		 * user. Why? Because most proper APIs store the users' details
		 * on a different endpoint, so we need to make a new request for
		 * each user.
		 */

		// Check the cache.
		$cached_details = get_transient( 'inpsyde_user_details' );

		// Check if this specific user id is cached. Otherwise, fetch.
		if ( ! isset( $cached_details[ $user_id ] ) ) {

			// No cache :( Time to make a request.
			$request_decoded = $this->get_results( Restful::REST_USERS_ROUTE );

			// An error occurred!
			if ( is_wp_error( $request_decoded ) ) {
				return $request_decoded;
			}

			// Find the user details if it exists in the array.
			$array_index = array_search( $user_id, array_column( $request_decoded, 'id' ), true );

			// If the user id is invalid.
			if ( false === $array_index ) {
				return new \WP_Error( 'inpsyde_invalid_id', esc_html__( 'The requested user ID is not valid.', 'inpsyde-users' ) );
			}

			// Cache the results.
			$cached_details[ $user_id ] = array(
				'ID'       => isset( $request_decoded[ $array_index ]['id'] ) ? absint( $request_decoded[ $array_index ]['id'] ) : '',
				'name'     => isset( $request_decoded[ $array_index ]['name'] ) ? sanitize_text_field( $request_decoded[ $array_index ]['name'] ) : '',
				'username' => isset( $request_decoded[ $array_index ]['username'] ) ? sanitize_user( $request_decoded[ $array_index ]['username'] ) : '',
				'company'  => isset( $request_decoded[ $array_index ]['company'] ) ? sanitize_user( $request_decoded[ $array_index ]['company']['name'] ) : '',
				'email'    => isset( $request_decoded[ $array_index ]['email'] ) ? sanitize_email( $request_decoded[ $array_index ]['email'] ) : '',
				'phone'    => isset( $request_decoded[ $array_index ]['phone'] ) ? sanitize_text_field( $request_decoded[ $array_index ]['phone'] ) : '',
				'city'     => isset( $request_decoded[ $array_index ]['address'] ) ? sanitize_text_field( $request_decoded[ $array_index ]['address']['city'] ) : '',
				'location' => isset( $request_decoded[ $array_index ]['address'] ) ? sanitize_text_field( $request_decoded[ $array_index ]['address']['street'] ) : '',
				'website'  => isset( $request_decoded[ $array_index ]['website'] ) ? esc_url_raw( $request_decoded[ $array_index ]['website'] ) : '',
				'avatar'   => get_avatar_url( $request_decoded[ $array_index ]['email'] ),
			);
			// Cache the results.
			set_transient( 'inpsyde_user_details', $cached_details, absint( apply_filters( 'inpsyde_transient', 3600 ) ) );
		}

		// Escape the results and output.
		$response = array(
			'ID'       => absint( $cached_details[ $user_id ]['ID'] ),
			'name'     => esc_html( $cached_details[ $user_id ]['name'] ),
			'username' => esc_html( $cached_details[ $user_id ]['username'] ),
			'company'  => esc_html( $cached_details[ $user_id ]['company'] ),
			'email'    => esc_html( $cached_details[ $user_id ]['email'] ),
			'phone'    => esc_html( $cached_details[ $user_id ]['phone'] ),
			'city'     => esc_html( $cached_details[ $user_id ]['city'] ),
			'location' => esc_html( $cached_details[ $user_id ]['location'] ),
			'website'  => esc_html( $cached_details[ $user_id ]['website'] ),
			'avatar'   => esc_url( $cached_details[ $user_id ]['avatar'] ),
		);

		// Return the results.
		return apply_filters( 'inpsyde_user_details', $response );
	}
}
