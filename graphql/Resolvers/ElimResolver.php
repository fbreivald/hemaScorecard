<?php
namespace HemaScorecard\GraphQL\Resolvers;

class ElimResolver {

    public static function isComplete(array $elim, array $args, array $context): bool {
        return (bool)($elim['groupComplete'] ?? false);
    }

    public static function matches(array $elim, array $args, array $context): array {
        $groupID = (int)$elim['groupID'];
        $sql     = "SELECT * FROM eventMatches
                    WHERE groupID = {$groupID}
                    ORDER BY matchNumber";
        return (array) mysqlQuery($sql, ASSOC);
    }

    public static function roster(array $elim, array $args, array $context): array {
        $groupID = (int)$elim['groupID'];
        $sql     = "SELECT eventGroupRoster.*, eventRoster.systemRosterID
                    FROM eventGroupRoster
                    INNER JOIN eventRoster USING(rosterID)
                    WHERE eventGroupRoster.groupID = {$groupID}";
        return (array) mysqlQuery($sql, ASSOC);
    }
}
