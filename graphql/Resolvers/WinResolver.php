<?php
namespace HemaScorecard\GraphQL\Resolvers;

class WinResolver {
    use ExchangeCommonFields;

    public static function winner(array $exchange, array $args, array $context): ?array {
        $rosterID = $exchange['scoringID'] ?? null;
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
