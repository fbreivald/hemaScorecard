<?php
namespace HemaScorecard\GraphQL\Resolvers;

class MatchResolver {

    public static function fighter1(array $match, array $args, array $context): ?array {
        return self::rosterEntry($match['fighter1ID'] ?? null);
    }

    public static function fighter2(array $match, array $args, array $context): ?array {
        return self::rosterEntry($match['fighter2ID'] ?? null);
    }

    public static function fighter1Score(array $match, array $args, array $context): ?float {
        return (float)$match['fighter1score'] ?? null;
    }

    public static function fighter2Score(array $match, array $args, array $context): ?float {
        return (float)$match['fighter2score'] ?? null;
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

    public static function matchTimeSeconds(array $match, array $args, array $context): bool {
        return (bool)($match['matchTime'] ?? false);
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

    public static function result(array $match, array $args, array $context): ?string {
        $matchID = (int)$match['matchID'];
        $sql     = "SELECT exchangeType FROM eventExchanges
                    WHERE matchID = {$matchID}
                      AND exchangeType IN ('winner', 'tie')
                    ORDER BY FIELD(exchangeType, 'winner', 'tie')
                    LIMIT 1";
        $type = mysqlQuery($sql, SINGLE, 'exchangeType');
        if ($type === 'winner') return 'win';
        if ($type === 'tie')    return 'tie';
        return null;
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
                INNER JOIN eventRoster ON eventTournamentRoster.rosterID = eventRoster.systemRosterID
                WHERE eventRoster.rosterID = {$rosterID}
                LIMIT 1";
        return mysqlQuery($sql, SINGLE) ?: null;
    }
}
