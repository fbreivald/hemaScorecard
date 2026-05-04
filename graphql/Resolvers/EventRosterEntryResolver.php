<?php
namespace HemaScorecard\GraphQL\Resolvers;

class EventRosterEntryResolver {

    public static function personID(array $entry, array $args, array $context): string {
        return (string)$entry['systemRosterID'];
    }

    public static function isTeam(array $entry, array $args, array $context): bool {
        return (bool)($entry['isTeam'] ?? false);
    }

    public static function checkedIn(array $entry, array $args, array $context): bool {
        return (bool)($entry['eventCheckIn'] ?? false);
    }

    public static function waiverSigned(array $entry, array $args, array $context): bool {
        return (bool)($entry['eventWaiver'] ?? false);
    }

    public static function school(array $entry, array $args, array $context): ?array {
        $schoolID = $entry['schoolID'] ?? null;
        return $schoolID ? getSchoolInfo((int)$schoolID) ?: null : null;
    }

    public static function tournamentEntries(array $entry, array $args, array $context): array {
        $rosterID = (int)$entry['rosterID'];
        $sql      = "SELECT * FROM eventTournamentRoster WHERE rosterID = {$rosterID}";
        return (array) mysqlQuery($sql, ASSOC);
    }
}
