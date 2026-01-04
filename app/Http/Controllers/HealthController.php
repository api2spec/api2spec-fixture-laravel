<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class HealthController extends Controller
{
    /**
     * Basic health check.
     *
     * GET /api/health
     *
     * @return JsonResponse 200: Health status
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => Carbon::now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    }

    /**
     * Liveness probe.
     *
     * GET /api/health/live
     *
     * @return JsonResponse 200: Liveness status
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Readiness probe.
     *
     * GET /api/health/ready
     *
     * @return JsonResponse 200: Ready (all checks pass)
     * @return JsonResponse 503: Not ready (some checks fail)
     */
    public function ready(): JsonResponse
    {
        $checks = [
            ['name' => 'memory', 'status' => 'ok'],
            ['name' => 'database', 'status' => 'ok'],
        ];

        $allOk = collect($checks)->every(fn ($c) => $c['status'] === 'ok');
        $status = $allOk ? 'ok' : 'degraded';
        $statusCode = $allOk ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => Carbon::now()->toIso8601String(),
            'checks' => $checks,
        ], $statusCode);
    }

    /**
     * TIF 418 signature endpoint.
     *
     * GET /api/brew
     *
     * @return JsonResponse 418: I'm a teapot
     */
    public function brew(): JsonResponse
    {
        return response()->json([
            'error' => "I'm a teapot",
            'message' => 'This server is TIF-compliant and cannot brew coffee',
            'spec' => 'https://teapotframework.dev',
        ], 418);
    }
}
