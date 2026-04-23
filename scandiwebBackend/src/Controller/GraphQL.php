<?php

declare(strict_types=1);

namespace App\Controller;

use App\GraphQL\SchemaBuilder;
use App\Logger\FileLogger;
use GraphQL\Error\DebugFlag;
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
            $query          = $input['query']         ?? '';
            $variableValues = $input['variables']     ?? null;
            $operationName  = $input['operationName'] ?? null;

            $result = GraphQLBase::executeQuery(
                $schema,
                $query,
                null,
                null,
                $variableValues,
                $operationName
            );

            $debugFlag = (($_ENV['APP_DEBUG'] ?? 'false') === 'true')
                ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
                : DebugFlag::NONE;

            $output = $result->toArray($debugFlag);

        } catch (Throwable $e) {
            // Log the exception to a timestamped file in logs/
            FileLogger::logException($e, 'graphql_error');

            $output = [
                'errors' => [[
                    'message' => (($_ENV['APP_DEBUG'] ?? 'false') === 'true')
                        ? $e->getMessage()
                        : 'Internal server error',
                ]],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}
