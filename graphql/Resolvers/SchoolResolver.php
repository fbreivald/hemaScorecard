<?php
namespace HemaScorecard\GraphQL\Resolvers;

// systemSchools columns are prefixed with "school"; map them to schema field names.
class SchoolResolver {

    public static function fullName(array $school, array $args, array $context): ?string {
        return $school['schoolFullName'] ?? null;
    }

    public static function shortName(array $school, array $args, array $context): ?string {
        return $school['schoolShortName'] ?? null;
    }

    public static function abbreviation(array $school, array $args, array $context): ?string {
        return $school['schoolAbbreviation'] ?? null;
    }

    public static function branch(array $school, array $args, array $context): ?string {
        return $school['schoolBranch'] ?? null;
    }

    public static function city(array $school, array $args, array $context): ?string {
        return $school['schoolCity'] ?? null;
    }

    public static function province(array $school, array $args, array $context): ?string {
        return $school['schoolProvince'] ?? null;
    }

    public static function country(array $school, array $args, array $context): ?string {
        return $school['countryName'] ?? null;
    }

    public static function members(array $school, array $args, array $context): array {
        $schoolID = (int)$school['schoolID'];
        $sql      = "SELECT * FROM systemRoster WHERE schoolID = {$schoolID} ORDER BY lastName, firstName";
        return (array) mysqlQuery($sql, ASSOC);
    }
}
