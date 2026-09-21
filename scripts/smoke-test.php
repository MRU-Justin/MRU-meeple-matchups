<?php

/**
 * Boots the server and confirms it still does the three things it must do:
 * serve a page, serve a static file, and 404 an unknown path.
 *
 * Run it with `npm run smoke`. It's the fastest way to find out that
 * something fundamental broke.
 *
 * Exits 0 if everything passes, 1 if anything failed.
 */

$project_root = dirname(__DIR__);
$port = 8765;
$base = "http://localhost:$port";

// Refuse to run if something is already listening. Otherwise our server
// can't bind, and every check below would quietly pass or fail against
// whatever else is on that port -- which is a far more confusing outcome
// than just stopping.
$existing = @fsockopen('localhost', $port, $error_code, $error_message, 0.3);

if ($existing) {
    fclose($existing);
    echo "Port $port is already in use.\n";
    echo "Something is still listening there -- probably a server left over\n";
    echo "from an earlier run. Stop it and try again.\n";
    exit(1);
}

// Passing the command as an array rather than a string matters: a string is
// handed to the shell, so the process we'd get back is the shell, and
// proc_terminate would kill that while leaving the actual server running and
// holding the port. An array skips the shell entirely.
//
// PHP_BINARY is the interpreter running this script, so the server is
// guaranteed to be the same PHP you invoked us with.
$command = [
    PHP_BINARY,
    '-S',
    "localhost:$port",
    '-t',
    "$project_root/www/public",
    "$project_root/www/public/index.php",
];

$descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

$server = proc_open($command, $descriptors, $pipes, $project_root);

if (!is_resource($server)) {
    echo "Could not start the server.\n";
    exit(1);
}

// Make sure the server is stopped however this script ends -- including if a
// check throws, or somebody hits Ctrl-C.
register_shutdown_function(function () use ($server, $pipes) {
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }

    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
});

// Give the server a moment to bind to the port before asking it anything.
$ready = false;

for ($attempt = 0; $attempt < 40; $attempt++) {
    $probe = @fsockopen('localhost', $port, $error_code, $error_message, 0.2);

    if ($probe) {
        fclose($probe);
        $ready = true;
        break;
    }

    usleep(100000); // 0.1s
}

/**
 * Fetches a path and returns [status_code, body].
 */
function fetch($url, $method = 'GET')
{
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    $status = 0;

    // PHP 8.5 deprecated the magic $http_response_header variable in favour
    // of a function. Support both, so this runs on whatever students have.
    $headers = function_exists('http_get_last_response_headers')
        ? http_get_last_response_headers()
        : ($http_response_header ?? []);

    if (isset($headers[0])
        && preg_match('#HTTP/\S+\s+(\d+)#', $headers[0], $m)) {
        $status = (int) $m[1];
    }

    return [$status, $body === false ? '' : $body];
}

$failures = [];

function check(&$failures, $description, $passed, $detail = '')
{
    echo ($passed ? '  ok    ' : '  FAIL  ') . $description . "\n";

    if (!$passed) {
        if ($detail !== '') {
            echo "          $detail\n";
        }

        $failures[] = $description;
    }
}

echo "Smoke test against $base\n\n";

if (!$ready) {
    echo "  FAIL  server never started listening on port $port\n";
    exit(1);   // the shutdown function stops the server
}

// 1. The home page renders through a controller and a view.
[$status, $body] = fetch("$base/");
check($failures, 'GET / returns 200', $status === 200, "got $status");
check(
    $failures,
    'GET / renders the index view',
    str_contains($body, 'The template is running'),
    'expected page text not found -- is www/controllers/app/index.php present?'
);
check(
    $failures,
    'GET / has no PHP errors in the output',
    !preg_match('/(Fatal error|Warning:|Deprecated:)/', $body),
    'PHP emitted an error into the page'
);

// 2. Static files are served rather than routed. This is the one that breaks
//    if the cli-server check at the top of index.php goes missing.
[$status, $body] = fetch("$base/styles/app.css");
check($failures, 'GET /styles/app.css returns 200', $status === 200, "got $status");
check(
    $failures,
    'GET /styles/app.css returns CSS, not the 404 page',
    str_contains($body, '--ink') && !str_contains($body, '<!DOCTYPE'),
    'the router handled a request that should have been a static file'
);

// 3. A .php file under public/ is never served or executed on its own --
//    everything goes through the router.
[$status, $body] = fetch("$base/index.php");
check(
    $failures,
    'GET /index.php does not execute the front controller directly',
    !str_contains($body, 'Cannot redeclare'),
    'the static-file check handed a .php file back to the server'
);
check($failures, 'GET /index.php returns 404', $status === 404, "got $status");

// 4. Output is escaped.
[, $body] = fetch("$base/");
check(
    $failures,
    'the untrusted demo value is escaped in the page',
    str_contains($body, '&lt;script&gt;')
        && !str_contains($body, '<script>alert('),
    'a raw <script> tag reached the page -- e() is missing somewhere'
);

// 5. The template works with no database whatsoever, and doesn't create one
//    behind your back. Lectures and projects that don't store anything depend
//    on this, so it's checked rather than assumed.
$database_files = glob("$project_root/database/*.db");

check(
    $failures,
    'no database file is created just by serving a page',
    $database_files === [],
    'found: ' . implode(', ', array_map('basename', $database_files ?: []))
        . ' -- something opened a connection it did not need, or SQLite '
        . 'created an empty file'
);

// 6. A path that exists but not for that method reports 405, not 404.
[$status] = fetch("$base/", 'POST');
check($failures, 'POST to a GET-only route returns 405', $status === 405, "got $status");

// 7. An unknown path 404s with the 404 view.
[$status, $body] = fetch("$base/definitely-not-a-real-path");
check($failures, 'an unknown path returns 404', $status === 404, "got $status");
check(
    $failures,
    'an unknown path renders the 404 view',
    str_contains($body, 'Page Not Found'),
    'expected www/views/404.php'
);

echo "\n";

if (empty($failures)) {
    echo "Smoke test passed.\n";
    exit(0);
}

echo 'Smoke test FAILED -- ' . count($failures) . " check(s).\n";
exit(1);
