<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levelsData = [
            'Level One' => ['able', 'love', 'buy', 'cube', 'wavy', 'bowl', 'claw', 'you', 'clay', 'clue'],
            'Level Two' => ['risk', 'sir', 'dirt', 'kids', 'verb', 'dark', 'four', 'draw', 'feud', 'cake'],
            'Level Three' => ['foxy', 'onyx', 'gown', 'honk', 'minx', 'hack', 'claw', 'hawk', 'numb'],
            'Level Four' => ['quiz', 'mazy', 'jive', 'vape', 'jump', 'squiz', 'equip', 'czar', 'pivot'],
        ];

        foreach ($levelsData as $levelTitle => $words) {
            $level = \App\Models\Level::where('title', $levelTitle)->first();
            if ($level) {
                // Remove potential duplicates just in case
                $uniqueWords = array_unique($words);
                
                $order = 1;
                foreach ($uniqueWords as $word) {
                    \App\Models\TestWord::updateOrCreate(
                        ['level_id' => $level->id, 'word' => $word],
                        ['order' => $order++]
                    );
                }
            }
        }
    }
}
