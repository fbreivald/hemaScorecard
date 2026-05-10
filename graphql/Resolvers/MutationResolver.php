<?php
namespace HemaScorecard\GraphQL\Resolvers;

class MutationResolver {

    // input key => [db column, type ('s'|'i')]
    private static array $personColumnMap = [
        'firstName'    => ['firstName',     's'],
        'lastName'     => ['lastName',      's'],
        'middleName'   => ['middleName',    's'],
        'nickname'     => ['nickname',      's'],
        'gender'       => ['gender',        's'],
        'birthdate'    => ['birthdate',     's'],
        'country'      => ['rosterCountry', 's'],
        'province'     => ['rosterProvince','s'],
        'city'         => ['rosterCity',    's'],
        'email'        => ['eMail',         's'],
        'schoolID'     => ['schoolID',      'i'],
        'hemaRatingsID'=> ['HemaRatingsID', 'i'],
    ];

    public static function createPerson($root, array $args, array $context): array {
        $input   = $args['input'];
        $columns = [];
        $values  = [];
        $types   = '';

        foreach (self::$personColumnMap as $key => [$col, $type]) {
            if (!isset($input[$key])) {
                continue;
            }
            $columns[] = $col;
            $values[]  = $type === 'i' ? (int)$input[$key] : trim((string)$input[$key]);
            $types    .= $type;
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $colList      = implode(', ', $columns);
        $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'],
            "INSERT INTO systemRoster ({$colList}) VALUES ({$placeholders})");
        mysqli_stmt_bind_param($stmt, $types, ...$values);
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
        $values   = [];
        $types    = '';

        foreach (self::$personColumnMap as $key => [$col, $type]) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $setParts[] = "{$col} = ?";
            $values[]   = $input[$key] !== null
                ? ($type === 'i' ? (int)$input[$key] : trim((string)$input[$key]))
                : null;
            $types .= $type;
        }

