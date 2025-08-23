<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\OpenAIService;


class ChatController extends Controller
{
    protected $openAI;

    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }

    /**
     * POST /api/ask
     * Receives a question, asks OpenAI, saves the interaction, and returns JSON.
     */
    public function ask(Request $request)
    {
        // Validate input
        $validation = $this->validateAskRequest($request);
        if ($validation) {
            return $validation;
        }

        $question = $request->input('question');

        // Get AI response
        $aiResult = $this->getAIResponse($question);
        if (!$aiResult['success']) {
            return $this->handleAIError($question, $aiResult);
        }

        // Save interaction and return response
        return $this->saveAndRespond($question, $aiResult);
    }

    /**
     * Validate the ask request
     */
    private function validateAskRequest(Request $request): ?object
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:1000|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request',
                'messages' => $validator->errors()
            ], 422);
        }

        return null;
    }

    /**
     * Get AI response with error handling
     */
    private function getAIResponse(string $question): array
    {
        try {
            return $this->openAI->ask($question);
        } catch (\Exception $e) {
            Log::error('AI Service Exception', [
                'question' => $question,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'AI service unavailable',
                'error_type' => 'service_error'
            ];
        }
    }

    /**
     * Handle AI service errors
     */
    private function handleAIError(string $question, array $result): object
    {
        Log::error('OpenAI Service Error', [
            'question' => $question,
            'error' => $result['error'],
            'error_type' => $result['error_type'] ?? 'unknown'
        ]);

        $statusCode = match($result['error_type'] ?? 'unknown') {
            'api_error' => 422,
            'network_error', 'service_error' => 503,
            default => 500
        };

        return response()->json([
            'success' => false,
            'error' => 'AI Service Error',
            'message' => $this->getUserFriendlyError($result['error_type'] ?? 'general_error')
        ], $statusCode);
    }

    /**
     * Save interaction and return response
     */
    private function saveAndRespond(string $question, array $aiResult): object
    {
        // Validate content size
        if (!$this->isContentSizeValid($question, $aiResult['answer'])) {
            Log::warning('Content too large to save', [
                'question_length' => strlen($question),
                'answer_length' => strlen($aiResult['answer'])
            ]);

            return response()->json([
                'success' => true,
                'id' => null,
                'answer' => $aiResult['answer'],
                'warning' => 'Content too large to save to history'
            ], 200);
        }

        // Attempt to save
        try {
            $interaction = Interaction::create([
                'question' => $question,
                'answer' => $aiResult['answer'],
            ]);

            return response()->json([
                'success' => true,
                'id' => $interaction->id,
                'answer' => $aiResult['answer']
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->handleDatabaseError($question, $aiResult, $e, 'persistence');

        } catch (\Exception $e) {
            return $this->handleDatabaseError($question, $aiResult, $e, 'general');
        }
    }

    /**
     * Handle database errors during interaction save
     */
    private function handleDatabaseError(string $question, array $aiResult, \Exception $e, string $type): object
    {
        $logContext = [
            'question' => $question,
            'answer_length' => strlen($aiResult['answer']),
            'error' => $e->getMessage(),
            'type' => $type
        ];

        if ($e instanceof \Illuminate\Database\QueryException) {
            $logContext['error_code'] = $e->getCode();
            Log::error('Database Persistence Error', $logContext);
        } else {
            $logContext['trace'] = $e->getTraceAsString();
            Log::error('Interaction Save Error', $logContext);
        }

        // Graceful degradation: return AI answer without saving
        return response()->json([
            'success' => true,
            'id' => null,
            'answer' => $aiResult['answer'],
            'warning' => 'Answer generated but not saved to history'
        ], 200);
    }

    /**
     * Validate content size before saving
     */
    private function isContentSizeValid(string $question, string $answer): bool
    {
        return strlen($question) <= 1000 && strlen($answer) <= 10000;
    }

    public function history(Request $request)
    {
        try {
            $limit = intval($request->query('limit', 10));
            $keyword = $request->query('keyword');

            // Validate limit
            if ($limit < 1 || $limit > 100) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid limit',
                    'message' => 'Limit must be between 1 and 100'
                ], 422);
            }

            $query = Interaction::orderBy('created_at', 'desc');

            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('question', 'like', "%$keyword%")
                    ->orWhere('answer', 'like', "%$keyword%");
                });
            }

            $interactions = $query->take($limit)
                ->get(['id', 'question', 'answer', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => $interactions,
                'meta' => [
                    'count' => $interactions->count(),
                    'limit' => $limit,
                    'keyword' => $keyword
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database Query Error in History', [
                'limit' => $limit,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => 'Unable to retrieve chat history due to database issue'
            ], 503);

        } catch (\Exception $e) {
            Log::error('ChatController History Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error',
                'message' => 'Unable to retrieve chat history'
            ], 500);
        }
    }
    
    /**
     * DELETE /api/history
     * Deletes all chat history
     */
    public function deleteHistory(Request $request)
    {
        try {
            Interaction::truncate();
            return response()->json([
                'success' => true,
                'message' => 'History deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete History Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Unable to delete history.'
            ], 500);
        }
    }

    /**
     * Convert technical error types to user-friendly messages
     */
    private function getUserFriendlyError(string $errorType): string
    {
        return match($errorType) {
            'api_error' => 'The AI service is temporarily unavailable. Please try again in a few moments.',
            'network_error' => 'Connection error. Please check your internet connection and try again.',
            'general_error' => 'Unable to process your request. Please try again.',
            default => 'An unexpected error occurred. Please try again later.'
        };
    }
}
