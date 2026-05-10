<?php
namespace HemaScorecard\GraphQL\Resolvers;

class QueryResolver {

    // ── People ────────────────────────────────────────────────────────────────

    public static function person($root, array $args, array $context): ?array {
        $id  = (int)$args['personID'];
        $sql = "SELECT * FROM systemRoster WHERE systemRosterID = {$id}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }

    public static function people($root, array $args, array $context): array {
        $where = [];

        if (!empty($args['schoolID'])) {
            $schoolID = (int)$args['schoolID'];
            $where[] = "schoolID = {$schoolID}";
        }
        if (!empty($args['firstName'])) {
            $firstName  = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $args['firstName']);
            $where[] = "firstName = '{$firstName}'";
        }
        if (!empty($args['lastName'])) {
            $lastName = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $args['lastName']);
            $where[] = "lastName = '{$lastName}'";
        }

        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql    = "SELECT * FROM systemRoster {$clause} ORDER BY lastName, firstName";
        return (array) mysqlQuery($sql, ASSOC);
    }

    // ── Events ────────────────────────────────────────────────────────────────

    public static function event($root, array $args, array $context): ?array {
        $id  = (int)$args['eventID'];
        $sql = "SELECT * FROM systemEvents WHERE eventID = {$id}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }

    public static function events($root, array $args, array $context): array {
        $archived = isset($args['open']) && $args['open'] === false ? 1 : 0;
        $sql = "SELECT * FROM systemEvents
                WHERE isArchived = {$archived}
                ORDER BY eventName ASC";
        return (array) mysqlQuery($sql, ASSOC);
    }

    // ── Schools ───────────────────────────────────────────────────────────────

    public static function school($root, array $args, array $context): ?array {
        return getSchoolInfo((int)$args['schoolID']) ?: null;
    }

    public static function schools($root, array $args, array $context): array {
        if (!empty($args['country'])) {
            $country = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $args['country']);
            $sql     = "SELECT * FROM systemSchools
                        INNER JOIN systemCountries USING(countryIso2)
                        WHERE countryName = '{$country}'
                        ORDER BY schoolShortName";
            return (array) mysqlQuery($sql, ASSOC);
        }
        $sql = "SELECT systemSchools.*, countryName
                FROM systemSchools
                INNER JOIN systemCountries USING(countryIso2)
                ORDER BY schoolShortName";
        return (array) mysqlQuery($sql, ASSOC);
    }

    // ── Tournaments ───────────────────────────────────────────────────────────

    public static function tournament($root, array $args, array $context): ?array {
        $id  = (int)$args['tournamentID'];
        $sql = "SELECT * FROM eventTournaments WHERE tournamentID = {$id}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }

    public static function tournaments($root, array $args, array $context): array {
        $data = getTournamentsFull((int)$args['eventID']);
        $rows = [];
        foreach ((array) $data as $id => $row) {
            $rows[] = ['tournamentID' => $id] + (array) $row;
        }
        return $rows;
    }

    // ── Matches ───────────────────────────────────────────────────────────────

    public static function match($root, array $args, array $context): ?array {
        return getMatchInfo((int)$args['matchID']) ?: null;
    }

    public static function matches($root, array $args, array $context): array {
        $where = [];
        if (!empty($args['tournamentID'])) {
            $tID     = (int)$args['tournamentID'];
            $where[] = "eventGroups.tournamentID = {$tID}";
        }
        if (!empty($args['groupID'])) {
            $gID     = (int)$args['groupID'];
            $where[] = "eventMatches.groupID = {$gID}";
        }
        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql    = "SELECT eventMatches.*
                   FROM eventMatches
                   INNER JOIN eventGroups USING(groupID)
                   {$clause}
                   ORDER BY matchNumber";
        return (array) mysqlQuery($sql, ASSOC);
    }
}
