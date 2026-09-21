<?php

/**
 * Checks the rules in the README's "The rules" section, mechanically.
 *
 * Run it yourself with `npm run check`. CI runs the same thing on every push,
 * so anything it complains about is something you want to fix now rather
 * than discover at grading time.
 *
 * Exits 0 if everything passes, 1 if anything failed.
 */

$project_root = dirname(__DIR__);

$problems = [];

function problem(&$problems, $rule, $where, $detail)
{
    $problems[] = ['rule' => $rule, 'where' => $where, 'detail' => $detail];
}

/**
 * Every PHP/JS/HTML file we care about, skipping things we don't own.
 */
function project_files($root)
{
    // 'scripts' holds the checks themselves. They talk about the rules, so
    // scanning them would flag their own descriptions of what's forbidden.
    $skip = ['.git', 'node_modules', 'vendor', 'logs', '.claude', 'scripts'];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            function ($file) use ($skip) {
                return !in_array($file->getFilename(), $skip, true);
            }
        )
    );

    $files = [];

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

function relative_to($path, $root)
{
    return str_replace('\\', '/', substr($path, strlen($root) + 1));
}


$files = project_files($project_root);


// ---------------------------------------------------------------------------
// Rule 1: no dependencies.
// ---------------------------------------------------------------------------

foreach (['composer.json', 'composer.lock'] as $forbidden) {
    if (file_exists("$project_root/$forbidden")) {
        problem(
            $problems,
            'No dependencies',
            $forbidden,
            'Composer is not used in this course. Remove this file.'
        );
    }
}

if (is_dir("$project_root/vendor")) {
    problem(
        $problems,
        'No dependencies',
        'vendor/',
        'A vendor directory means a package manager was used.'
    );
}

$package_json_path = "$project_root/package.json";

if (file_exists($package_json_path)) {
    $package = json_decode(file_get_contents($package_json_path), true);

    if (!empty($package['dependencies'])) {
        problem(
            $problems,
            'No dependencies',
            'package.json',
            'Runtime dependencies found: '
                . implode(', ', array_keys($package['dependencies']))
                . '. Only devDependencies (the linter) are allowed.'
        );
    }
}

// Third-party code pulled in from a CDN counts as a dependency too.
//
// CSS is the one exception. A stylesheet is declarative -- it ships no
// behaviour -- and a CSS framework is a reasonable thing to reach for. So a
// remote <link rel="stylesheet"> is allowed, while anything that can execute
// is not: a remote <script>, or a <link> that preloads code rather than
// styling it.
foreach ($files as $file) {
    if (!preg_match('/\.(php|html)$/', $file)) {
        continue;
    }

    $contents = file_get_contents($file);

    // Remote <script src="..."> -- never allowed.
    if (preg_match_all(
        '/<script\b[^>]*src\s*=\s*["\']https?:\/\/[^"\']+/i',
        $contents,
        $matches
    )) {
        foreach ($matches[0] as $match) {
            problem(
                $problems,
                'No dependencies',
                relative_to($file, $project_root),
                'Loads third-party JavaScript from the web: '
                    . trim(substr($match, 0, 80))
            );
        }
    }

    // Remote <link href="..."> -- allowed, but only as a plain stylesheet.
    if (preg_match_all('/<link\b[^>]*>/i', $contents, $matches)) {
        foreach ($matches[0] as $tag) {
            // Local stylesheets were never in question.
            if (!preg_match('/href\s*=\s*["\']https?:\/\//i', $tag)) {
                continue;
            }

            $is_stylesheet = preg_match(
                '/\brel\s*=\s*["\']?stylesheet\b/i',
                $tag
            ) === 1;

            // rel="modulepreload" and as="script"/"worker" fetch code that
            // runs later. A stylesheet link they are not.
            $preloads_code = preg_match(
                '/\brel\s*=\s*["\']?modulepreload\b/i',
                $tag
            ) === 1 || preg_match(
                '/\bas\s*=\s*["\']?(?:script|worker)\b/i',
                $tag
            ) === 1;

            if ($is_stylesheet && !$preloads_code) {
                continue;
            }

            problem(
                $problems,
                'No dependencies',
                relative_to($file, $project_root),
                'Loads third-party code from the web: '
                    . trim(substr($tag, 0, 80))
            );
        }
    }
}


// ---------------------------------------------------------------------------
// Rule 2: the framework core is not modified.
// ---------------------------------------------------------------------------

$allowed_core_files = ['Router.php', 'DatabaseHelper.php'];

foreach (glob("$project_root/www/core/*") as $core_file) {
    if (!in_array(basename($core_file), $allowed_core_files, true)) {
        problem(
            $problems,
            'Core untouched',
            relative_to($core_file, $project_root),
            'New files do not belong in www/core/. Framework code lives '
                . 'here; your code goes in controllers, views, or '
                . 'DatabaseQueries.'
        );
    }
}


// ---------------------------------------------------------------------------
// Rule 3: all database access goes through DatabaseHelper::run().
// ---------------------------------------------------------------------------

