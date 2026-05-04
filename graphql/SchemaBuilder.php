<?php
namespace HemaScorecard\GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use HemaScorecard\GraphQL\Scalars\DateScalar;
use HemaScorecard\GraphQL\Resolvers\EventResolver;
use HemaScorecard\GraphQL\Resolvers\MutationResolver;
use HemaScorecard\GraphQL\Resolvers\EventRosterEntryResolver;
use HemaScorecard\GraphQL\Resolvers\ExchangeResolver;
use HemaScorecard\GraphQL\Resolvers\TournamentRosterEntryResolver;
use HemaScorecard\GraphQL\Resolvers\MatchResolver;
use HemaScorecard\GraphQL\Resolvers\PersonResolver;
use HemaScorecard\GraphQL\Resolvers\PoolResolver;
use HemaScorecard\GraphQL\Resolvers\PoolStandingResolver;
use HemaScorecard\GraphQL\Resolvers\QueryResolver;
use HemaScorecard\GraphQL\Resolvers\SchoolResolver;
use HemaScorecard\GraphQL\Resolvers\TournamentResolver;

class SchemaBuilder {

    private static array $resolverMap = [
        'Mutation'        => MutationResolver::class,
        'Query'           => QueryResolver::class,
        'Event'           => EventResolver::class,
        'EventRosterEntry'=> EventRosterEntryResolver::class,
        'Person'       => PersonResolver::class,
        'School'       => SchoolResolver::class,
        'Tournament'   => TournamentResolver::class,
        'Pool'         => PoolResolver::class,
        'PoolStanding' => PoolStandingResolver::class,
        'Match'        => MatchResolver::class,
        'Exchange'              => ExchangeResolver::class,
        'TournamentRosterEntry' => TournamentRosterEntryResolver::class,
    ];

    public static function build(): Schema {
        $sdl = file_get_contents(__DIR__ . '/schema.graphql');

        return BuildSchema::build($sdl, function (array $typeConfig): array {
            if ($typeConfig['name'] === 'Date') {
                $typeConfig['serialize']    = [DateScalar::class, 'serialize'];
                $typeConfig['parseValue']   = [DateScalar::class, 'parseValue'];
                $typeConfig['parseLiteral'] = [DateScalar::class, 'parseLiteral'];
                return $typeConfig;
            }

            $resolverClass = self::$resolverMap[$typeConfig['name']] ?? null;
            if ($resolverClass === null) {
                return $typeConfig;
            }

            $typeConfig['resolveField'] = static function ($root, array $args, array $context, $info) use ($resolverClass) {
                $field = $info->fieldName;
                if (method_exists($resolverClass, $field)) {
                    return $resolverClass::$field($root, $args, $context);
                }
                // Default: return matching key from associative array
                return $root[$field] ?? null;
            };

            return $typeConfig;
        });
    }
}
