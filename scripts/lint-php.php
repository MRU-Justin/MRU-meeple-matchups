<?php

/**
 * Syntax-checks every PHP file in the project, using PHP's own `-l` flag.
 *
 * Run it with `npm run lint:php`. A syntax error otherwise shows up as a
 * blank white page in the browser, which is a miserable thing to debug.
 *
 * Exits 0 if everything parses, 1 if anything doesn't.
 */

$project_root = dirname(__DIR__);

$skip = ['.git', 'node_modules', 'vendor', 'logs', '.claude'];

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($project_root, FilesystemIterator::SKIP_DOTS),
        function ($file) use ($skip) {
            return !in_array($file->getFilename(), $skip, true);
        }
    )
);

$failures = [];
$checked = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $checked++;

    $output = [];
    $exit_code = 0;

    exec(
        'php -l ' . escapeshellarg($path) . ' 2>&1',
        $output,
        $exit_code
    );

    if ($exit_code !== 0) {
        $relative = str_replace('\\', '/', substr($path, strlen($project_root) + 1));
        $failures[$relative] = implode("\n", $output);
    }
}

if (empty($failures)) {
    echo "No syntax errors. Checked $checked PHP files.\n";
    exit(0);
}

echo "\nSyntax errors in " . count($failures) . " file(s):\n\n";

foreach ($failures as $relative => $message) {
    echo "  $relative\n";

    foreach (explode("\n", $message) as $line) {
        if (trim($line) !== '') {
            echo "    " . trim($line) . "\n";
        }
    }

    echo "\n";
}

exit(1);
