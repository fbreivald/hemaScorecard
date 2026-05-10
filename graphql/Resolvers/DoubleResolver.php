<?php
namespace HemaScorecard\GraphQL\Resolvers;

class DoubleResolver {
    use ExchangeCommonFields;

    public static function fighter1Attack(array $exchange, array $args, array $context): ?array {
        return self::attackObject($exchange);
    }

    public static function fighter2Attack(array $exchange, array $args, array $context): ?array {
        return null;
    }
}
