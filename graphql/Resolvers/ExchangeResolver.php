<?php
namespace HemaScorecard\GraphQL\Resolvers;

class ExchangeResolver {

    public static function isDouble(array $exchange, array $args, array $context): bool {
        return in_array($exchange['exchangeType'] ?? '', ['double', 'doubleOut'], true);
    }

    public static function scorer(array $exchange, array $args, array $context): ?array {
        return self::tournamentRosterEntry($exchange['scoringID'] ?? null);
    }

    public static function receiver(array $exchange, array $args, array $context): ?array {
        return self::tournamentRosterEntry($exchange['receivingID'] ?? null);
    }

    public static function prefix(array $exchange, array $args, array $context): ?string {
        $attack = self::attack($exchange['refPrefix']);
        return $attack['attackCode'] ?? null;
    }

    public static function target(array $exchange, array $args, array $context): ?string {
        $attack = self::attack($exchange['refTarget']);
        return $attack['attackCode'] ?? null;
    }

    public static function type(array $exchange, array $args, array $context): ?string {
        $attack = self::attack($exchange['refType']);
        return $attack['attackCode'] ?? null;
    }
    
    private static function attack(?int $attackID): ?array {
        if (!$attackID) {
            return null;
        }
        $sql = "SELECT systemAttacks.*
                FROM systemAttacks
                WHERE systemAttacks.attackID = {$attackID}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }

    private static function tournamentRosterEntry(?int $rosterID): ?array {
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
