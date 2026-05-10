<?php
namespace HemaScorecard\GraphQL\Scalars;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;

class DateScalar {

    public static function serialize(mixed $value): string {
        return (string) $value;
    }

    public static function parseValue(mixed $value): string {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            throw new \UnexpectedValueException(
                "Date must be a string in YYYY-MM-DD format, got: " . json_encode($value)
            );
        }
        return $value;
    }

    public static function parseLiteral(Node $valueNode, ?array $variables = null): string {
        if (!$valueNode instanceof StringValueNode) {
            throw new \UnexpectedValueException("Date must be a string literal");
        }
        return self::parseValue($valueNode->value);
    }
}
