<?php
namespace HemaScorecard\GraphQL\Resolvers;

class CleanResolver {
    use ExchangeCommonFields;

    public static function scorer(array $exchange, array $args, array $context): ?array {
        return self::rosterEntry((int)($exchange['scoringID'] ?? 0));
    }

    public static function receiver(array $exchange, array $args, array $context): ?array {
        return self::rosterEntry((int)($exchange['receivingID'] ?? 0));
    }

    public static function attack(array $exchange, array $args, array $context): ?array {
        return self::attackObject($exchange);
    }
}
