<?php
namespace HemaScorecard\GraphQL\Resolvers;

class SwitchFighterResolver {
    use ExchangeCommonFields;

    public static function incoming(array $exchange, array $args, array $context): ?array {
        return self::rosterEntry((int)($exchange['receivingID'] ?? 0));
    }

    public static function outgoing(array $exchange, array $args, array $context): ?array {
        return self::rosterEntry((int)($exchange['scoringID'] ?? 0));
    }
}
