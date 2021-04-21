# InpsydeUsers

**This is a WordPress plugin assigned by Inpsyde Gmbh for a demonstration of skills and knowledge.**

----------------------------------------------------------------

#### Table of Content

- [About the Project](#about-the-project)
- [Features](#features)
- [How to Install](#how-to-install)
- [How to Use](#how-to-use)
- [Implementations](#implementations)
    - [Classes](#imp-classes)
    - [Rest Routes](#imp-routes)
    - [Hooks](#imp-hooks)
    - [PHP CodeSniffer](#php-codesniffer)
    - [PHP Unit Tests](#php-unit-tests)
    - [Hooks](#imp-hooks)
    - [External Libraries](#external-libraries)
- [How it works](#how-it-works)
- [Requirements](#requirements)
- [License](#license)

----------------------------------------------------------------

## About the Project

The required task was to create a WordPress plugin that would fetch a list of user data from a third-party API, and then
render the results via a page with a custom permalink.

Use of PHP 8 was avoided due to being fairly new, even though PHP 8 is a personal preference.

## Features

- The plugin was fully developed in an OOP way. There are no extra functions defined in the project.
- It uses the built-in WordPress function `wp_remote_get()` to access the external API.
- All the data are validated, escaped and sanitized.
- All the errors are handled via the built-in `WP_Error` class.
- Translation files available under the `language` directory.

## How to Install

You can use composer to install the plugin, by adding the below to your `composer.json`:

```json
{
	"repositories" : [
		{
			"type" : "vcs",
			"url" : "username@github.com:jackjohansson/inpsydeusers.git"
		}
	]
}
```

You can also clone the repository using `git clone` and then run `composer install`, and it will setup a WordPress
installation for you.

Ultimately, you can download a copy from the `release` branch and upload it via the plugin installer page in WordPress
dashboard. This branch follow the traditional WordPress plugin repository style.

## How to Use

To use the plugin, a user can navigate to `//your-domain/this-is-a-totally-fake-permalink/`. Upon page load, the user
data will be automatically loaded and will be used to populate the Datatable. You can sort and search the table, if you
desire.

By clicking the "View" button in each row, a secondary request will be sent to the server and extra details for that
user will be retrieved. These data will be used to populate the right column, which is only visible if a request has
been sent already.

To flush all the users' cache, click the "Flush" button above the Datatable.

To flush the user details for a specific user, click the "Refresh" button below a user's details.

Note that flushing any data will fetch a new copy and populate the proper column.

## Implementations

### Classes

There are 3 classes available under the "Inpsyde" namespace. These follow the PSR structure. The classes are:

- `Kernel` : Responsible for registering rest routes, hooks, loading assets, and more.
- `Restful` : Responsible for handling a rest request and sending back a response.
- `Users` : Responsible for dealing with the data, fetching from external API, caching, and so.

### Rest Routes

There are 4 customizable rest routes registered by this plugin:

- `inpsde/users` : Used to fetch a list of all users and their basic info.
- `inpsde/user-info` : Used to fetch extra information about a specific user.
- `inpsde/users-flush` : Used to flush all the user data using `DELETE` method
- `inpsde/user-flush` : Used to flush a single user's data using `DELETE` method.

### Hooks

This plugin offers multiple filters to modify the data. Below is a list of the available filters.

- `inpsyde_rest_namespace` : Filters the restful route namespace.
- `inpsyde_rest_users` : Filters the restful path used for fetching a list of users.
- `inpsyde_rest_user_details` : Filters the restful path used for fetching the details about a specific user.
- `inpsyde_rest_users_flush` : Filters the restful path used to flush all of the users' data.
- `inpsyde_rest_user_flush` : Filters the restful path used to flush the data for a specific user.
- `inpsyde_rewrite_rule` : Filters the rewrite rule used to register the custom permalink.
- `inpsyde_localization` : Filters the localization array which is output in the footer and used by jQuery.
- `inpsyde_transient` : Filters the duration of cache, in seconds.
- `inpsyde_user_details` : Filters the user details array that is sent to the client.

### PHP CodeSniffer

The code have been formatted based on the Inpsyde Coding Standards. However, certain parts were omitted as they would
raise serious doubts, such as:

- Using a prefix in a namespaced environment
- Dropping support for PSR-4 in favor of traditional `class-NAME.php`

### PHP Unit Tests

Unit tests have been written using PHPUnitTest and BrainMonkey, and the coverage is for the `Users` class. They are
included under the `tests` folder and `Inpsyde\Tests` namespace.

The test is using multiple syntax for the purpose of demonstration, so certain error messages are expected. In fact,
they are not errors, they're informative messages. Please take a look at the test file's comments, this has been
explained in the docs.

### External Libraries

A set of external libraries have been used by this plugin, as listed below:

- [Datatables](https://datatables.net/) : A table plugin for jQuery to render the users' table.
- [Sweet Alert](https://sweetalert2.github.io/) : A custom JavaScript popup used to view user-friendly messages.
- [BlockUI](https://github.com/malsup/blockui) : A jQuery library used to block certain elements while data is being
  loaded.

Please notice, these assets where not loaded via NPM as they were modified and bundled with another premium HTML/CSS
template that has been used to output the custom page. I state that I'm legally able to use this bundle as a valid
license is present for non-commercial uses ONLY.

## How it works

By visiting the custom permalink mentioned above, a custom templated ( located under `assets/templated/users.php` ) will
be loaded.

When a request is made to any of the registered rest routes, the `Restful` class will parse the request and calls a
proper method from the `Users` class. The method will then follow this sequence:

- Validate the request further. Check for required parameters.
- Check if the requested data exists in the cache, and send it if so.
- If the data does not exist, request a fresh copy from the external API.
- Validate and sanitize the data, and move to next step if everything is fine.
- Construct a proper response, and store a copy in the cache as a transient. Object cache was avoided as it's better be
  used for more crucial reasons.
- Return the response.

After a response is received, it's further parsed by jQuery to make sure it is valid. In that case, it will be used to
populate the datatables. If not, a proper error is shown to the user in a graceful way, using Sweet Alert.

## Requirements

- PHP version 7.4 or higher
- WordPress 4.5 or higher
- Composer

## License

The plugin itself is licensed under GPL v3.

The contents of the "vendor" folder under each styles/scripts have their own licenses which can be found on the author's
website. The author's website URL is stored in the header of each file.