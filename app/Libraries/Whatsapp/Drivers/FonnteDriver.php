<?php namespace App\Libraries\WhatsApp\Drivers;

use App\Libraries\WhatsApp\WhatsAppInterface;

class FonnteDriver implements WhatsAppInterface {

    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    public function sendMessage($targetPhone, $message) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $targetPhone,
                'message' => $message,
                'countryCode' => '62', // Otomatis ubah 08 jadi 62
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    public function parseWebhook($data) {
        // --- LOGIKA FILTER ANTI SPAM & BOCOR (FONNTE) ---

        // 1. Cek apakah ini pesan keluar (dari Bot sendiri)?
        // Fonnte kadang kirim parameter 'me' = true jika itu pesan dari device sendiri
        if (isset($data['me']) && $data['me'] == true) {
            return null; // ABAIKAN, JANGAN DIPROSES
        }

        // 2. Cek apakah ini pesan dari GRUP?
        // Pesan grup di WA biasanya ID pengirimnya diakhiri @g.us
        // Kita wajib tolak biar bot gak nyamber orang di grup
        $sender = $data['sender'] ?? '';
        if (strpos($sender, '@g.us') !== false) {
            return null; // ABAIKAN PESAN GRUP
        }

        // 3. Cek apakah pesan kosong?
        $message = $data['message'] ?? '';
        if (trim($message) == '') {
            return null;
        }

        // 4. Ambil ID Pesan (Jika ada) untuk mencegah pemrosesan ganda
        // Fonnte biasanya kirim 'id'
        $msgId = $data['id'] ?? null;

        // --- LOLOS SELEKSI, KEMBALIKAN DATA ---
        return [
            'id'      => $msgId,          // ID Pesan (Penting buat Anti-Loop di Controller)
            'phone'   => $sender,         // Nomor HP Pengirim
            'name'    => $data['name'] ?? 'Unknown',
            'message' => $message,        // Isi Pesan
            'raw'     => $data            // Data mentah (buat debug)
        ];
    }
}