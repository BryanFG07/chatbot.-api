<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    /**
     * Sends a question to OpenAI (chat model) and returns the answer as a string.
     *
     * @param string $question
     * @return string
     */
    public function ask(string $question): string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $question]
                ],
                'max_tokens' => 150,
            ]);

            // Return the content of the first message, trimmed
            return trim($response->choices[0]->message->content ?? 'No answer received');

        } catch (\Exception $e) {
            // Return error message if OpenAI API fails
            return 'Error communicating with OpenAI: ' . $e->getMessage();
        }
    }
}
