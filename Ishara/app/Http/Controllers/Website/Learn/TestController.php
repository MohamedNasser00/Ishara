<?php

namespace App\Http\Controllers\Website\Learn;

use App\Http\Controllers\Controller;
use App\Services\Website\Learn\TestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $testService;

    public function __construct(TestService $testService)
    {
        $this->testService = $testService;
    }

    /**
     * Get all levels with test word progress.
     */
    public function levels(): JsonResponse
    {
        return $this->testService->getLevelsWithTestProgress();
    }

    /**
     * Complete a test word.
     */
    public function complete($wordId): JsonResponse
    {
        return $this->testService->completeTestWord($wordId);
    }
}
