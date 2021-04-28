<?php

/**
 * Class used to handle the user's data.
 *
 * @package Inpsyde
 */

declare(strict_types=1);

namespace Inpsyde;

/**
 * Class Users
 *
 * @package Inpsyde
 */
class Users
{
    /**
     * Holds the error code for the last ocurred error.
     *
     * @var string
     */
    private static string $lastErrorCode = '';
    /**
     * Holds the last occurred error.
     *
     * @var string $lastErrorMessage
     */
    private static string $lastErrorMessage = '';

    /**
     * Returns the last error code.
     * @return string
     */
    public function errorCode(): string
    {
        return self::$lastErrorCode;
    }

    /**
     * Returns the last error message.
     * @return string
     */
    public function errorMessage(): string
    {
        return self::$lastErrorMessage;
    }

    /**
     * Flush all the users from the cache.
     */
    public function flushAll(): bool
    {

        $flushUsers = delete_transient('inpsyde_users');
        $flushDetails = delete_transient('inpsyde_user_details');

        if (! $flushUsers) {
            $this->setError(
                'inpsyde_flush_failed',
                esc_html__(
                    'Could not flush the user cache. This is mostly because the results were already flushed.',
                    'inpsyde-users'
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Set the last error message and code.
     * @param string $code
     * @param string $message
     */
    private function setError(string $code, string $message)
    {

        self::$lastErrorCode = $code;
        self::$lastErrorMessage = $message;
    }

    /**
     * Remove a single user from the cache.
     *
     * @param int $userId The id of user which we want its data flushed.
     *
     * @return bool The result of flushing.
     */
    public function flushUser(int $userId): bool
    {

        $cache = get_transient('inpsyde_user_details');

        if (false === $cache || ! isset($cache[ $userId ])) {
            $this->setError(
                'inpsyde_flush_failed',
                esc_html__('This user ID is not cached yet.', 'inpsyde-users')
            );
            return false;
        }

        // Remove the current user.
        unset($cache[ $userId ]);

        // Apply filters.
        $transient = absint(apply_filters('inpsyde_transient', 3600));

        // Save.
        $saveResult = set_transient('inpsyde_user_details', $cache, $transient);

        // If saving has failed.
        if (! $saveResult) {
            $this->setError(
                'inpsyde_flush_failed',
                esc_html__(
                    'Could not save the cache into database. Please try again later.',
                    'inpsyde-users'
                )
            );
            return false;
        }
        return true;
    }

    /**
     * List all the users and their information.
     *
     * @return array An empty array on failure, or an array of user data.
     */
    public function listUsers(): array
    {

        // Check the cache first.
        $cachedUsers = get_transient('inpsyde_users');

        // This is already properly formatted.
        if (false !== $cachedUsers) {
            return $cachedUsers;
        }

        // Make a new request to the API.
        $requestDecoded = $this->fetchResults(Restful::REST_USERS_ROUTE);

        // An error occurred!
        if (empty($requestDecoded)) {
            return [];
        }

        $response = [
            'meta' => [
                'page' => 1,
                'pages' => max(absint(count($requestDecoded) / 10), 1),
                'perpage' => -1,
                'total' => count($requestDecoded),
                'sort' => 'asc',
                'field' => 'ID',
            ],
        ];

        // Construct the response.
        foreach ($requestDecoded as $user) {
            $response['data'][] = [
                'ID' => $user['id'] ?? 0,
                'Name' => $user['name'] ?? '',
                'Username' => $user['username'] ?? '',
                'Email' => $user['email'] ?? '',
                'Phone' => $user['phone'] ?? '',
                'Website' => $user['website'] ?? '',
                'Actions' => $user['id'] ?? 0,
            ];
        }

        // Cache for an hour. let's not store the entire response so we can write more unnecessary code for demonstration purposes ONLY.
        set_transient('inpsyde_users', $response, absint(apply_filters('inpsyde_transient', 3600)));

        // Return the results.
        return $response;
    }

    /**
     * Fetch the results from an specific endpoint, and
     * decode them.
     *
     * @param string $endpoint The api endpoint used to fetch data from.
     *
     * @return array An array of user data, or an empty array on failure.
     */
    public function fetchResults(string $endpoint): array
    {

        // Make the request to the API.
        $requestBody = $this->makeRequest($endpoint);

        // if the result is empty.
        if (empty($requestBody)) {
            $this->setError(
                'inpsyde_empty_response',
                esc_html__(
                    'The API has returned an empty response. Please try again later.',
                    'inpsyde-users'
                )
            );
            return [];
        }

        // Decode the json.
        try {
            $requestDecoded = json_decode($requestBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            // Return the json error.
            $this->setError('inpsyde_json_error', $exception->getMessage());
            return [];
        }

        return $requestDecoded;
    }

    /**
     * Make a request to the external API, and return the string.
     *
     * @param string $endpoint The endpoint to connect to.
     *
     * @return string The response body.
     */
    public function makeRequest(string $endpoint): string
    {

        // Make a new request to the API.
        $wp_request = wp_safe_remote_get(API_ROOT . $endpoint);

        // Let the API handle the error.
        if (is_wp_error($wp_request)) {
            $this->setError($wp_request->get_error_code(), $wp_request->get_error_message());
            return '';
        }

        return wp_remote_retrieve_body($wp_request);
    }

    /**
     * Get a user's detailed information from its ID.
     *
     * @param int $userId The user id of whom we trying to get data.
     *
     * @return array An array of user data, or an empty array on failure.
     */
    public function userInfo(int $userId): array
    {
        /**
         * We could have simply stored the entire JSON response, but for the
         * sake of the demonstration, let's make a new request for each
         * user. Why? Because most proper APIs store the users' details
         * on a different endpoint, so we need to make a new request for
         * each user.
         */

        // Check the cache.
        $cachedDetails = get_transient('inpsyde_user_details');

        // Check if this specific user id is cached. Otherwise, fetch.
        if (! isset($cachedDetails[ $userId ])) {
            // No cache :( Time to make a request.
            $requestDecoded = $this->fetchResults(Restful::REST_USERS_ROUTE);

            // An error occurred!
            if (empty($requestDecoded)) {
                return [];
            }

            // Find the user details if it exists in the array.
            $arrayIndex = array_search($userId, array_column($requestDecoded, 'id'), true);

            // If the user id is invalid.
            if (false === $arrayIndex) {
                $this->setError(
                    'inpsyde_invalid_id',
                    esc_html__('The requested user ID is not valid.', 'inpsyde-users')
                );
                return [];
            }

            // Cache the results.
            $cachedDetails[$userId] = $this->buildUserData($requestDecoded[ $arrayIndex ]);

            // Apply user filters.
            $transient = absint(apply_filters('inpsyde_transient', 3600));
            // Cache the results.
            set_transient('inpsyde_user_details', $cachedDetails, $transient);
        }

        // Escape the results and output.
        $response = [
            'ID' => absint($cachedDetails[ $userId ][ 'ID']),
            'name' => esc_html($cachedDetails[ $userId ][ 'name']),
            'username' => esc_html($cachedDetails[ $userId ][ 'username']),
            'company' => esc_html($cachedDetails[ $userId ][ 'company']),
            'email' => esc_html($cachedDetails[ $userId ][ 'email']),
            'phone' => esc_html($cachedDetails[ $userId ][ 'phone']),
            'city' => esc_html($cachedDetails[ $userId ][ 'city']),
            'location' => esc_html($cachedDetails[ $userId ][ 'location']),
            'website' => esc_html($cachedDetails[ $userId ][ 'website']),
            'avatar' => esc_url($cachedDetails[ $userId ][ 'avatar']),
        ];

        // Return the results.
        return apply_filters('inpsyde_user_details', $response);
    }

    /**
     * Build user's data based on a decoded json response.
     * @param array $data
     *
     * @return array
     */
    public function buildUserData(array $data): array
    {
        $location = isset($data['address']) ? sanitize_text_field($data['address']['street']) : '';
        return [
            'ID' => isset($data['id']) ? absint($data['id']) : '',
            'name' => isset($data['name']) ? sanitize_text_field($data['name']) : '',
            'username' => isset($data['username']) ? sanitize_user($data['username']) : '',
            'company' => isset($data['company']) ? sanitize_user($data['company']['name']) : '',
            'email' => isset($data['email']) ? sanitize_email($data['email']) : '',
            'phone' => isset($data['phone']) ? sanitize_text_field($data['phone']) : '',
            'city' => isset($data['address']) ? sanitize_text_field($data['address']['city']) : '',
            'location' => $location,
            'website' => isset($data['website']) ? esc_url_raw($data['website']) : '',
            'avatar' => get_avatar_url($data['email']),
        ];
    }
}
