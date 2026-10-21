<?php

// This is a CONTROLLER. A route in www/routes/web.php points here, and its
// job is to gather up whatever the page needs and then hand off to a view
// that does the actual displaying.
//
// Rule of thumb for the whole course: controllers decide WHAT to show,
// views decide HOW it looks. Controllers don't echo markup.

$page_title = "The template is running";

// Nothing here comes from the database yet -- that's later in the course.
// For now these are just hard-coded values to prove the plumbing works.
$framework_pieces = [
    [
        'file' => 'www/public/index.php',
        'role' => 'The front door. Every request that isn\'t a real file '
            . 'ends up here.',
    ],
    [
        'file' => 'www/routes/web.php',
        'role' => 'Which paths exist, and which controller handles each one.',
    ],
    [
        'file' => 'www/controllers/app/',
        'role' => 'One file per route. Gathers data, requires a view.',
    ],
    [
        'file' => 'www/views/app/',
        'role' => 'The markup. This page is www/views/app/index.php.',
    ],
];

// A deliberately nasty value, to demonstrate what e() is for. Imagine this
// came out of the database because somebody typed it into a form.
$untrusted_value = '<script>alert("Your site just ran my code.")</script>';

view('app/index', [
    'page_title' => $page_title,
    'framework_pieces' => $framework_pieces,
    'untrusted_value' => $untrusted_value,
]);
