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
        $systemRosterID = (int)($entry['systemRosterID'] ?? 0);
        if (!$systemRosterID) {
            return null;
        }
        $sql = "SELECT * FROM systemRoster WHERE systemRosterID = {$systemRosterID}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }

    public static function school(array $entry, array $args, array $context): ?array {
        $schoolID = $entry['schoolID'] ?? null;
        return $schoolID ? getSchoolInfo((int)$schoolID) ?: null : null;
    }
}
