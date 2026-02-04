<?php namespace App\Libraries\WhatsApp\Drivers;

use App\Libraries\WhatsApp\WhatsAppInterface;

class MetaDriver implements WhatsAppInterface {

    private $token;
    private $phoneId;
    private $version = 'v18.0'; // Gunakan versi API terbaru

    public function __construct($token, $phoneId) {
        $this->token = $token;
        $this->phoneId = $phoneId;
    }

    public function sendMessage($targetPhone, $message) {
        // Meta URL Endpoint
        $url = "https://graph.facebook.com/{$this->version}/{$this->phoneId}/messages";
        
        // 1. Normalisasi Nomor (Hapus 0/62, pastikan format 62xxx)
        // Meta Strict: Harus kode negara tanpa tanda +
        $targetPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (substr($targetPhone, 0, 1) == '0') {
            $targetPhone = '62' . substr($targetPhone, 1);
        }

        // 2. Susun Payload JSON
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $targetPhone,
            'type'              => 'text',
            'text'              => ['body' => $message]
        ];

        // 3. Kirim via cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Return array hasil (opsional: bisa tambah log error jika perlu)
        return json_decode($result, true);
    }

    // Di dalam Class MetaDriver...

    public function parseWebhook($data) {
        try {
            $entry = $data['entry'][0] ?? null;
            $change = $entry['changes'][0] ?? null;
            $value = $change['value'] ?? null;

            // --- FILTER PENTING: CEK APAKAH INI PESAN? ---
            // Meta sering kirim 'statuses' (read/delivered), kita WAJIB abaikan.
            if (!isset($value['messages'][0])) {
                return null; // Abaikan jika bukan pesan (misal: status update)
            }

            $msg = $value['messages'][0];
            
            // --- FILTER PENTING: CEK TIPE PESAN ---
            // Saat ini kita hanya terima text. Kalau user kirim gambar/sticker, handle errornya.
            $messageBody = '';
            if ($msg['type'] == 'text') {
                $messageBody = $msg['text']['body'];
            } else {
                // Opsional: Balas "Maaf saya hanya mengerti teks" di Controller nanti
                $messageBody = ""; 
            }

            return [
                'id'      => $msg['id'], // <--- INI KUNCI ANTI SPAM (Message ID Unik)
                'phone'   => $msg['from'], 
                'name'    => $value['contacts'][0]['profile']['name'] ?? 'Unknown',
                'message' => $messageBody,
                'type'    => $msg['type'],
                'raw'     => $data
            ];

        } catch (\Exception $e) {
            return null;
        }
    }
}