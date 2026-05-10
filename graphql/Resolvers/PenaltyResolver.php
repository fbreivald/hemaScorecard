<?php
namespace HemaScorecard\GraphQL\Resolvers;

class PenaltyResolver {
    use ExchangeCommonFields;

    // refType stores the card as a systemAttacks ID (attackClass = 'penalty')
    public static function card(array $exchange, array $args, array $context): ?string {
        $code = self::attackCode((int)($exchange['refType'] ?? 0));
        return match ($code) {
            'yellowCard' => 'yellow',
            'redCard'    => 'red',
            'blackCard'  => 'black',
            default      => null,
        };
    }

    // refTarget stores the illegal action as a systemAttacks ID (attackClass = 'illegalAction')
    public static function illegalAction(array $exchange, array $args, array $context): ?string {
        $code = self::attackCode((int)($exchange['refTarget'] ?? 0));
        return $code ? trim($code) : null;
    }

    public static function recipient(array $exchange, array $args, array $context): ?array {
        return self::rosterEntry((int)($exchange['receivingID'] ?? 0));
    }

}
