<?php
namespace HemaScorecard\GraphQL\Resolvers;

trait ExchangeCommonFields {

    public static function exchangeTime(array $exchange, array $args, array $context): int {
        return (int)($exchange['exchangeTime'] ?? 0);
    }

    public static function score(array $exchange, array $args, array $context): int {
        return (int)($exchange['scoreValue'] ?? 0);
    }

    public static function match(array $exchange, array $args, array $context): ?array {
        $matchID = (int)($exchange['matchID'] ?? 0);
        if (!$matchID) {
            return null;
        }
        return mysqlQuery("SELECT * FROM eventMatches WHERE matchID = {$matchID}", SINGLE) ?: null;
    }

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

    private static function attackObject(array $exchange): ?array {
        if (!($exchange['refPrefix'] ?? null) && !($exchange['refTarget'] ?? null) && !($exchange['refType'] ?? null)) {
            return null;
        }
        return [
            'prefix' => self::attackCode((int)($exchange['refPrefix'] ?? 0)),
            'target' => self::attackCode((int)($exchange['refTarget'] ?? 0)),
            'type'   => self::attackCode((int)($exchange['refType'] ?? 0)),
        ];
    }

    private static function attackCode(int $attackID): ?string {
        if (!$attackID) {
            return null;
        }
        return mysqlQuery(
            "SELECT attackCode FROM systemAttacks WHERE attackID = {$attackID}",
            SINGLE,
            'attackCode'
        ) ?: null;
    }
}
