<?php namespace App\Libraries\AI;

use App\Libraries\AI\Drivers\OpenAIDriver;

class AIFactory {
    public static function create($provider, $apiKey, $model) {
        switch ($provider) {
            case 'openai':
                return new OpenAIDriver($apiKey, $model);
            // case 'gemini': return new GeminiDriver($apiKey, $model); // Next dev
            default:
                throw new \Exception("Provider AI tidak didukung.");
        }
    }
}