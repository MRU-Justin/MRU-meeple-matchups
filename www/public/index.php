<?php

// require_once, not require: this file defines functions, and defining the
// same function twice is a fatal error.
require_once __DIR__ . '/../coretools.php';

// PHP's built-in web server runs THIS script for every single request --
// including requests for files that really do exist, like your stylesheets,
// your JavaScript, and your images.
//
// Returning false is how we say "never mind, you handle this one", which
// hands the request back to the server so it can send the file itself.
//
// Without these lines, every stylesheet you write returns a 404.
//
// Note the .php check. Handing a PHP file back to the server would make the
// server run it as though it were the page -- a second time, in the same
// request, after this script already ran. That's both a crash and a hole:
// every request is supposed to go through the router, so no .php file under
// public/ should ever be reachable on its own.
//
// (You don't have to guard against "../" tricks here: the built-in server
// rejects those before this script ever runs.)
if (PHP_SAPI === 'cli-server') {
    $requested_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requested_file = __DIR__ . $requested_path;

    $is_php = str_ends_with(strtolower($requested_path), '.php');

    if (is_file($requested_file) && !$is_php) {
        return false;
    }
}

// This will be uncommented and explained later in the course:
// session_start();

require path_to('core/Router.php');

$router = new Router();

require path_to('routes/web.php');

require path_to('routes/api.php');


$resource_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

try {
    $router->route($resource_path, $method);
} catch (Exception $e) {
    // Something went wrong that we didn't plan for.
    //
    // Setting the status code matters: a browser -- and any fetch() call you
    // write in the second half of the course -- decides whether a request
    // succeeded by looking at the status. Dumping an error message at 200
    // would tell your JavaScript that everything went fine.
    http_response_code(500);
    dd($e->getMessage(), "Something went wrong");
}
