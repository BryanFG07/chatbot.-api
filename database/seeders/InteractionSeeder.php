<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Interaction;

class InteractionSeeder extends Seeder
{
    public function run()
    {
        Interaction::create([
            'question' => 'What is financial wellness?',
            'answer' => 'Financial wellness means having control over your finances and being prepared for emergencies.',
        ]);
        Interaction::create([
            'question' => 'How can I save money?',
            'answer' => 'Track your expenses, set a budget, and save a portion of your income regularly.',
        ]);
        Interaction::create([
            'question' => 'Give me a tip for productivity.',
            'answer' => 'Set clear goals, prioritize tasks, and take regular breaks to stay focused.',
        ]);
        Interaction::create([
            'question' => 'What is the best way to learn programming?',
            'answer' => 'Practice regularly, build small projects, and read documentation and tutorials.',
        ]);
    }
}
