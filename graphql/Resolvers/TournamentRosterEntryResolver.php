<?php
namespace HemaScorecard\GraphQL\Resolvers;

class TournamentRosterEntryResolver {

    public static function checkedIn(array $entry, array $args, array $context): bool {
        return (bool)($entry['tournamentCheckIn'] ?? false);
    }

    public static function gearChecked(array $entry, array $args, array $context): bool {
        return (bool)($entry['tournamentGearCheck'] ?? false);
    }

    public static function person(array $entry, array $args, array $context): ?array {
        $tournamentRosterID = (int)($entry['tournamentRosterID'] ?? 0);
        if (!$tournamentRosterID) {
            return null;
        }
        $sql = "SELECT systemRoster.* 
                FROM eventTournamentRoster
                INNER JOIN systemRoster ON systemRoster.systemRosterID = eventTournamentRoster.rosterID
                WHERE tournamentRosterID = {$tournamentRosterID}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }

    public static function school(array $entry, array $args, array $context): ?array {
        $schoolID = $entry['schoolID'] ?? null;
        return $schoolID ? getSchoolInfo((int)$schoolID) ?: null : null;
    }

    public static function tournament(array $entry, array $args, array $context): ?array {
        $tournamentID = (int)($entry['tournamentID'] ?? 0);
        if (!$tournamentID) {
            return null;
        }
        $sql = "SELECT * FROM eventTournaments WHERE tournamentID = {$tournamentID}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }
}
