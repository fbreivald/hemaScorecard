<?php
namespace HemaScorecard\GraphQL\Resolvers;

class TournamentResolver {

    public static function name(array $tournament, array $args, array $context): string {
        return getTournamentName((int)$tournament['tournamentID']);
    }

    public static function weapon(array $tournament, array $args, array $context): ?string {
        return self::typeName($tournament['tournamentWeaponID'] ?? null);
    }

    public static function prefix(array $tournament, array $args, array $context): ?string {
        return self::typeName($tournament['tournamentPrefixID'] ?? null);
    }

    public static function gender(array $tournament, array $args, array $context): ?string {
        return self::typeName($tournament['tournamentGenderID'] ?? null);
    }

    public static function material(array $tournament, array $args, array $context): ?string {
        return self::typeName($tournament['tournamentMaterialID'] ?? null);
    }

    public static function suffix(array $tournament, array $args, array $context): ?string {
        return self::typeName($tournament['tournamentSuffixID'] ?? null);
    }

    public static function numParticipants(array $tournament, array $args, array $context): int {
        return (int)($tournament['numFighters'] ?? $tournament['numParticipants'] ?? 0);
    }

    public static function roster(array $tournament, array $args, array $context): array {
        $data = getTournamentRoster((int)$tournament['tournamentID']);
        return array_values((array) $data);
    }

    public static function groups(array $tournament, array $args, array $context): array {
        $tID = (int)$tournament['tournamentID'];
        $sql = "SELECT * FROM eventGroups WHERE tournamentID = {$tID} ORDER BY groupSet, groupNumber";
        return (array) mysqlQuery($sql, ASSOC);
    }

    public static function pools(array $tournament, array $args, array $context): array {
        $tID      = (int)$tournament['tournamentID'];
        $setClause = isset($args['groupSet'])
            ? "AND groupSet = " . (int)$args['groupSet']
            : '';
        $sql = "SELECT groupID, tournamentID, groupName, groupNumber, groupSet,
                       groupComplete, numFighters, locationID
                FROM eventGroups
                WHERE tournamentID = {$tID}
                AND groupType = 'pool'
                {$setClause}
                ORDER BY groupSet, groupNumber";
        return (array) mysqlQuery($sql, ASSOC);
    }

    public static function matches(array $tournament, array $args, array $context): array {
        $tID = (int)$tournament['tournamentID'];
        $sql = "SELECT eventMatches.*
                FROM eventMatches
                INNER JOIN eventGroups USING(groupID)
                WHERE eventGroups.tournamentID = {$tID}
                ORDER BY matchNumber";
        return (array) mysqlQuery($sql, ASSOC);
    }

    // ── Scalar bool fields (tinyint → bool) ───────────────────────────────────

    public static function allowTies(array $t, array $args, array $context): bool {
        return (bool)($t['allowTies'] ?? false);
    }

    public static function isFinalized(array $t, array $args, array $context): bool {
        return (bool)($t['isFinalized'] ?? false);
    }

    public static function isPrivate(array $t, array $args, array $context): bool {
        return (bool)($t['isPrivate'] ?? false);
    }

    public static function isTeams(array $t, array $args, array $context): bool {
        return (bool)($t['isTeams'] ?? false);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private static function typeName(?int $typeID): ?string {
        if (!$typeID) {
            return null;
        }
        $sql = "SELECT tournamentType FROM systemTournaments WHERE tournamentTypeID = {$typeID}";
        return mysqlQuery($sql, SINGLE, 'tournamentType') ?: null;
    }
}
