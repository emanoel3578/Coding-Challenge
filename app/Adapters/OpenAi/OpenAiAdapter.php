<?php

namespace App\Adapters\OpenAi;

use App\Adapters\Interfaces\IAiRequestAdapter;
use App\ValueObjects\PromptResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;
use Throwable;

class OpenAiAdapter implements IAiRequestAdapter
{
    private Client $client;

    public function __construct()
    {
        $this->startOpenAiClient();
    }

    private function startOpenAiClient()
    {
        try {
            $apiKey = env('OPENAI_API_KEY');
            $this->client = OpenAI::client($apiKey);
        } catch (Throwable $th) {
            throw new Exception('Error starting OpenAI client: ' . $th->getMessage());
        }
    }

    public function sendRequest(array $messages): PromptResponse
    {
        try {
            $promptResponse = $this->client->chat()->create(
                [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                ]
            );

            $response = $promptResponse->choices[0]->message->content ?? null;

            if (!$response) {
                return new PromptResponse('Sorry, I could not process your request. Please try again.');
            }

            return new PromptResponse($response);
        } catch (Throwable $th) {
            Log::error('Error sending request to OpenAI: ' . $th->getMessage());
            return new PromptResponse('Sorry, I could not process your request. Please try again.');
        }
    }
}
