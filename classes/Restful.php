<?php

/**
 * Class used to handle the rest requests.
 *
 * @package Inpsyde
 */

declare(strict_types=1);

namespace Inpsyde;

/**
 * Class Restful
 *
 * @package Inpsyde
 */
class Restful
{

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
    public static function listUsers(\WP_REST_Request $request): array
    {

        $users = new Users();

        // List all the users.
        $userList = $users->listUsers();

        // If there's an error.
        if (empty($userList)) {
            return [
                'success' => false,
                'message' => $users->errorMessage(),
            ];
        }

        return $userList;
    }

    /**
     * Flush a single user's data.
     *
     * @param \WP_REST_Request $request The request object passed to the callback function.
     *
     * @return array An array of data sent back to the user.
     */
    public static function userFlush(\WP_REST_Request $request): array
    {

        $users = new Users();

        $userId = $request->get_param('user_id');

        if (null === $userId) {
            return [
                'success' => false,
                'message' => esc_html__(
                    'The required parameter ID is missing from the request. Please try again later.',
                    'inpsyde-users'
                ),
            ];
        }

        $flushResult = $users->flushUser((int)$userId);

        // If there's an error.
        if (!$flushResult) {
            return [
                'success' => false,
                'message' => $users->errorMessage(),
            ];
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Handle the request to list the details
     * for a specific user.
     *
     * @param \WP_REST_Request $request The request object passed to the callback function.
     *
     * @return array An array of data sent back to the user.
     */
    public static function userInfo(\WP_REST_Request $request): array
    {

        $users = new Users();

        // Get user's ID.
        $userId = $request->get_param('id');

        // If the id is missing.
        if (null === $userId) {
            return [
                'success' => false,
                'message' => esc_html__(
                    'The required parameter ID is missing from the request. Please try again later.',
                    'inpsyde-users'
                ),
            ];
        }

        // Fetch the details.
        $userInfo = $users->userInfo((int)$userId);

        // If there's an error.
        if (empty($userInfo)) {
            return [
                'success' => false,
                'message' => $users->errorMessage(),
            ];
        }

        // Return the results.
        return $userInfo;
    }

    /**
     * Flush all the users' details.
     *
     * @param \WP_REST_Request $request The request object passed to the callback function.
     *
     * @return array An array of data sent back to the user.
     */
    public static function usersFlush(\WP_REST_Request $request): array
    {

        $users = new Users();

        $flushResult = $users->flushAll();

        // If there's an error.
        if (!$flushResult) {
            return [
                'success' => false,
                'message' => $users->errorMessage(),
            ];
        }

        return [
            'success' => true,
        ];
    }
}
