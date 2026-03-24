<?php

namespace App\Services\Website\Learn;

use App\Models\Level;
use App\Models\TestWord;
use App\Models\UserTestProgress;
use App\Services\HandleResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class TestService
{
    /**
     * Get all levels with test words and current user test progress.
     */
    public function getLevelsWithTestProgress()
    {
        try {
            $user = Auth::user();
            
            $levels = Level::with(['testWords'])->orderBy('order')->get();

            $formattedLevels = $levels->map(function ($level) use ($user) {
                $words = $level->testWords->map(function ($testWord) use ($user) {
                    $progress = UserTestProgress::where('user_id', $user->id)
                        ->where('test_word_id', $testWord->id)
                        ->first();
                    
                    return [
                        'id' => $testWord->id,
                        'word' => $testWord->word,
                        'is_completed' => $progress ? (bool) $progress->is_completed : false,
                        'completed_at' => $progress ? $progress->completed_at : null,
                    ];
                });

                $totalWords = $words->count();
                $completedCount = $words->where('is_completed', true)->count();
                $percentage = $totalWords > 0 ? round(($completedCount / $totalWords) * 100) : 0;

                return [
                    'id' => $level->id,
                    'title' => $level->title,
                    'completion_percentage' => $percentage,
                    'completed_words' => $completedCount,
                    'total_words' => $totalWords,
                    'is_level_completed' => ($percentage === 100),
                    'words' => $words,
                ];
            });

            return HandleResponse::success('Test data retrieved successfully', [
                'levels' => $formattedLevels
            ]);

        } catch (Exception $e) {
            Log::error('❌ Get Test Progress Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to retrieve test progress', [], 500);
        }
    }

    /**
     * Mark a test word as completed.
     */
    public function completeTestWord($wordId)
    {
        try {
            $user = Auth::user();
            $testWord = TestWord::find($wordId);

            if (!$testWord) {
                return HandleResponse::fail('Word not found', [], 404);
            }

            $progress = UserTestProgress::updateOrCreate(
                ['user_id' => $user->id, 'test_word_id' => $testWord->id],
                [
                    'is_completed' => true, 
                    'completed_at' => now()
                ]
            );

            return HandleResponse::success('Test word marked as completed', [
                'test_word_id' => $testWord->id,
                'is_completed' => true
            ]);

        } catch (Exception $e) {
            Log::error('❌ Complete Test Word Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to update test progress', [], 500);
        }
    }
}
