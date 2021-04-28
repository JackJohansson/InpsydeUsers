<?php

	/**
	 * Test class used to test the methods of the
	 * Inpsyde\Users class.
	 *
	 * @package Inpsyde
	 */

	declare( strict_types = 1 );

	// @codingStandardsIgnoreStart
	namespace Inpsyde\Tests\Unit;

	use Brain\Monkey;
	use Brain\Monkey\Functions;
	use PHPUnit\Framework\TestCase;

	// Mock the constant.
	define( 'Inpsyde\API_ROOT', '' );

	/**
	 * Class UsersTest
	 *
	 * @package Inpsyde
	 */
	class UsersTest extends TestCase {
		/**
		 * An array of user's data.
		 *
		 * @var $transient_data array
		 */
		protected array $transient_data = [];

		/**
		 * An example of a user's data.
		 *
		 * @var $user array
		 */
		protected array $user = [
			'id'       => 123,
			'name'     => 'Fake Name',
			'username' => 'Fake Username',
			'email'    => 'fake@mail.test',
			'phone'    => '1234567890',
			'website'  => 'www.example.com',
		];

		/**
		 * Mock the required functions.
		 *
		 */
		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();

			Functions\stubs(
				[
					'delete_transient' => TRUE,
					'get_transient'    => TRUE,
					'set_transient'    => TRUE,
					'WP_Error'         => \Mockery::mock( '\WP_Error' ),
				]
			);

			Functions\stubs(
				[
					'esc_attr',
					'esc_html',
					'esc_textarea',
					'__',
					'_x',
					'esc_html__',
					'esc_html_x',
					'esc_attr_x',
					'esc_url',
					'esc_url_raw',
					'wp_remote_retrieve_body',
					'wp_safe_remote_get',
				]
			);

			// Let's make it as realistic as possible.
			Functions\when( 'delete_transient' )->alias(
				function ( $key ) {
					unset( $this->transient_data[ $key ] );

					return TRUE;
				}
			);

			Functions\when( 'get_transient' )->alias(
				function ( $key ) {
					$this->transient_data[ $key ] = $this->user;

					return $this->transient_data[ $key ];
				}
			);

			Functions\when( 'set_transient' )->alias(
				function ( $key ) {
					$this->transient_data[ $key ] = $this->user;

					return $this->transient_data[ $key ];
				}
			);
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}

		/**
		 * Test the flushAll() method. This method only returns
		 * true, or a WP_Error.
		 *
		 */
		public function testFlush_all() {
			$users  = new \Inpsyde\Users();
			$result = $users->flushAll();

			/**
			 * Syntax 1 of assertion. A single conditional assert.
			 */
			$this->assertIsBool( $result );
		}

		/**
		 * Test the flushUser() method. This method only
		 * returns true, or a WP_Error.
		 *
		 */
		public function testFlush_user() {
			$users = new \Inpsyde\Users();

			$results = $users->flushUser( 0 );

			// If there's an error, the error message should not be empty. The return value is always a boolean.
			if ( $results ) {
				$this->assertEmpty( $users->errorMessage() );
			} else {
				$this->assertNotEmpty( $users->errorMessage() );
			}
		}

		/**
		 * Test the fetchResults method. This method only returns
		 * an array, or an instance of WP_Error. The result can not be empty.
		 *
		 *
		 */
		public function testGetResults() {

			$users   = new \Inpsyde\Users();
			$results = $users->fetchResults( \Inpsyde\Restful::REST_USERS_ROUTE );

			// The result must be a non-empty array, otherwise there must be an error report.
			if ( empty( $results ) ) {
				$this->assertNotEmpty( $users->errorMessage() );
			} else {
				$this->assertEmpty( $users->errorMessage() );
			}
		}

		/**
		 * Test the listUsers() method. This method will only return
		 * a non-empty array, or an instance of WP_Error.
		 *
		 */
		public function testListUsers() {

			$users   = new \Inpsyde\Users();
			$results = $users->listUsers();

			// Syntax 1 of assertion. Multiple conditions in a single assert.
			$this->assertTrue( ( ! empty( $results ) && is_array( $results ) ) || ( '' !== $users->errorMessage() ) );
		}

		/**
		 * Test the results of the makeRequest() method.
		 * We use the syntax 2 of assertion here, using multiple
		 * asserts for the sake of demonstration.
		 *
		 */
		public function testMakeRequest() {
			$users = new \Inpsyde\Users();

			// Make a request. Request response can be either a string or WP_Error
			$results = $users->makeRequest( \Inpsyde\Restful::REST_USERS_ROUTE );

			// If the response is empty, there's an error.
			if ( empty( $results ) ) {
				$this->assertNotEmpty( $users->errorMessage() );
			} else {
				$this->assertEmpty( $users->errorMessage() );
			}
		}

		/**
		 * Test the results of userInfo() method. This method
		 * returns an array of data on success, or a WP_Error
		 * on failure.
		 *
		 */
		public function testUserInfo() {
			$users = new \Inpsyde\Users();

			// This should always be either an array or a WP_Error.
			$results = $users->userInfo( 0 );

			// Results are a non-empty array, otherwise there's an error message.
			if ( empty( $results ) ) {
				$this->assertNotEmpty( $users->errorMessage() );
			} else {
				$this->assertEmpty( $users->errorMessage() );
			}

		}
	}
