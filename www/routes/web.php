<?php

// Every path your site responds to gets listed here, along with the
// controller in www/controllers/ that handles it.
//
// add() returns the router itself, so these can be chained. Call restrict()
// after a route to require an authorized session for it:
//
//    $router->add("GET", "/dashboard", "dashboard.php")->restrict("/admin");
//
// The argument to restrict() is required: it's the path an unauthorized
// visitor gets redirected to, and where to send them is a decision the route
// has to make. Here that's "/admin", the login page.
//
// A path segment starting with a colon is a path parameter. A route of
// "/venues/:venue_id" matches /venues/42, and the controller reads the
// value as $_GET['venue_id'].

$router->add("GET", "/", "app/index.php");
$router->add("GET", "/admin", "admin/login.php");
$router->add("GET", "/admin/dashboard", "admin/dashboard.php");
$router->add("GET", "/admin/members", "admin/members.php")->restrict("/admin");
$router->add("GET", "/admin/venues", "admin/venues.php")->restrict("/admin");