        if (!empty($setParts)) {
            $sql  = "UPDATE systemRoster SET " . implode(', ', $setParts)
                  . " WHERE systemRosterID = {$systemRosterID}";
            $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], $sql);
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $sql = "SELECT * FROM systemRoster WHERE systemRosterID = {$systemRosterID}";
        return mysqlQuery($sql, SINGLE);
    }

    public static function createExchange($root, array $args, array $context): array {
        $input   = $args['input'];
        $matchID = (int)$input['matchID'];

        $sql            = "SELECT COUNT(exchangeID) AS n FROM eventExchanges WHERE matchID = {$matchID}";
        $exchangeNumber = (int)mysqlQuery($sql, SINGLE, 'n') + 1;

        $exchangeType   = $input['exchangeType'];
        $scoringID      = isset($input['scoringID'])      ? (int)$input['scoringID']      : null;
        $receivingID    = isset($input['receivingID'])    ? (int)$input['receivingID']    : null;
        $scoreValue     = isset($input['scoreValue'])     ? (float)$input['scoreValue']   : null;
        $scoreDeduction = isset($input['scoreDeduction']) ? (float)$input['scoreDeduction'] : null;
        $exchangeTime   = isset($input['exchangeTime'])   ? (int)$input['exchangeTime']   : null;
        $refPrefix      = isset($input['refPrefix'])      ? (int)$input['refPrefix']      : null;
        $refTarget      = isset($input['refTarget'])      ? (int)$input['refTarget']      : null;
        $refType        = isset($input['refType'])        ? (int)$input['refType']        : null;

        $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'],
            "INSERT INTO eventExchanges
             (matchID, exchangeType, scoringID, receivingID, scoreValue,
              scoreDeduction, exchangeTime, refPrefix, refTarget, refType, exchangeNumber)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isiiiddiii',
            $matchID, $exchangeType, $scoringID, $receivingID, $scoreValue,
            $scoreDeduction, $exchangeTime, $refPrefix, $refTarget, $refType, $exchangeNumber);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $exchangeID = mysqli_insert_id($GLOBALS['___mysqli_ston']);
        $sql        = "SELECT * FROM eventExchanges WHERE exchangeID = {$exchangeID}";
        return mysqlQuery($sql, SINGLE);
    }

    public static function updateExchange($root, array $args, array $context): array {
        $exchangeID = (int)$args['exchangeID'];
        $input      = $args['input'];

        $setParts = [];
        $values   = [];
        $types    = '';

        if (array_key_exists('exchangeType', $input)) {
            $setParts[] = 'exchangeType = ?';
            $values[]   = $input['exchangeType'];
            $types     .= 's';
        }
        foreach (['scoringID' => 'i', 'receivingID' => 'i', 'exchangeTime' => 'i',
                  'refPrefix' => 'i', 'refTarget' => 'i', 'refType' => 'i'] as $field => $type) {
            if (array_key_exists($field, $input)) {
                $setParts[] = "{$field} = ?";
                $values[]   = $input[$field] !== null ? (int)$input[$field] : null;
                $types     .= $type;
            }
        }
        foreach (['scoreValue' => 'd', 'scoreDeduction' => 'd'] as $field => $type) {
            if (array_key_exists($field, $input)) {
                $setParts[] = "{$field} = ?";
                $values[]   = $input[$field] !== null ? (float)$input[$field] : null;
                $types     .= $type;
            }
        }

        if (!empty($setParts)) {
            $sql  = "UPDATE eventExchanges SET " . implode(', ', $setParts)
                  . " WHERE exchangeID = {$exchangeID}";
            $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], $sql);
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $sql = "SELECT * FROM eventExchanges WHERE exchangeID = {$exchangeID}";
        return mysqlQuery($sql, SINGLE);
    }

    public static function deleteExchange($root, array $args, array $context): bool {
        $exchangeID = (int)$args['exchangeID'];
        $sql        = "DELETE FROM eventExchanges WHERE exchangeID = {$exchangeID}";
        mysqlQuery($sql, SEND);
        return true;
    }

    public static function createClean($root, array $args, array $context): array {
        return self::insertTypedExchange('clean', $args['matchID'], [
            'scoringID'    => $args['scorerID']    ?? null,
            'receivingID'  => $args['receiverID']  ?? null,
            'scoreValue'   => $args['score']  ?? null,
            'exchangeTime' => $args['exchangeTime'] ?? null,
            'refPrefix'    => $args['attack']['prefix'] ?? null,
            'refTarget'    => $args['attack']['target'] ?? null,
            'refType'      => $args['attack']['type']   ?? null,
        ]);
    }

    public static function createAfterBlow($root, array $args, array $context): array {
        return self::insertTypedExchange('afterblow', $args['matchID'], [
            'scoringID'    => $args['scorerID']    ?? null,
            'receivingID'  => $args['receiverID']  ?? null,
            'scoreValue'   => $args['score']  ?? null,
            'exchangeTime' => $args['exchangeTime'] ?? null,
            'refPrefix'    => $args['attack']['prefix'] ?? null,
            'refTarget'    => $args['attack']['target'] ?? null,
            'refType'      => $args['attack']['type']   ?? null,
        ]);
    }

    public static function createDouble($root, array $args, array $context): array {
        return self::insertTypedExchange('double', $args['matchID'], [
            'exchangeTime' => $args['exchangeTime'] ?? null,
            'refPrefix'    => $args['attack']['prefix'] ?? null,
            'refTarget'    => $args['attack']['target'] ?? null,
            'refType'      => $args['attack']['type']   ?? null,
        ]);
    }

    public static function createDoubleOut($root, array $args, array $context): array {
        return self::insertTypedExchange('doubleOut', $args['matchID'], [
            'exchangeTime' => $args['exchangeTime'] ?? null,
            'refPrefix'    => $args['attack']['prefix'] ?? null,
            'refTarget'    => $args['attack']['target'] ?? null,
            'refType'      => $args['attack']['type']   ?? null,
        ]);
    }

    public static function createWin($root, array $args, array $context): array {
        return self::insertTypedExchange('winner', $args['matchID'], [
            'scoringID'    => (int)$args['winnerID'],
            'exchangeTime' => $args['exchangeTime'] ?? null,
        ]);
    }

    public static function createTie($root, array $args, array $context): array {
        return self::insertTypedExchange('tie', $args['matchID'], [
            'exchangeTime' => $args['exchangeTime'] ?? null,
        ]);
    }

    public static function createNoExchange($root, array $args, array $context): array {
        return self::insertTypedExchange('noExchange', $args['matchID'], [
            'exchangeTime' => $args['exchangeTime'] ?? null,
        ]);
    }

    public static function createNoQuality($root, array $args, array $context): array {
        return self::insertTypedExchange('noQuality', $args['matchID'], [
            'receivingID'  => isset($args['recipientID']) ? (int)$args['recipientID'] : null,
            'exchangeTime' => $args['exchangeTime'] ?? null,
        ]);
    }

    public static function createPenalty($root, array $args, array $context): array {
        $cardMap = ['yellow' => 'yellowCard', 'red' => 'redCard', 'black' => 'blackCard'];
        $cardCode = $cardMap[$args['card']] ?? 'yellowCard';
        $cardEsc  = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $cardCode);
        $actionEsc = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $args['illegalAction']);

        $cardID   = (int)mysqlQuery(
            "SELECT attackID FROM systemAttacks WHERE attackCode = '{$cardEsc}' AND attackClass = 'penalty' LIMIT 1",
            SINGLE, 'attackID'
        );
        $actionID = (int)mysqlQuery(
            "SELECT attackID FROM systemAttacks WHERE attackCode = '{$actionEsc}' AND attackClass = 'illegalAction' LIMIT 1",
            SINGLE, 'attackID'
        );

        return self::insertTypedExchange('penalty', $args['matchID'], [
            'receivingID'    => (int)$args['recipientID'],
            'scoreDeduction' => $args['score'] ?? null,
            'exchangeTime'   => $args['exchangeTime'] ?? null,
            'refTarget'      => $actionID ?: null,
            'refType'        => $cardID   ?: null,
        ]);
    }

    public static function createSwitchFighter($root, array $args, array $context): array {
        return self::insertTypedExchange('switchFighter', $args['matchID'], [
            'scoringID'    => (int)$args['outgoingID'],
            'receivingID'  => (int)$args['incomingID'],
            'exchangeTime' => $args['exchangeTime'] ?? null,
        ]);
    }

    private static function insertTypedExchange(string $exchangeType, int|string $matchID, array $fields): array {
        $matchID = (int)$matchID;
        $n       = (int)mysqlQuery(
            "SELECT COUNT(exchangeID) AS n FROM eventExchanges WHERE matchID = {$matchID}",
            SINGLE, 'n'
        );
        $exchangeNumber = $n + 1;

        $scoringID      = isset($fields['scoringID'])      ? (int)$fields['scoringID']      : null;
        $receivingID    = isset($fields['receivingID'])    ? (int)$fields['receivingID']    : null;
        $scoreValue     = isset($fields['scoreValue'])     ? (float)$fields['scoreValue']   : null;
        $scoreDeduction = isset($fields['scoreDeduction']) ? (float)$fields['scoreDeduction'] : null;
        $exchangeTime   = isset($fields['exchangeTime'])   ? (int)$fields['exchangeTime']   : null;
        $refPrefix      = isset($fields['refPrefix'])      ? (int)$fields['refPrefix']      : null;
        $refTarget      = isset($fields['refTarget'])      ? (int)$fields['refTarget']      : null;
        $refType        = isset($fields['refType'])        ? (int)$fields['refType']        : null;

        $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'],
            "INSERT INTO eventExchanges
             (matchID, exchangeType, scoringID, receivingID, scoreValue,
              scoreDeduction, exchangeTime, refPrefix, refTarget, refType, exchangeNumber)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isiiiddiii',
            $matchID, $exchangeType, $scoringID, $receivingID, $scoreValue,
            $scoreDeduction, $exchangeTime, $refPrefix, $refTarget, $refType, $exchangeNumber);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $exchangeID = mysqli_insert_id($GLOBALS['___mysqli_ston']);
        return mysqlQuery("SELECT * FROM eventExchanges WHERE exchangeID = {$exchangeID}", SINGLE);
    }

    public static function createSchool($root, array $args, array $context): array {
        $input = $args['input'];

        $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'],
            "INSERT INTO systemSchools (schoolFullName, schoolShortName, schoolAbbreviation,
                                        schoolBranch, schoolCity, schoolProvince, countryIso2)
             VALUES (?, ?, ?, ?, ?, ?, ?)");
        $fullName     = trim($input['fullName']);
        $shortName    = isset($input['shortName'])    ? trim($input['shortName'])    : null;
        $abbreviation = isset($input['abbreviation']) ? trim($input['abbreviation']) : null;
        $branch       = isset($input['branch'])       ? trim($input['branch'])       : null;
        $city         = isset($input['city'])         ? trim($input['city'])         : null;
        $province     = isset($input['province'])     ? trim($input['province'])     : null;
        $countryIso2  = isset($input['countryIso2'])  ? trim($input['countryIso2'])  : null;
        mysqli_stmt_bind_param($stmt, 'sssssss',
            $fullName, $shortName, $abbreviation, $branch, $city, $province, $countryIso2);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $schoolID = mysqli_insert_id($GLOBALS['___mysqli_ston']);
        return getSchoolInfo($schoolID);
    }

    public static function updateSchool($root, array $args, array $context): array {
        $schoolID = (int)$args['schoolID'];
        $input    = $args['input'];

        $columnMap = [
            'fullName'     => 'schoolFullName',
            'shortName'    => 'schoolShortName',
            'abbreviation' => 'schoolAbbreviation',
            'branch'       => 'schoolBranch',
            'city'         => 'schoolCity',
            'province'     => 'schoolProvince',
            'countryIso2'  => 'countryIso2',
        ];

        $setParts = [];
        $values   = [];
        foreach ($columnMap as $inputKey => $column) {
            if (array_key_exists($inputKey, $input)) {
                $setParts[] = "{$column} = ?";
                $values[]   = $input[$inputKey] !== null ? trim((string)$input[$inputKey]) : null;
            }
        }

        if (!empty($setParts)) {
            $typeStr = str_repeat('s', count($values));
            $sql     = "UPDATE systemSchools SET " . implode(', ', $setParts)
                     . " WHERE schoolID = {$schoolID}";
            $stmt    = mysqli_prepare($GLOBALS['___mysqli_ston'], $sql);
            mysqli_stmt_bind_param($stmt, $typeStr, ...$values);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        return getSchoolInfo($schoolID);
    }
}
