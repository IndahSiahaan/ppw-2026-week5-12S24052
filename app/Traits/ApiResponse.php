<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data, string $message = 'OK', int $status = 200): JsonResponse
    {
        $payload = ['message' => $message];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        return response()->json($payload, $status);
    }

    protected function createdResponse(mixed $data, string $message = 'Created'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function problemResponse(
        int    $status,
        string $title,
        string $detail,
        string $instance = '',
        array  $extra = []
    ): JsonResponse {
        $body = array_merge([
            'type'     => 'https://httpstatuses.io/' . $status,
            'title'    => $title,
            'status'   => $status,
            'detail'   => $detail,
            'instance' => $instance ?: request()->path(),
        ], $extra);

        return response()->json($body, $status, ['Content-Type' => 'application/problem+json']);
    }
}
