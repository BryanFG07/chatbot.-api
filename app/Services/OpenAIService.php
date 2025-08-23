<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use Exception;

class OpenAIService
{
    /**
     * Sends a question to OpenAI (chat model) and returns the answer as a string.
     *
     * @param string $question
     * @return array
     * @throws Exception
     */
    public function ask(string $question): array
    {
        try {
            // Validate input
            if (empty(trim($question))) {
                throw new Exception('Question cannot be empty');
            }

            // Check if API key is configured
            if (empty(config('openai.api_key'))) {
                throw new Exception('OpenAI API key is not configured');
            }

            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $question]
                ],
                'max_tokens' => 300
            ]);

            // Check if response has choices
            if (empty($response->choices)) {
                throw new Exception('No response received from OpenAI');
            }

            $content = $response->choices[0]->message->content ?? null;
            
            if (empty($content)) {
                throw new Exception('Empty response received from OpenAI');
            }

            return [
                'success' => true,
                'answer' => trim($content),
            ];

        } catch (ErrorException $e) {
            // OpenAI API errors (rate limits, invalid requests, etc.)
            return [
                'success' => false,
                'error' => 'OpenAI API Error: ' . $e->getMessage(),
                'error_type' => 'api_error'
            ];
            
        } catch (TransporterException $e) {
            // Network/transport errors
            return [
                'success' => false,
                'error' => 'Unable to connect to OpenAI' . $e->getMessage(),
                'error_type' => 'network_error'
            ];
            
        } catch (Exception $e) {
            // General errors
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => 'general_error'
            ];
        }
    }
}
