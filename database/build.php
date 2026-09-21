<?php

/**
 * Builds the SQLite database file from schema.sql and seed.sql.
 *
 *     php database/build.php
 *
 * Run it whenever you change your schema, and any time you've made a mess of
 * your data and want to start over.
 *
 * The existing database is NOT quietly thrown away -- it's renamed to
 * <name>.bak first, so a mistaken rebuild is recoverable.
 *
 * To build a different database file, set COMP3512_DB, the same variable
 * www/config/database.php reads:
 *
 *     PowerShell:  $env:COMP3512_DB="scratch.db"; php database/build.php
 *     macOS:       COMP3512_DB=scratch.db php database/build.php
 */

$database_dir = __DIR__;

$configured = getenv('COMP3512_DB');

if ($configured === false || $configured === '') {
    $configured = 'app.db';
}

$is_full_path = preg_match('#^([a-zA-Z]:[\\\\/]|/|\\\\)#', $configured) === 1;

$database_path = $is_full_path
    ? $configured
    : "$database_dir/$configured";

$schema_path = "$database_dir/schema.sql";
$seed_path = "$database_dir/seed.sql";

/**
 * Paths built by joining with "/" end up with mixed slashes on Windows, which
 * looks like a mistake when printed. Tidy them for display only.
 */
function shown($path)
{
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}


if (!file_exists($schema_path)) {
    echo "There's no schema.sql in $database_dir.\n";
    echo "That file is what describes your tables -- create it first.\n";
    exit(1);
}

$schema_sql = file_get_contents($schema_path);

// A file of nothing but comments means the student hasn't designed anything
// yet. Building an empty database from it would "succeed" confusingly.
if (!has_statements($schema_sql)) {
    echo "schema.sql doesn't define any tables yet -- it's all comments.\n\n";
    echo "Design your tables in " . shown($schema_path) . " first, then run this again.\n";
    exit(1);
}


// Move any existing database aside rather than destroying it.
if (file_exists($database_path)) {
    $backup_path = $database_path . '.bak';

    if (file_exists($backup_path)) {
        unlink($backup_path);
    }

    rename($database_path, $backup_path);

    echo "Existing database moved to " . basename($backup_path) . "\n";
}


try {
    $connection = new PDO("sqlite:$database_path", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $connection->exec('PRAGMA foreign_keys = ON;');

    $connection->exec($schema_sql);
    echo "Applied schema.sql\n";

    if (file_exists($seed_path)) {
        $seed_sql = file_get_contents($seed_path);

        if (has_statements($seed_sql)) {
            $connection->exec($seed_sql);
            echo "Applied seed.sql\n";
        } else {
            echo "Skipped seed.sql (no data in it yet)\n";
        }
    }
} catch (PDOException $e) {
    echo "\nThe database could not be built:\n\n  " . $e->getMessage() . "\n\n";
    echo "The half-built file is still at " . shown($database_path) . " so you can look at\n";
    echo "it; your previous database, if there was one, is alongside it as\n";
    echo basename($database_path) . ".bak\n";
    exit(1);
}


// Report what actually got built, so a silent success can't hide a mistake.
$tables = $connection
    ->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);

echo "\nBuilt " . basename($database_path) . "\n";

if (empty($tables)) {
    echo "  (no tables -- check your schema.sql)\n";
    exit(1);
}

foreach ($tables as $table) {
    // Table names come from your own schema file, but quoting them is the
    // right habit regardless.
    $count = $connection
        ->query('SELECT COUNT(*) FROM "' . str_replace('"', '""', $table) . '"')
        ->fetchColumn();

    printf("  %-24s %s row%s\n", $table, $count, $count === 1 ? '' : 's');
}

echo "\n";
exit(0);


/**
 * True if the SQL contains anything other than comments and blank lines.
 */
function has_statements($sql)
{
    $without_block_comments = preg_replace('#/\*.*?\*/#s', '', $sql);

    foreach (explode("\n", $without_block_comments) as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }

        return true;
    }

    return false;
}
