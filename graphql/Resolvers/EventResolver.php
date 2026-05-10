<?php
namespace HemaScorecard\GraphQL\Resolvers;

class EventResolver {

    public static function isMetaEvent(array $event, array $args, array $context): bool {
        return (bool)($event['isMetaEvent'] ?? false);
    }

    public static function startDate(array $event, array $args, array $context): ?string {
        return $event['eventStartDate'] ?? null;
    }

    public static function endDate(array $event, array $args, array $context): ?string {
        return $event['eventEndDate'] ?? null;
    }

    public static function tournaments(array $event, array $args, array $context): array {
        $data = getTournamentsFull((int)$event['eventID']);
        $rows = [];
        foreach ((array) $data as $id => $row) {
            $rows[] = ['tournamentID' => $id] + (array) $row;
        }
        return $rows;
    }

    public static function roster(array $event, array $args, array $context): array {
        $eventID = (int)$event['eventID'];
        $sql     = "SELECT eventRoster.*
                    FROM eventRoster
                    WHERE eventID = {$eventID}";
        return (array) mysqlQuery($sql, ASSOC);
    }
}
