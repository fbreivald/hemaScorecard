# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

HEMA Scorecard is a PHP tournament management system for Historical European Martial Arts competitions. It handles event creation, participant registration, match scoring, bracket progression (pools → rounds → finals), logistics/staffing, and statistics generation.

## Running Locally

```bash
docker-compose up
```

App runs at `http://localhost:8000`. MySQL credentials are in `includes/database.php`. There is no build step — PHP is interpreted directly. There are no tests or linting tools.

XDebug is enabled in Docker on port 9000; `.vscode/launch.json` has debug configurations.

## Architecture

### Request Lifecycle

Every page is a `.php` file in the project root. Pages follow this structure:

1. Include `includes/config.php` — establishes DB connection, initializes session, defines constants
2. Include `includes/header.php` — HTML head, navigation bar, Foundation CSS
3. Call functions from `includes/functions/` to read data and render HTML
4. Include `includes/footer.php` — jQuery/JS vendor includes, custom scripts

### POST Form Handling

All form submissions use a hidden `formName` field. `includes/functions/doPOST.php` (2,281 lines) is a large switch statement that dispatches on `formName`, calls write/scoring functions, then redirects back to the originating page. This is included via `config.php`.

```
Form POST → config.php → doPOST.php (switch formName)
  ├─ DB_write_functions.php  — INSERT/UPDATE/DELETE
  ├─ scoring_functions.php   — recalculate standings, ratings
  ├─ data_handling_functions.php — validation
  └─ redirect back to page
```

### AJAX

`includes/functions/AJAX.php` handles all async requests (called via jQuery from `includes/scripts/`). It switches on a `request` POST parameter and returns JSON or HTML fragments.

### Key Function Files

| File | Purpose |
|------|---------|
| `includes/functions/DB_read_functions.php` | All SELECT queries (10K lines) |
| `includes/functions/DB_write_functions.php` | All INSERT/UPDATE/DELETE (10K lines) |
| `includes/functions/display_functions.php` | HTML rendering helpers |
| `includes/functions/scoring_functions.php` | Match scoring, ratings, statistics |
| `includes/functions/doPOST.php` | POST form router |
| `includes/functions/AJAX.php` | AJAX endpoints |
| `includes/functions/mysql_lib.php` | `mysqlQuery($sql, $mode)` wrapper |

### Database Access Pattern

All queries go through `mysqlQuery($sql, $mode)`. Common modes:
- `SEND` — execute with no return
- `SINGLE` — return one row as associative array
- `ASSOC` — return all rows as associative array
- `INDEX` — return rows keyed by first column
- `NUM_ROWS` — return count

Raw SQL is built with string concatenation using PHP constants for table/column names defined in `config.php`.

### Session & Access Control

`initializeSession()` in `config.php` sets up `$_SESSION`. Key session variables: `userName`, `eventID`, `tournamentID`, `matchID`, `formatID`. Role-based access is checked per-page via an `ALLOW[]` array populated during session init.

### Database Schema

Full schema is in `includes/Tables - ScorecardV9.sql` (50+ tables). The main entity hierarchy is: **Event → Tournament → Division → Pool/Round → Match → Score**.

### Frontend Stack

- **CSS**: Foundation 6 (files in `includes/foundation/`)
- **JS**: jQuery, jQuery UI, DataTables, TinyMCE (rich text), Google Charts
- **Custom JS**: `includes/scripts/` (~12 files, loaded in `footer.php`)

### Page Categories

Pages in the root follow naming conventions:
- `admin*.php` — manage event/tournament data (requires auth)
- `score*.php` — live match scoring interfaces
- `info*.php` — public-facing informational pages
- `participants*.php` — check-in, schedules
- `pool*.php` / `round*.php` — tournament bracket progression
- `stats*.php` — analytics and reports
- `logistics*.php` — staffing and scheduling
- `video*.php` — livestream integration
