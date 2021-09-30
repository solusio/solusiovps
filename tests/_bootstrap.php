<?php
// Copyright 2021. Plesk International GmbH.

// This is the bootstrap for PHPUnit testing.
if (!defined('WHMCS')) {
    define('WHMCS', true);
}

// Skip module migrations
const SKIP_MIGRATIONS = true;

// Include the WHMCS module.
global $CONFIG;

// Define function for tests
function add_hook(string $name, int $priority, $callback) {
}

function decrypt(string $data) {
    return $data;
}

$CONFIG = [ 'Language' => 'english' ];
require_once __DIR__ . '/../modules/servers/solusiovps/solusiovps.php';
require_once __DIR__ . '/../modules/servers/solusiovps/hooks.php';
require __DIR__ . '/../vendor/autoload.php';

/**
 * Mock logModuleCall function for testing purposes.
 *
 * Inside of WHMCS, this function provides logging of module calls for debugging
 * purposes. The module log is accessed via Utilities > Logs.
 *
 * @param string $module
 * @param string $action
 * @param string|array $request
 * @param string|array $response
 * @param string|array $data
 * @param array $variablesToMask
 *
 * @return void|false
 */
function logModuleCall(
    $module,
    $action,
    $request,
    $response,
    $data = '',
    $variablesToMask = array()
) {
    // do nothing during tests
}
