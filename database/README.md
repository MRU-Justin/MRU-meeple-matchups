# The database

**Your project may not need one.** The template runs perfectly well with no
database at all — nothing here is opened until your code actually asks for
data. If you're not storing anything, you can ignore this whole directory.

## How it works

The database file itself is **not** in version control. What's committed is
the recipe for building it:

| File | What it's for |
| --- | --- |
| `schema.sql` | Your tables. The source of truth for your design. |
| `seed.sql` | Starting data. Optional. |
| `build.php` | Builds the database file from the two above. |
| `app.db` | The database itself. Generated, and gitignored. |

## Building it

```
php database/build.php
```

Run it after any change to `schema.sql`, and any time your data has got into a
state you'd rather back out of. Your previous database is renamed to
`app.db.bak` first, so a mistaken rebuild is recoverable.

The script tells you which tables it created and how many rows each holds, so
a build that silently did nothing useful is visible immediately.

## Why not just commit the .db file?

Three reasons, and the third is the one that will actually bite you:

1. **You can read a text file.** Your schema can be diffed, reviewed, and
   commented on. Nobody can review a binary.
2. **Everyone gets the same data.** A teammate runs `build.php` and has your
   exact rows, rather than whatever their copy happens to contain.
3. **Git cannot merge two `.db` files.** If two people on a project both run
   the app and both commit the database, you get a conflict in a binary file
   that neither of you can resolve, and the usual fix is for somebody to lose
   their work.

## Working in a GUI

Tools like DB Browser for SQLite are useful for *looking* at data. Be careful
about designing in them: changes made through a GUI go into the `.db` file
only, and `schema.sql` won't know about them. The two drift apart, and the
next `build.php` quietly reverts your work.

Keep `schema.sql` as the thing you edit, and treat the `.db` as disposable
output.

## Using a different database

Set `COMP3512_DB`. Both `build.php` and the application read it:

```
# PowerShell
$env:COMP3512_DB="scratch.db"
php database/build.php

# macOS
COMP3512_DB=scratch.db php database/build.php
```

A bare filename is looked for in this directory; a full path is used as-is.
