-- Your database schema: the tables your project is built on.
--
-- This file is the source of truth. Edit it, then run:
--
--     php database/build.php
--
-- ...which rebuilds the database file from scratch. Don't design your tables
-- in a GUI and hope this file keeps up -- it won't, and the two will drift
-- apart silently.
--
-- ---------------------------------------------------------------------------
-- GIVEN TABLES -- games and venues
--
-- These two are provided for you, along with their data in seed.sql. Leave
-- them as they are; your own tables go at the bottom of this file.
-- ---------------------------------------------------------------------------

CREATE TABLE games (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    title             TEXT    NOT NULL COLLATE NOCASE,
    year_published    INTEGER NOT NULL,
    min_players       INTEGER NOT NULL,
    max_players       INTEGER NOT NULL,
    play_time_minutes INTEGER NOT NULL
);

-- COLLATE NOCASE on title is what lets an index actually be used for the
-- case-insensitive prefix search your title-search endpoint needs.

-- **UNIQUE WHERE TO USE**
CREATE INDEX idx_games_title ON games (title);

CREATE TABLE venues (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    address     TEXT NOT NULL,
    city        TEXT NOT NULL,
    province    TEXT NOT NULL,
    postal_code TEXT NOT NULL,
    latitude    REAL NOT NULL,
    longitude   REAL NOT NULL,
    phone       TEXT NOT NULL
);

CREATE INDEX idx_venues_province ON venues (province);


-- ===========================================================================
-- YOUR TABLES
-- ===========================================================================
--
-- Everything the requirement docs ask for that isn't games or venues goes
-- below, and you will be adding to it all term.
--
--   * Use "id INTEGER PRIMARY KEY AUTOINCREMENT", exactly as above. INTEGER
--     must be spelled out -- INT does not behave the same way in SQLite.
--
--   * Declare foreign keys with REFERENCES, as in the example that shipped
--     with this template. The framework turns foreign key enforcement on;
--     SQLite does not do so by default.
--
--   * SQLite has no date type. Store dates as TEXT in ISO-8601 format
--     ('2026-11-14'), which sorts correctly and works with SQLite's date
--     functions. Don't invent your own format.
--
--   * SQLite is loose about types -- a column declared INTEGER will accept
--     the text 'banana'. That is not permission to be sloppy. Declare what
--     you mean; I read this file in December to understand your design.
--
-- ===========================================================================

CREATE TABLE administrators (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    email             TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    digest            TEXT    NOT NULL,
    last_login_at     TEXT,
    previous_login_at TEXT
);

CREATE TABLE members (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name        TEXT    NOT NULL,
    last_name         TEXT    NOT NULL,
    email             TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    cell_phone        TEXT,
    plan_type         TEXT NOT NULL CHECK (plan_type IN ('standard', 'premium')),
    preferred_contact TEXT NOT NULL CHECK (preferred_contact IN ('email', 'cell_phone')),
    enrolled_on       TEXT NOT NULL
);

CREATE TABLE plays (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    logged_by_member_id INTEGER NOT NULL REFERENCES members (id),
    game_id             INTEGER NOT NULL REFERENCES games (id),
    venue_id            INTEGER NOT NULL REFERENCES venues (id),
    played_on           TEXT    NOT NULL
);

CREATE TABLE play_players (
    play_id           INTEGER NOT NULL REFERENCES plays (id),
    member_id         INTEGER NOT NULL REFERENCES members (id),
    score             INTEGER NOT NULL,
    PRIMARY KEY (play_id, member_id)
);

CREATE TABLE venue_featured_games (
    venue_id          INTEGER NOT NULL REFERENCES venues (id),
    game_id           INTEGER NOT NULL REFERENCES games (id),
    PRIMARY KEY (venue_id, game_id)
);

CREATE TABLE member_preferred_venues (
    member_id         INTEGER NOT NULL REFERENCES members (id),
    venue_id          INTEGER NOT NULL REFERENCES venues (id),
    PRIMARY KEY (member_id, venue_id)
);

CREATE TABLE wishlist_items (
    member_id         INTEGER NOT NULL REFERENCES members (id),
    game_id           INTEGER NOT NULL REFERENCES games (id),
    PRIMARY KEY (member_id, game_id)
);

CREATE TABLE connections (
    member_id         INTEGER NOT NULL REFERENCES members (id),
    connected_member_id INTEGER NOT NULL REFERENCES members (id),
    PRIMARY KEY (member_id, connected_member_id),
    CHECK (member_id <> connected_member_id)
);
