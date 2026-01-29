<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Libraries\WhatsApp\WhatsAppFactory;
use App\Libraries\AI\AIFactory;

class WebhookController extends BaseController {
    use ResponseTrait;

    public function index() {
        // 1. Ambil Data Webhook
        $json = $this->request->getJSON(true);
        if (!$json) {
            log_message('error', 'Webhook Error: No JSON Data received.');
            return $this->fail('No Data', 400);
        }

        // 2. Load Database & Settings
        $db = \Config\Database::connect();
        $settings = $db->table('bot_settings')->get()->getRowArray();

        if (!$settings) {
            log_message('critical', 'Webhook Error: Bot Settings not found in DB.');
            return $this->fail('Bot not configured', 500);
        }

        // 3. Inisialisasi Driver WA & Parsing Data
        try {
            $wa = WhatsAppFactory::create($settings['wa_provider'], $settings['wa_api_token']);
            $data = $wa->parseWebhook($json); 
        } catch (\Exception $e) {
            log_message('error', 'WA Driver Error: ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }

        // Normalisasi Nomor HP (Hapus karakter aneh biar match database akurat)
        // Ubah 08... jadi 628... jika perlu, atau biarkan raw sesuai format Fonnte (biasanya 62...)
        $senderPhone = preg_replace('/[^0-9]/', '', $data['phone']); 
        $userMessage = trim($data['message']);
        $senderName  = $data['name'] ?? 'Unknown';

        // Validasi Payload
        if (empty($senderPhone) || empty($userMessage)) return $this->fail('Invalid Payload: Missing Phone/Message');

        // Ignor pesan status/device (Khusus Fonnte)
        if ($settings['wa_provider'] == 'fonnte' && isset($json['device_id'])) {
            return $this->respond('Self status ignored');
        }

        // --- LOGIC 1: MANUAL OVERRIDE (Bot On/Off via WA) ---
        // FIX: Menggunakan kolom 'phone_number' sesuai struktur tabel users kamu
        $adminUser = $db->table('users')
                        ->where('phone_number', $senderPhone) // Gunakan WHERE biar presisi
                        ->get()->getRowArray();
        
        if ($adminUser) {
            $cmd = strtolower($userMessage);
            if ($cmd === '#bot off') {
                $db->table('bot_settings')->update(['is_active' => 0], ['id' => $settings['id']]);
                $wa->sendMessage($senderPhone, "System: Bot DIMATIKAN.");
                return $this->respond('Bot turned off by Admin');
            }
            if ($cmd === '#bot on') {
                $db->table('bot_settings')->update(['is_active' => 1], ['id' => $settings['id']]);
                $wa->sendMessage($senderPhone, "System: Bot DIAKTIFKAN.");
                return $this->respond('Bot turned on by Admin');
            }
        }

        // --- LOGIC 2: CEK DATABASE LEADS & UPDATE ---
        // Mulai Transaksi Database (Biar aman)
        $db->transStart();

        $lead = $db->table('leads')->where('phone', $senderPhone)->get()->getRowArray();
        $leadId = null;

        if ($lead) {
            // Lead Lama: Update Timestamp
            $db->table('leads')->where('id', $lead['id'])->update([
                'last_interaction_at' => date('Y-m-d H:i:s')
            ]);
            $leadId = $lead['id'];

            // GUARD: Cek Status Lead (Human Handling)
            // Jika statusnya booking/survey/closing, Bot DIAM.
            if (in_array($lead['status'], ['booking', 'survey', 'closing'])) {
                $db->transComplete(); // Selesaikan transaksi update timestamp tadi
                return $this->respond('Lead handled by human, bot skipped.');
            }
        } else {
            // Lead Baru: Insert
            $newLeadData = [
                'name'   => $senderName != 'Unknown' ? $senderName : "Lead $senderPhone",
                'phone'  => $senderPhone,
                'source' => 'WA Bot', // Bisa diganti 'Fonnte' atau lainnya
                'status' => 'new',
                'last_interaction_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->table('leads')->insert($newLeadData);
            $leadId = $db->insertID();
        }

        // Simpan Chat Masuk (User)
        $db->table('chat_histories')->insert([
            'lead_id'      => $leadId,
            'phone_number' => $senderPhone,
            'sender'       => 'user',
            'message'      => $userMessage,
            'created_at'   => date('Y-m-d H:i:s')
        ]);

        $db->transComplete(); // Komit perubahan database

        // --- LOGIC 3: AI PROCESSING ---
        if ($settings['is_active'] == 0) return $this->respond('Bot is OFF');

        // Persiapan Knowledge Base
        $knowledgeData = $db->table('bot_knowledge')->where('is_active', 1)->get()->getResultArray();
        
        $knowledgeText = "";
        if (!empty($knowledgeData)) {
            foreach ($knowledgeData as $k) {
                $knowledgeText .= "--- KONTEKS: {$k['title']} ---\n{$k['content_text']}\n\n";
            }
        } else {
            $knowledgeText = "Belum ada data spesifik produk. Jawablah secara umum dan sopan sebagai Customer Service.";
        }

        // Susun System Prompt
        $systemPrompt = $settings['behavior_prompt'] . "\n\n" . 
                        "INSTRUKSI KHUSUS:\n" . 
                        "1. Gunakan data berikut sebagai referensi utama:\n" . $knowledgeText . "\n" . 
                        "2. Jawablah dengan singkat, ramah, dan persuasif.\n" .
                        "3. Jika jawaban tidak ditemukan di data, arahkan user menghubungi Admin/Sales.";

        // Panggil AI
        try {
            $ai = AIFactory::create($settings['ai_provider'], $settings['api_key'], $settings['ai_model']);
            $reply = $ai->chat($systemPrompt, $userMessage);

            // Kirim Balasan WA
            $wa->sendMessage($senderPhone, $reply);

            // Simpan Chat Balasan (Bot)
            $db->table('chat_histories')->insert([
                'lead_id'      => $leadId,
                'phone_number' => $senderPhone,
                'sender'       => 'bot',
                'message'      => $reply,
                'created_at'   => date('Y-m-d H:i:s')
            ]);

            return $this->respond('Replied via AI');

        } catch (\Exception $e) {
            log_message('error', 'AI Processing Error: ' . $e->getMessage());
            return $this->fail('AI Error: ' . $e->getMessage());
        }
    }
}