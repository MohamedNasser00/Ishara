<?php

namespace App\Services\Website\Learn;

use App\Models\Level;
use App\Models\Lesson;
use App\Models\UserPracticeProgress;
use App\Services\HandleResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PracticeService
{
    /**
     * Get all levels with lessons and current user practice progress.
     */
    public function getLevelsWithPracticeProgress()
    {
        try {
            $user = Auth::user();
            
            $levels = Level::with(['lessons'])->orderBy('order')->get();

            $formattedLevels = $levels->map(function ($level) use ($user) {
                $lessons = $level->lessons->map(function ($lesson) use ($user) {
                    $progress = UserPracticeProgress::where('user_id', $user->id)
                        ->where('lesson_id', $lesson->id)
                        ->first();
                    
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

            return HandleResponse::success('Practice data retrieved successfully', [
                'levels' => $formattedLevels
            ]);

        } catch (Exception $e) {
            Log::error('❌ Get Practice Progress Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to retrieve practice progress', [], 500);
        }
    }

    /**
     * Mark a practice lesson as completed.
     */
    public function completePracticeLesson($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = Lesson::find($lessonId);

            if (!$lesson) {
                return HandleResponse::fail('Lesson not found', [], 404);
            }

            $progress = UserPracticeProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['is_completed' => true, 'completed_at' => now()]
            );

            return HandleResponse::success('Practice lesson marked as completed', [
                'lesson_id' => $lesson->id,
                'is_completed' => true
            ]);

        } catch (Exception $e) {
            Log::error('❌ Complete Practice Lesson Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to update practice progress', [], 500);
        }
    }
}
