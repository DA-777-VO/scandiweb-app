<?php

declare(strict_types=1);

namespace App\Controller;

use App\GraphQL\SchemaBuilder;
use GraphQL\GraphQL as GraphQLBase;
use RuntimeException;
use Throwable;

class GraphQL
{
    public static function handle(): string
    {
        try {
            $schema = SchemaBuilder::build();

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input          = json_decode($rawInput, true);
            $query          = $input['query'] ?? '';
            $variableValues = $input['variables'] ?? null;
            $operationName  = $input['operationName'] ?? null;

            $result = GraphQLBase::executeQuery(
                $schema,
                $query,
                null,
                null,
                $variableValues,
                $operationName
            );

            $output = $result->toArray(
                (bool)($_ENV['APP_DEBUG'] ?? false)
                    ? \GraphQL\Error\DebugFlag::INCLUDE_DEBUG_MESSAGE | \GraphQL\Error\DebugFlag::INCLUDE_TRACE
                    : \GraphQL\Error\DebugFlag::NONE
            );
        } catch (Throwable $e) {
            $output = [
                'errors' => [
                    ['message' => $e->getMessage()],
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}
