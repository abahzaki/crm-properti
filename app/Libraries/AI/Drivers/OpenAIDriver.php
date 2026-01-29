<?php namespace App\Libraries\AI\Drivers;

use App\Libraries\AI\AIInterface;

class OpenAIDriver implements AIInterface {
    protected $apiKey;
    protected $model;

    public function __construct($apiKey, $model = 'gpt-4o-mini') {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function chat($systemPrompt, $userMessage) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'temperature' => 0.7
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            return "Maaf, otak AI sedang gangguan (Error $httpCode).";
        }

        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? 'Tidak ada jawaban.';
    }
}