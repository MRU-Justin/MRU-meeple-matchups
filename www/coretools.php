<?php

define('PROJECT_ROOT', __DIR__);

/**
 * Helps get you out of relative path hell.
 * Provides the proper path to a given file, rooted at www (on this Codespace).
 * 
 * Example of use: 
 *   path_to('core/Router.php') gives you the proper path to Router.php, 
 *   no matter what directory you're in.
 */
function path_to($resource)
{
    return PROJECT_ROOT . "/$resource";
}

/**
 * Renders a view, rooted in the views directory.
 *
 * A view is just a PHP file full of markup, so "rendering" one is really
 * nothing more than require. This wrapper does two small things that add up:
 *
 *   1. Roots the path at views/ and adds the .php, so a controller writes
 *          view('app/index')
 *      instead of
 *          require path_to('views/app/index.php').
 *
 *   2. Passes the page's data through $data rather than through shared scope.
 *      The require below runs in THIS function's scope, not the controller's,
 *      so a view can't accidentally see whatever variables the controller
 *      happened to leave lying around. Everything a view needs, the
 *      controller has to name out loud:
 *
 *          view('venues/show', ['venue' => $venue]);
 *
 *      extract() then turns each key of $data into a plain variable ($venue)
 *      for the view to use.
 *
 * The values in $data arrive RAW. Escaping is the VIEW'S job, done with e()
 * at the moment each value is echoed -- see e() below for why that can't be
 * done for you up here in the controller.
 *
 * A word on extract(): the PHP manual warns not to extract() untrusted data,
 * and it's right -- but the danger is untrusted KEYS, because keys become
 * variable names. Here the keys are the array literal a controller writes
 * ('venue', 'page_title'), never user input, so the warning doesn't apply.
 * The one thing you must never do is hand this function a user-controlled
 * array itself -- view('some/page', $_GET) -- which would let a request
 * invent variables in your view. Pass an array whose keys YOU wrote.
 *
 * Two things below make that misuse survivable rather than catastrophic:
 * the file to require is resolved BEFORE extract() runs, and EXTR_SKIP stops
 * any key in $data from overwriting a variable that already exists here. So
 * even view('some/page', $_GET) can't redirect the require to another file.
 */
function view($relative_path, $data = [])
{
    // Resolved first, so nothing in $data can change WHICH view is rendered.
    $view_file = path_to("views/{$relative_path}.php");

    extract($data, EXTR_SKIP);

    require $view_file;
}

/**
 * "Escape" -- the single most important function in this file.
 *
 * Any value that came from a user, a database, or an API is just DATA. But
 * the moment you echo it into a page, the browser reads it as HTML. So a
 * venue name of <img src=x onerror=alert(1)> stops being a name and
 * becomes a tag that runs.
 *
 * This translates the characters that would change how the browser reads the
 * page (< > & " ') into escape sequences that LOOK identical on screen but
 * are read as ordinary text.
 *
 * USE THIS EVERY TIME YOU ECHO A VALUE INTO A VIEW. Every time. No exceptions.
 *
 *   Wrong:  <p><?= $venue['name'] ?></p>
 *   Right:  <p><?= e($venue['name']) ?></p>
 *
 * The same idea as using prepared statements for SQL: the value is crossing
 * from one language into another, so it has to be translated on the way.
 *
 * Named e() rather than escape() on purpose -- you'll type it hundreds of
 * times, and the safe version has to be as easy to type as the unsafe one.
 * (Laravel calls its version e() too, for exactly the same reason.)
 *
 * Two things e() canNOT do for you:
 *   1. Quote your attributes. <div class=<?= e($x) ?>> is still exploitable;
 *      <div class="<?= e($x) ?>"> is not. Always quote attributes.
 *   2. Make a URL safe. A $url of "javascript:alert(1)" has no special
 *      characters in it, so e() passes it through untouched.
 */
function e($value)
{
    // The (string) cast matters: database columns are often null, and
    // passing null straight to htmlspecialchars is deprecated in PHP 8.1+.
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// "Dump and Die"
// Useful for those situations where you want to do a trace statement,
// and then want to immediately stop execution so that other things later
// on in the code don't happen!
function dd($var, $title = "")
{
    d($var, $title);
    die();
}


// Got this from a Laracasts episode at one point? Or a php.net comment?
// Modified it a bit, because OCD.
// Yes, this function totally violates my suggestion of "don't echo markup!"
function d($var, $title = "")
{
    // Put a horizontal rule in to visually separate things in browser.
    echo "<hr>";

    // If a title was passed in, use it.
    if ($title) {
        echo "<p><b>$title</b>:</p>";
    }

    // Put the variable between <pre> tags for formatting purposes.
    // If the thing coming in is just a string, no need for var_dump().
    echo "<pre>";
    if (gettype($var) === "string") {
        // Escaped, same as anything else we echo -- otherwise dumping a value
        // that happens to contain markup would both mangle this output and
        // set a bad example. See e() above.
        echo e($var);
    } else {
        var_dump($var);
    }
    echo "</pre>";

    echo "<hr>";
}

/**
 * Useful for styling navigation - returns true iff the given target
 * matches the current URL path.
 * 
 * Currently breaks if there's a query string. There are ways around that,
 * but that's an exercise for the reader. 😁
 * 
 * Saw this on Laracasts at some point. Stupid memory....
 */
function url_path_matches($target)
{
    // parse_url gives us just the path, so a query string on the end
    // (?page=2) no longer breaks the comparison.
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $target;
}


// Modified from: https://www.php.net/manual/en/function.session-destroy.php#example-4820
// 
// Assumption: session_start() has been called prior to this call.
function nuke_session()
{

    // Clear all session variables.
    $_SESSION = [];


    // Wipe out the PHPSESSID cookie on the browser side.
    $params = session_get_cookie_params();
    setcookie('PHPSESSID', '', strtotime("-1 month"), $params['path'], $params['domain'], $params['secure'], $params['httponly']);

    session_destroy();
}

// Convenience wrapper to do redirects. Mostly used for authorization,
// hence the 302, but could be used in other ways, too.
function redirect($location, $code = 302)
{
    header("Location: {$location}", true, $code);
    // Make sure no code executes after this point in case browser has been told to ignore redirects.
    die();
}