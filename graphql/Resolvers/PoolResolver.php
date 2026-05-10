<?php
namespace HemaScorecard\GraphQL\Resolvers;

class PoolResolver {

    public static function isComplete(array $pool, array $args, array $context): bool {
        return (bool)($pool['groupComplete'] ?? false);
    }

    public static function matches(array $pool, array $args, array $context): array {
        $groupID = (int)$pool['groupID'];
        $sql     = "SELECT * FROM eventMatches
                    WHERE groupID = {$groupID}
                    ORDER BY matchNumber";
        return (array) mysqlQuery($sql, ASSOC);
    }

    public static function roster(array $pool, array $args, array $context): array {
        $groupID = (int)$pool['groupID'];
        $sql     = "SELECT eventGroupRoster.*, eventRoster.systemRosterID
                    FROM eventGroupRoster
                    INNER JOIN eventRoster USING(rosterID)
                    WHERE eventGroupRoster.groupID = {$groupID}";
        return (array) mysqlQuery($sql, ASSOC);
    }

    public static function standings(array $pool, array $args, array $context): array {
        $groupID = (int)$pool['groupID'];
        $sql     = "SELECT eventStandings.*, eventRoster.systemRosterID
                    FROM eventStandings
                    INNER JOIN eventRoster USING(rosterID)
                    WHERE eventStandings.groupID = {$groupID}
                    AND eventStandings.groupType = 'pool'
                    ORDER BY rank ASC";
        return (array) mysqlQuery($sql, ASSOC);
    }
}
