<?php
namespace HemaScorecard\GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use HemaScorecard\GraphQL\Scalars\DateScalar;
use HemaScorecard\GraphQL\Resolvers\AfterblowResolver;
use HemaScorecard\GraphQL\Resolvers\CleanResolver;
use HemaScorecard\GraphQL\Resolvers\DoubleResolver;
use HemaScorecard\GraphQL\Resolvers\DoubleOutResolver;
use HemaScorecard\GraphQL\Resolvers\ElimResolver;
use HemaScorecard\GraphQL\Resolvers\EventResolver;
use HemaScorecard\GraphQL\Resolvers\NoExchangeResolver;
use HemaScorecard\GraphQL\Resolvers\NoQualityResolver;
use HemaScorecard\GraphQL\Resolvers\PenaltyResolver;
use HemaScorecard\GraphQL\Resolvers\ScoredResolver;
use HemaScorecard\GraphQL\Resolvers\SwitchFighterResolver;
use HemaScorecard\GraphQL\Resolvers\TieResolver;
use HemaScorecard\GraphQL\Resolvers\MutationResolver;
use HemaScorecard\GraphQL\Resolvers\EventRosterEntryResolver;
use HemaScorecard\GraphQL\Resolvers\ExchangeResolver;
use HemaScorecard\GraphQL\Resolvers\TournamentRosterEntryResolver;
use HemaScorecard\GraphQL\Resolvers\WinResolver;
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
        'Elim'         => ElimResolver::class,
        'PoolStanding' => PoolStandingResolver::class,
        'Match'        => MatchResolver::class,
        'Exchange'              => ExchangeResolver::class,
        'Win'                   => WinResolver::class,
        'Tie'                   => TieResolver::class,
        'NoExchange'            => NoExchangeResolver::class,
        'Clean'                 => CleanResolver::class,
        'Afterblow'             => AfterblowResolver::class,
        'Double'                => DoubleResolver::class,
        'DoubleOut'             => DoubleOutResolver::class,
        'Scored'                => ScoredResolver::class,
        'NoQuality'             => NoQualityResolver::class,
        'Penalty'               => PenaltyResolver::class,
        'SwitchFighter'         => SwitchFighterResolver::class,
        'TournamentRosterEntry' => TournamentRosterEntryResolver::class,
    ];

    public static function build(): Schema {
        $sdl = file_get_contents(__DIR__ . '/schema.graphql');

        return BuildSchema::build($sdl, function (array $typeConfig): array {
            if ($typeConfig['name'] === 'Date') {
                $typeConfig['serialize']     = [DateScalar::class, 'serialize'];
                $typeConfig['parseValue']    = [DateScalar::class, 'parseValue'];
                $typeConfig['parseLiteral']  = [DateScalar::class, 'parseLiteral'];
                return $typeConfig;
            }

            if ($typeConfig['name'] === 'Exchange') {
                $typeConfig['resolveType'] = static function (array $exchange): string {
                    return match ($exchange['exchangeType'] ?? '') {
                        'clean'                     => 'Clean',
                        'afterblow'                 => 'Afterblow',
                        'winner'                    => 'Win',
                        'tie'                       => 'Tie',
                        'double'                    => 'Double',
                        'doubleOut'                 => 'DoubleOut',
                        'scored'                    => 'Scored',
                        'noQuality'                 => 'NoQuality',
                        'penalty'                   => 'Penalty',
                        'switchFighter'             => 'SwitchFighter',
                        default                     => 'NoExchange',
                    };
                };
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