foreach ($files as $file) {
    $relative = relative_to($file, $project_root);

    if (!str_ends_with($file, '.php')) {
        continue;
    }

    // The two places PDO is legitimately used directly: the helper every
    // query goes through, and the script that builds the database file in
    // the first place (which can't use the helper -- there's nothing to
    // connect to yet).
    if (
        $relative === 'www/core/DatabaseHelper.php'
        || $relative === 'database/build.php'
    ) {
        continue;
    }

    $contents = file_get_contents($file);

    if (preg_match('/\bnew\s+PDO\b/', $contents)) {
        problem(
            $problems,
            'Queries via run()',
            $relative,
            'Creates its own PDO connection. Use DatabaseHelper::run() '
                . 'instead.'
        );
    }

    if (preg_match('/\b(mysqli|mysql_connect|pg_connect)\b/', $contents)) {
        problem(
            $problems,
            'Queries via run()',
            $relative,
            'This project uses SQLite through DatabaseHelper, nothing else.'
        );
    }
}


// ---------------------------------------------------------------------------
// Rule 4: values echoed into views are escaped with e().
//
// This is a heuristic, not a proof. It looks at short-echo tags and flags any
// whose expression doesn't start with a function call -- which is what an
// unescaped variable looks like.
// ---------------------------------------------------------------------------

foreach ($files as $file) {
    if (!preg_match('#www/views/#', str_replace('\\', '/', $file))) {
        continue;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES);

    foreach ($lines as $index => $line) {
        if (!preg_match_all('/<\?=\s*(.+?)\s*\?>/', $line, $matches)) {
            continue;
        }

        foreach ($matches[1] as $expression) {
            // Anything wrapped in a function call is assumed deliberate:
            // e(...), number_format(...), json_encode(...), and so on.
            if (preg_match('/^[a-z_][a-z0-9_]*\s*\(/i', $expression)) {
                continue;
            }

            problem(
                $problems,
                'Escaped output',
                $relative = relative_to($file, $project_root)
                    . ':' . ($index + 1),
                "Unescaped output: <?= $expression ?>. "
                    . 'Wrap it: <?= e(' . $expression . ') ?>'
            );
        }
    }
}


// ---------------------------------------------------------------------------
// Rule 5: controllers render views with view(), not a bare require.
//
// view() roots the path at views/, keeps the view out of the controller's
// scope, and makes the data a controller hands a view explicit. A controller
// that require()s a view file itself sidesteps all three. There is one
// sanctioned way to render a page -- see "Rendering a view" in the README.
// ---------------------------------------------------------------------------

foreach ($files as $file) {
    $relative = relative_to($file, $project_root);

    if (!preg_match('#^www/controllers/.+\.php$#', str_replace('\\', '/', $relative))) {
        continue;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES);

    foreach ($lines as $index => $line) {
        // A require/include that pulls in a view -- anything with "views/" in
        // it. Data helpers and query classes don't match, so those are left
        // alone; only rendering-by-require is flagged.
        if (preg_match(
            '/\b(require|require_once|include|include_once)\b.*\bviews\//',
            $line
        )) {
            problem(
                $problems,
                'Render with view()',
                $relative . ':' . ($index + 1),
                "A controller requires a view directly. Render it with "
                    . "view('...') instead -- see 'Rendering a view' in "
                    . 'README.md.'
            );
        }
    }
}


// ---------------------------------------------------------------------------
// Rule 6: never hand view() a raw request superglobal as its data.
//
// view() extract()s the array it's given, turning keys into variables. When
// the keys are literals a controller wrote, that's fine; when they come from
// a request -- view('page', $_GET) -- the request chooses what variables show
// up in your view. Passing a superglobal straight through is the one way to
// turn view() into a hole, so it's called out by name.
//
// Heuristic: flags a bare request superglobal as an argument to view(). A
// single element like $_GET['q'] used as a VALUE inside an array you built
// (['q' => $_GET['q']]) is safe and is deliberately not flagged.
// ---------------------------------------------------------------------------

foreach ($files as $file) {
    $relative = relative_to($file, $project_root);

    if (!preg_match(
        '#^www/(controllers|views)/.+\.php$#',
        str_replace('\\', '/', $relative)
    )) {
        continue;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES);

    foreach ($lines as $index => $line) {
        // The (?!\s*\[) is what tells "the whole array" ($_GET) apart from
        // "one value out of it" ($_GET['q']) -- only the former is flagged.
        if (preg_match(
            '/\bview\s*\(.*,\s*\$_(GET|POST|REQUEST|FILES|COOKIE)\b(?!\s*\[)/',
            $line
        )) {
            problem(
                $problems,
                'No superglobals to view()',
                $relative . ':' . ($index + 1),
                'Passes a request superglobal straight to view(), which lets '
                    . 'the request choose what variables your view sees. Build '
                    . "the array yourself: view('...', ['key' => \$value])."
            );
        }
    }
}


// ---------------------------------------------------------------------------
// Report.
// ---------------------------------------------------------------------------

if (empty($problems)) {
    echo "Framework check passed. Checked " . count($files) . " files.\n";
    exit(0);
}

echo "\nFramework check FAILED -- " . count($problems) . " problem(s).\n";
echo "See the 'The rules' section of README.md.\n\n";

$by_rule = [];

foreach ($problems as $problem) {
    $by_rule[$problem['rule']][] = $problem;
}

foreach ($by_rule as $rule => $rule_problems) {
    echo "  $rule\n";

    foreach ($rule_problems as $problem) {
        echo "    " . $problem['where'] . "\n";
        echo "      " . $problem['detail'] . "\n";
    }

    echo "\n";
}

exit(1);
