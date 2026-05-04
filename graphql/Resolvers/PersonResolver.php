<?php
namespace HemaScorecard\GraphQL\Resolvers;

// Resolves fields on Person that don't map 1:1 to systemRoster column names,
// and nested types (school, eventEntry). All other fields fall through to
// the default array-key resolver in SchemaBuilder.

class PersonResolver {

    // Column name differs: rosterCountry → country
    public static function country(array $person, array $args, array $context): ?string {
        return $person['rosterCountry'] ?? null;
    }

    public static function province(array $person, array $args, array $context): ?string {
        return $person['rosterProvince'] ?? null;
    }

    public static function city(array $person, array $args, array $context): ?string {
        return $person['rosterCity'] ?? null;
    }

    public static function email(array $person, array $args, array $context): ?string {
        return $person['eMail'] ?? null;
    }

    public static function personID(array $person, array $args, array $context): string {
        return (string)$person['systemRosterID'];
    }

    // Column name differs: HemaRatingsID → hemaRatingsID
    public static function hemaRatingsID(array $person, array $args, array $context): ?int {
        $val = $person['HemaRatingsID'] ?? null;
        return $val !== null ? (int)$val : null;
    }

    // Nested type
    public static function school(array $person, array $args, array $context): ?array {
        $schoolID = $person['schoolID'] ?? null;
        if (!$schoolID) {
            return null;
        }
        return getSchoolInfo((int)$schoolID) ?: null;
    }

    // eventEntry requires an eventID argument — fetch the eventRoster row
    public static function eventEntry(array $person, array $args, array $context): ?array {
        $systemRosterID = (int)$person['systemRosterID'];
        $eventID        = (int)$args['eventID'];

        $sql = "SELECT eventRoster.*, systemEvents.eventID
                FROM eventRoster
                INNER JOIN systemEvents USING(eventID)
                WHERE eventRoster.systemRosterID = {$systemRosterID}
                  AND eventRoster.eventID = {$eventID}";
        $row = mysqlQuery($sql, SINGLE);
        return $row ?: null;
    }
}
