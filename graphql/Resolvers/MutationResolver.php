<?php
namespace HemaScorecard\GraphQL\Resolvers;

class MutationResolver {

    public static function createPerson($root, array $args, array $context): array {
        $input     = $args['input'];
        $firstName = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], trim($input['firstName']));
        $lastName  = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], trim($input['lastName']));
        $schoolID  = (int)$input['schoolID'];

        $sql = "INSERT INTO systemRoster (firstName, lastName, schoolID)
                VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $input['firstName'], $input['lastName'], $schoolID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $systemRosterID = mysqli_insert_id($GLOBALS['___mysqli_ston']);

        $sql = "SELECT * FROM systemRoster WHERE systemRosterID = {$systemRosterID}";
        return mysqlQuery($sql, SINGLE);
    }

    public static function updatePerson($root, array $args, array $context): array {
        $systemRosterID = (int)$args['personID'];
        $input          = $args['input'];

        $setParts = [];

        if (isset($input['firstName'])) {
            $setParts[] = "firstName = ?";
        }
        if (isset($input['lastName'])) {
            $setParts[] = "lastName = ?";
        }
        if (isset($input['schoolID'])) {
            $schoolID   = (int)$input['schoolID'];
            $setParts[] = "schoolID = {$schoolID}";
        }
        if (array_key_exists('hemaRatingsID', $input)) {
            $hemaID     = $input['hemaRatingsID'] !== null ? (int)$input['hemaRatingsID'] : 'NULL';
            $setParts[] = "HemaRatingsID = {$hemaID}";
        }

        if (empty($setParts)) {
            $sql = "SELECT * FROM systemRoster WHERE systemRosterID = {$systemRosterID}";
            return mysqlQuery($sql, SINGLE);
        }

        // Build parameterized query for string fields, literal SQL for int/null fields
        $stringValues = [];
        $typeStr      = '';
        if (isset($input['firstName'])) {
            $stringValues[] = trim($input['firstName']);
            $typeStr .= 's';
        }
        if (isset($input['lastName'])) {
            $stringValues[] = trim($input['lastName']);
            $typeStr .= 's';
        }

        $setClause = implode(', ', $setParts);
        $sql       = "UPDATE systemRoster SET {$setClause} WHERE systemRosterID = {$systemRosterID}";

        if ($typeStr) {
            $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], $sql);
            mysqli_stmt_bind_param($stmt, $typeStr, ...$stringValues);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            mysqlQuery($sql, SEND);
        }

        $sql = "SELECT * FROM systemRoster WHERE systemRosterID = {$systemRosterID}";
        return mysqlQuery($sql, SINGLE);
    }
}
