<?php

namespace App\Services\Website\Learn;

use App\Models\Level;
use App\Models\Lesson;
use App\Models\UserLesson;
use App\Services\HandleResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class LearnService
{
    /**
     * Get all levels with lessons and current user progress.
     */
    public function getLevelsWithProgress()
    {
        try {
            $user = Auth::user();
            
            $levels = Level::with(['lessons' => function($query) use ($user) {
                // We'll calculate progress for each lesson
            }])->orderBy('order')->get();

            $formattedLevels = $levels->map(function ($level) use ($user) {
                $lessons = $level->lessons->map(function ($lesson) use ($user) {
                    $progress = $user->userLessons()->where('lesson_id', $lesson->id)->first();
                    return [
                        'id' => $lesson->id,
                        'letter' => $lesson->letter,
                        'is_completed' => $progress ? (bool) $progress->is_completed : false,
                        'completed_at' => $progress ? $progress->completed_at : null,
                    ];
                });

                $totalLessons = $lessons->count();
                $completedCount = $lessons->where('is_completed', true)->count();
                $percentage = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;

                return [
                    'id' => $level->id,
                    'title' => $level->title,
                    'completion_percentage' => $percentage,
                    'completed_lessons' => $completedCount,
                    'total_lessons' => $totalLessons,
                    'is_level_completed' => ($percentage === 100),
                    'lessons' => $lessons,
                ];
            });

            return HandleResponse::success('Learn data retrieved successfully', [
                'levels' => $formattedLevels
            ]);

        } catch (Exception $e) {
            Log::error('❌ Get Learn Progress Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to retrieve progress', [], 500);
        }
    }

    /**
     * Mark a lesson as completed.
     */
    public function completeLesson($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = Lesson::find($lessonId);

            if (!$lesson) {
                return HandleResponse::fail('Lesson not found', [], 404);
            }

            $progress = UserLesson::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['is_completed' => true, 'completed_at' => now()]
            );

            return HandleResponse::success('Lesson marked as completed', [
                'lesson_id' => $lesson->id,
                'is_completed' => true
            ]);

        } catch (Exception $e) {
            Log::error('❌ Complete Lesson Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to update progress', [], 500);
        }
    }
}
