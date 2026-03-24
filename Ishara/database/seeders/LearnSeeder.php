<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LearnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            'Level One' => ['A', 'B', 'C', 'E', 'L', 'O', 'V', 'W', 'U', 'Y'],
            'Level Two' => ['D', 'F', 'K', 'R', 'S', 'I', 'T'],
            'Level Three' => ['G', 'H', 'M', 'N', 'X'],
            'Level Four' => ['P', 'Q', 'Z', 'J'],
        ];

        $order = 1;
        foreach ($levels as $title => $letters) {
            $level = \App\Models\Level::create([
                'title' => $title,
                'order' => $order++,
            ]);

            $lessonOrder = 1;
            foreach ($letters as $letter) {
                \App\Models\Lesson::create([
                    'level_id' => $level->id,
                    'letter' => $letter,
                    'order' => $lessonOrder++,
                ]);
            }
        }
    }
}
