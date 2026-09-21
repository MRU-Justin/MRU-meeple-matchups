<?php

class DatabaseHelper
{

    private $connection;

    public function __construct($config)
    {
        $path = $config['path'];

        // Check the file exists BEFORE trying to open it.
        //
        // Left alone, SQLite treats "this file isn't there" as "make me a new
        // empty database", and connects perfectly happily. That's the worst
        // possible outcome: every query afterwards fails with "no such table",
        // which sends you off inspecting your SQL when the real problem is
        // that you're talking to an empty file.
        // is_file rather than file_exists, so a path that points at a folder
        // by mistake is caught here too.
        if (!is_file($path)) {
            // Tidy the path up before showing it. The raw value contains a
            // "/../" and a mix of slash directions, which is noise in a
            // message whose only job is to tell you where to look.
            $directory = realpath(dirname($path));

            $shown_path = $directory === false
                ? $path
                : $directory . DIRECTORY_SEPARATOR . basename($path);

            throw new Exception(
                "No SQLite database file here:\n\n    $shown_path\n\n"
                    . "SQLite would otherwise create an empty one at that path "
                    . "and carry on, so every query would then fail with "
                    . "'no such table' and point you in the wrong direction.\n\n"
                    . "If your project needs a database, build one:\n"
                    . "    php database/build.php\n\n"
                    . "If it doesn't, then nothing should be constructing "
                    . "DatabaseQueries in the first place.\n\n"
                    . "To use a different database file, set COMP3512_DB -- "
                    . "see www/config/database.php."
            );
        }

        // SQLite does NOT use usernames/passwords!
        $username = null;
        $password = null;

        $dsn = "sqlite:$path";

        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->connection->exec('PRAGMA foreign_keys = ON;'); // SQLite doesn't default to using foreign keys
    }

    public function run($sql, $params = [])
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    public function close_connection()
    {
        $this->connection = null;
    }
}
