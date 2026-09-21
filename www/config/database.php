<?php

// Where the SQLite database lives.
//
// SQLite keeps an entire database in one file, so "connecting" to it is really
// just opening that file.
//
// The default is database/app.db at the top of the project. To point at a
// different one -- useful when you want to try something out without
// disturbing your real data -- set the COMP3512_DB environment variable
// rather than editing this file:
//
//   PowerShell:  $env:COMP3512_DB="scratch.db"; php -S localhost:8000 -t www/public www/public/index.php
//   macOS:       COMP3512_DB=scratch.db php -S localhost:8000 -t www/public www/public/index.php
//
// A bare filename is looked for inside database/. A full path is used as it is.
//
// Note that nothing opens this file until your code actually asks for data.
// A project that doesn't use a database never touches any of this.

$configured = getenv('COMP3512_DB');

if ($configured === false || $configured === '') {
    $configured = 'app.db';
}

// Does it look like a full path already? Either a drive letter (C:\...) or a
// leading slash. Anything else is treated as a name inside database/.
$is_full_path = preg_match('#^([a-zA-Z]:[\\\\/]|/|\\\\)#', $configured) === 1;

return [
    'path' => $is_full_path
        ? $configured
        : path_to("../database/$configured"),
];
