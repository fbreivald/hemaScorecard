<?php
namespace HemaScorecard\GraphQL\Resolvers;

class EventResolver {

    public static function isMetaEvent(array $event, array $args, array $context): bool {
        return (bool)($event['isMetaEvent'] ?? false);
    }

    public static function tournaments(array $event, array $args, array $context): array {
        $data = getTournamentsFull((int)$event['eventID']);
        return array_values((array) $data);
    }

    public static function roster(array $event, array $args, array $context): array {
        $eventID = (int)$event['eventID'];
        $sql     = "SELECT eventRoster.*
                    FROM eventRoster
                    WHERE eventID = {$eventID}";
        return (array) mysqlQuery($sql, ASSOC);
    }
}
