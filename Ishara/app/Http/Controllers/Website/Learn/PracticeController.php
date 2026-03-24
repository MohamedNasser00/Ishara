<?php

namespace App\Http\Controllers\Website\Learn;

use App\Http\Controllers\Controller;
use App\Services\Website\Learn\PracticeService;
use Illuminate\Http\JsonResponse;

class PracticeController extends Controller
{
    protected $practiceService;

    public function __construct(PracticeService $practiceService)
    {
        $this->practiceService = $practiceService;
    }

    /**
     * Get all levels with practice progress.
     */
    public function levels(): JsonResponse
    {
        return $this->practiceService->getLevelsWithPracticeProgress();
    }

    /**
     * Complete a practice lesson.
     */
    public function complete($lessonId): JsonResponse
    {
        return $this->practiceService->completePracticeLesson($lessonId);
    }
}
