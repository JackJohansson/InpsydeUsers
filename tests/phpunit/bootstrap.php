<?php

/**
 * Autoloader for the PHPUnit tests.
 *
 * @package InpsydeUsers
 */

// Get the path to autoloader.
$autoload = realpath(__DIR__ . '/../../vendor/autoload.php');

// Load if exists, or terminate.
if (!file_exists($autoload)) {
     echo 'Can not load the autoloader. Are you sure you ran the composer installer?';
    exit();
}
    require_once $autoload;
