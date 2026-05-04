<?php
namespace HemaScorecard\GraphQL\Resolvers;

class PoolStandingResolver {

    public static function person(array $standing, array $args, array $context): ?array {
        $systemRosterID = (int)($standing['systemRosterID'] ?? 0);
        if (!$systemRosterID) {
            return null;
        }
        $sql = "SELECT * FROM systemRoster WHERE systemRosterID = {$systemRosterID}";
        return mysqlQuery($sql, SINGLE) ?: null;
    }
}
