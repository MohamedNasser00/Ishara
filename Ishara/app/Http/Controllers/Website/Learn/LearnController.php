<?php

namespace App\Http\Controllers\Website\Learn;

use App\Http\Controllers\Controller;
use App\Services\Website\Learn\LearnService;
use Illuminate\Http\JsonResponse;

class LearnController extends Controller
{
    protected $learnService;

    public function __construct(LearnService $learnService)
    {
        $this->learnService = $learnService;
    }

    /**
     * Get all levels with progress.
     */
    public function levels(): JsonResponse
    {
        return $this->learnService->getLevelsWithProgress();
    }

    /**
     * Complete a lesson.
     */
    public function complete($lessonId): JsonResponse
    {
        return $this->learnService->completeLesson($lessonId);
    }
}
