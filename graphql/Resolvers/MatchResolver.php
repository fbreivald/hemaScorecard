<?php
namespace HemaScorecard\GraphQL\Resolvers;

class MatchResolver {

    public static function fighter1(array $match, array $args, array $context): ?array {
        return self::rosterEntry($match['fighter1ID'] ?? null);
    }

    public static function fighter2(array $match, array $args, array $context): ?array {
        return self::rosterEntry($match['fighter2ID'] ?? null);
    }

    public static function winner(array $match, array $args, array $context): ?array {
        return self::rosterEntry($match['winnerID'] ?? null);
    }

    public static function isComplete(array $match, array $args, array $context): bool {
        return (bool)($match['matchComplete'] ?? false);
    }

    public static function isIgnored(array $match, array $args, array $context): bool {
        return (bool)($match['ignoreMatch'] ?? false);
    }

    public static function tournamentID(array $match, array $args, array $context): ?int {
        if (!empty($match['tournamentID'])) {
            return (int)$match['tournamentID'];
        }
        $groupID = (int)($match['groupID'] ?? 0);
        if (!$groupID) {
            return null;
        }
        $sql = "SELECT tournamentID FROM eventGroups WHERE groupID = {$groupID}";
        return (int)(mysqlQuery($sql, SINGLE, 'tournamentID') ?? 0) ?: null;
    }

    public static function exchanges(array $match, array $args, array $context): array {
        $matchID = (int)$match['matchID'];
        $sql     = "SELECT * FROM eventExchanges WHERE matchID = {$matchID} ORDER BY exchangeNumber";
        return (array) mysqlQuery($sql, ASSOC);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private static function rosterEntry(?int $rosterID): ?array {
        if (!$rosterID) {
            return null;
        }
        $sql = "SELECT eventTournamentRoster.*, eventRoster.systemRosterID
                FROM eventTournamentRoster
                INNER JOIN eventRoster USING(rosterID)
                WHERE eventTournamentRoster.rosterID = {$rosterID}
                LIMIT 1";
        return mysqlQuery($sql, SINGLE) ?: null;
    }
}
