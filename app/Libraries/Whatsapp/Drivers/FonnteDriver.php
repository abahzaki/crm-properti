<?php namespace App\Libraries\WhatsApp\Drivers;

use App\Libraries\WhatsApp\WhatsAppInterface;

class FonnteDriver implements WhatsAppInterface {
    protected $token;

    public function __construct($token) {
        $this->token = $token;
    }

    public function sendMessage($target, $message) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
            ],
            CURLOPT_HTTPHEADER => ["Authorization: $this->token"],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function parseWebhook($requestData) {
        // Adaptasi format JSON Fonnte ke format standar aplikasi kita
        return [
            'phone'   => $requestData['sender'] ?? null,
            'message' => $requestData['message'] ?? null,
            'name'    => $requestData['name'] ?? 'Unknown'
        ];
    }
}