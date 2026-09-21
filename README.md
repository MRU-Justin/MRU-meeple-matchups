# Project Template for COMP3512-001, Fall 2026

A deliberately small PHP framework, built for this course, inspired by the
framework used in the PHP For Beginners course by Jeffrey Way of Laracasts.

There is no Composer, no autoloader, and no Laravel or Symfony - everything
here is something you can - and over time, should? - read top to bottom.

## System prerequisites

PHP installed locally, with the `pdo_sqlite` extension enabled. No Docker, no
Apache, no XAMPP/MAMP, no database servers, since SQLite needs no server of its own.

Check that you have what you need:

```
php --version          # 8.5 - the latest supported version
php -m | grep sqlite    # should list pdo_sqlite and sqlite3
```

On Windows PowerShell, the second command is:

```
php -m | Select-String sqlite
```

If `php` isn't found at all, PHP isn't on your PATH - that's a setup problem
to fix before anything else here will work.

**On a Mac:** macOS stopped shipping PHP with the operating system in Monterey,
so `php` almost certainly isn't there until you install it. With
[Homebrew](https://brew.sh):

```
brew install php
```

That build includes `pdo_sqlite` already. If `php --version` still reports
something ancient afterwards, an old copy earlier on your PATH is winning -
check with `which -a php`.

## Setup

```
git clone <your repository url>
cd <your repository>
npm install
```

`npm install` is only for the JavaScript linter and formatter - it needs
**Node 20.19 or newer**. No part of the PHP side depends on it, so if you're
only working on the backend you can skip it entirely.

## Running the server

```
php -S localhost:8000 -t www/public www/public/index.php
```

Then visit `http://localhost:8000`. You should get a page confirming the
template is running. If it's unstyled, something is wrong - see
Troubleshooting.

### What that command means

`php -S localhost:8000` starts PHP's built-in web server, listening on port
8000 on your machine. You can use different open ports if your 8000 port is
being used for something else for some reason.

`-t www/public` sets the **document root** to `www/public`. Only files under
that directory can ever be sent to a browser, which is why the rest of the
application lives outside it. Only reveal what you must!

`www/public/index.php` is the **router script**. The built-in server runs it
for **every single request** - including requests for files that really
exist, like your stylesheets and images. It is up to the script to decide
what to do. Returning `false` from it says "never mind, you handle this
one", which hands the request back to the server to serve the file directly.

That is exactly what the first few lines of `www/public/index.php` do: if the
request matches a real file under `www/public`, return `false`; otherwise,
carry on and route it. Delete those lines and every stylesheet on your site
returns a 404.

## How a request travels

```
browser
   |
   v
www/public/index.php     is it a real file? -> yes, server sends it, done
   |                                            (css, js, images, favicon)
   | no
   v
www/routes/web.php       which controller handles this path?
www/routes/api.php
   |
   v
www/controllers/*.php    gather what the page needs
   |
   v
www/views/**/*.php       write the markup
```

## Where things live

| Path                | What's in it                                                                                                                        |
| ------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `www/public/`       | The document root. **The only** directory a browser can reach: CSS, JS, images, and the front-door `index.php`.                     |
| `www/routes/`       | Route tables. `web.php` for pages, `api.php` for JSON endpoints.                                                                    |
| `www/controllers/`  | One file per route. Gathers data, then renders a view with `view()`.                                                                |
| `www/views/`        | Markup. `app/` for pages, `admin/` for restricted pages, `partials/` for reused fragments.                                          |
| `www/core/`         | The framework itself: `Router.php`, `DatabaseHelper.php`.                                                                           |
| `www/queries/`      | Your database queries, one method per question you ask.                                                                             |
| `www/config/`       | Configuration. Currently just where the database file is.                                                                           |
| `www/coretools.php` | Global helper functions, loaded before anything else.                                                                               |
| `database/`         | Your schema, seed data, and the script that builds the database from them. Outside `www/`, so the database can never be downloaded. |
| `scripts/`          | Checks you can run yourself; also run by CI.                                                                                        |

## The framework

This is the whole API. If you find yourself wanting something that isn't
here, that is worth a conversation before you build it - see The rules below.

### Helpers - `www/coretools.php`

| Function                    | What it does                                                             |
| --------------------------- | ------------------------------------------------------------------------ |
| `e($value)`                 | Escapes a value for output. **Use on every value you echo into a view.** |
| `path_to($resource)`        | Absolute path to a file under `www/`, from anywhere.                     |
| `view($path, $data)`        | Renders a view from `www/views/`, handing it `$data`. See below.         |
| `redirect($location)`       | Sends a redirect and stops.                                              |
| `d($var, $title)`           | Dumps a value to the page.                                               |
| `dd($var, $title)`          | Dumps a value and stops execution.                                       |
| `url_path_matches($target)` | True if the current path equals `$target`. Handy for nav styling.        |
| `nuke_session()`            | Clears the session and its cookie.                                       |

### Routing - `www/core/Router.php`

```php
$router->add("GET", "/venues", "venues.php");
$router->add("GET", "/venues/:venue_id", "venue.php");
$router->add("POST", "/venues/:venue_id/featured", "featured_add.php")->restrict("/admin");
```

- `add($method, $path, $controller)` - registers a route. Returns the router,
  so calls can be chained.
- `restrict($redirect_to)` - the route just added requires
  `$_SESSION['authorized']`. An unauthorized visitor is redirected to
  `$redirect_to` (for the admin portal, the `/admin` login page); the argument
  is required. Returns the router.
- A segment beginning with `:` is a path parameter. `/venues/:venue_id` matches
  `/venues/42`, and the controller reads it as `$_GET['venue_id']`.
- Matching is per segment, so `/venues/:venue_id` will **not** match
  `/venues/42/games`. Register that as its own route.
- Trailing slashes don't matter: `/venues` and `/venues/` are the same route.
- No route match produces a 404 and the `www/views/404.php` view.
- A path that exists but not for that method - `POST` to a `GET`-only route -
  produces a 405 and `www/views/405.php`, plus an `Allow` header listing the
  methods that do work. Worth knowing when a form submission lands somewhere
  unexpected: a 405 means the path was right and the method wasn't.

### Rendering a view - `view()`

A controller's last act is to hand off to a view:

```php
$page_title = "All venues";
$venues = $queries->all_venues();

view('venues/index', [
    'page_title' => $page_title,
    'venues' => $venues,
]);
```

`view($relative_path, $data)` roots the path at `www/views/` and adds the
`.php`, so `view('venues/index')` renders `www/views/venues/index.php`. Each
key in `$data` becomes a variable inside that view: `'venues' => $venues` is
what lets the view write `$venues`. A view sees the data you pass it and
nothing else, so what a page depends on is stated in one place.

Pass your data **raw**. The view escapes each value with `e()` at the moment
it echoes it - see [Output escaping](#output-escaping). The controller
doesn't escape, because only the view knows the context a value lands in
(page text, an attribute, a URL), and escaping depends on that context.

Write the array's keys yourself. Passing a user-controlled array straight
through - `view('some/page', $_GET)` - would let a request invent variables
inside your view, which is the one way to misuse this function. The _values_
you pass can be untrusted (that's what `e()` is for); the _keys_ must be
yours.

Render with `view()` rather than `require`-ing a view file yourself. It is the
one sanctioned way to render a page, and `npm run check` flags a controller
that reaches for `require` instead.

### Partials - reusing a fragment of markup

A **partial** is a view included from inside another view. Because `view()` is
just a function, a view can call it too, to drop a shared fragment of markup
into itself. Anything you'd otherwise copy onto every page - a site header, the
document `<head>`, a navigation bar - goes in one file under
`www/views/partials/` and is included wherever it's needed.

You include a partial the same way a controller renders a page - with `view()`:

```php
<?php view('partials/document-head', [
    'page_title' => $page_title,
    'stylesheet' => '/styles/app.css',
]); ?>
```

The data a partial sees is passed in **explicitly**, in that second argument,
exactly like any other view. Each `view()` call runs in its own scope, so a
partial sees only what you hand it - not whatever variables the surrounding
view happens to have. That's a feature: a partial's inputs are named right
where it's used, so what it depends on is never a guess. And, like any view, a
partial escapes every value it echoes with `e()`.

`www/views/partials/document-head.php` is a worked example. It renders the
document `<head>`, and both `www/views/app/index.php` and `www/views/404.php`
include it, each passing the title and stylesheet that page needs.

### A note on naming

Methods here are `snake_case` (`close_connection`, `url_path_matches`) rather
than the `camelCase` you'll see in PSR-12 and in most PHP you find online.
That's a deliberate house style for this course, not an oversight - but it is
a local convention, and code you write outside this project should follow the
wider one.

### Database - `www/core/DatabaseHelper.php`

**Optional.** The template runs fine with no database at all, and nothing is
opened until your code asks for data. If your project doesn't store anything,
skip this section entirely.

If it does, design your tables in `database/schema.sql` and build the database
with `php database/build.php`. The database file itself is not committed - see
[database/README.md](database/README.md) for why, and for how to point at a
different database file.

Every query goes through `run()`, and every value goes in as a parameter -
never concatenated into the SQL string. This is what makes SQL injection
hard to write by accident.

```php
$statement = $this->db_helper->run(
    "SELECT * FROM games WHERE year_published = ? AND play_time_minutes <= ?",
    [$year, $max_minutes]
);

$games = $statement->fetchAll();
```

Write your queries as methods on `DatabaseQueries` in
`www/queries/DatabaseQueries.php`.

### Output escaping

Every value that came from a user, a database, or an API gets escaped on its
way into a page:

```php
<p><?= e($game['title']) ?></p>
```

Two things `e()` cannot do for you:

1. **Quote your attributes.** `<div class=<?= e($x) ?>>` is still
   exploitable; `<div class="<?= e($x) ?>">` is not.
2. **Make a URL safe.** `javascript:alert(1)` contains no special characters,
   so `e()` passes it through untouched. Check the scheme yourself before
   putting a value in an `href` or `src`.

## The rules

Your project must be built on this framework. Concretely:

**You own, and are expected to add to:**

- `www/controllers/`, `www/views/`, `www/routes/`
- `www/public/js/`, `www/public/styles/`
- `www/queries/DatabaseQueries.php` - add all the query methods you like
- `database/` - your own schema and data
- New helper functions in `www/coretools.php`

**Do not change:**

- `www/core/Router.php`
- `www/core/DatabaseHelper.php`

**Do not add dependencies of any kind, with one exception for CSS.** No
Composer, no `vendor/`, no npm packages in dependencies, no CDN `<script>`
tags. Every line of JavaScript your project runs is either in this framework
or written by you.

The exception: you may load a CSS framework from a CDN with a
`<link rel="stylesheet">`. Styling is not behaviour, and a framework saves you
time that is better spent elsewhere. What you may not do is use that
framework's JavaScript - most of them ship a bundle for dropdowns, modals and
the like, and none of it is permitted here. If a component only works when you
also include its script, build it yourself instead.

This is the rule most likely to be broken by accident: search results and AI assistants will confidently suggest Composer, PSR-4 autoloading, and Laravel patterns, because that is what most PHP code looks like. None of it applies here.

Run `npm run check` before you submit. It checks the rules above
mechanically, and it is the same check CI runs on every push. A green check
is not a guarantee of a good mark, but a red one is a problem you want to
find now rather than at grading.

## Checks you can run

```
npm run check         # framework rules: no dependencies, escaping, core untouched
npm run lint:php      # syntax-check every PHP file
npm run smoke         # boot the server and confirm it responds correctly

npm run lint          # ESLint: find problems in your JavaScript
npm run lint:fix      # ...and fix the ones that can be fixed automatically

npm run format        # Prettier: lay your JS and CSS out consistently
npm run format:check  # ...report without changing anything
```

**Linting and formatting are separate jobs.** ESLint looks for things that are
wrong - an unused variable, `==` where you meant `===`, a `body` on a GET
request. Prettier decides where the spaces go and has no opinion about
correctness. Keeping them apart means a lint error always signals a real
problem rather than a misplaced comma.

ESLint is configured in `eslint.config.js`. If you find advice online that
mentions a `.eslintrc.json`, it's describing a format that was removed in
ESLint 10 and will not work here.

## Troubleshooting

**The page loads but has no styling, or images 404.** The static-file check
at the top of `www/public/index.php` has been removed or broken. See "What
that command means" above.

**`Failed to listen on localhost:8000`.** Something is already using that
port - often a server you started earlier and left running. Use a different
port (`-S localhost:8001`) or stop the other process.

**`could not find driver`.** `pdo_sqlite` isn't enabled. Find your `php.ini`
with `php --ini` and uncomment the `extension=pdo_sqlite` line.

**`No SQLite database file here`.** Your code asked for data but no database
has been built yet. Run `php database/build.php`, or - if your project doesn't
store anything - stop constructing `DatabaseQueries`. See
[database/README.md](database/README.md).

**A blank white page.** Almost always a PHP syntax error. Run
`npm run lint:php`, and look at the terminal where the server is running -
errors are printed there.

**Changes don't appear.** Make sure you're editing the file the route
actually points at, then hard-reload (Ctrl+Shift+R / Cmd+Shift+R) in case the
browser cached a stylesheet.

## License

The template is MIT licensed - see [LICENSE](LICENSE). Use it, change it, keep
it in your portfolio, take it to a job.

That covers **this template**. It says nothing about the project you build on
top of it: your own code is yours, and you can license it however you like, or
not at all.

## Known limitations

These are deliberate. They keep the framework small enough to read.

- **The built-in server handles one request at a time.** A page that fires
  several `fetch()` calls gets them answered in sequence, not in parallel, and
  a slow query blocks everything behind it. This is worth remembering in the
  second half of the course, when a page that feels frozen is usually this
  and not a bug in your JavaScript.
- **This server is for development only.** It is not built to be exposed to
  a network.
- **Path parameters arrive in `$_GET`**, alongside real query-string values.
- **Errors are shown in full in the browser**, which is useful while
  developing and would not be acceptable on a public site.
