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

        if (!$settings || $settings['is_active'] == 0) {
            // Jika setting tidak ada atau bot dimatikan via switch panel
            return $this->fail('Bot is OFF or Not Configured'); 
        }

        // 3. Cek Saldo Token (SaaS Logic)
        if ($settings['token_balance'] <= 0) {
            // Opsional: Kirim notif ke Owner kalau saldo habis
            log_message('error', 'Bot Skipped: Token Balance Empty');
            return $this->fail('Token Habis');
        }

        // 4. Inisialisasi Driver WA & Parsing Data
        try {
            $wa = WhatsAppFactory::create($settings['wa_provider'], $settings['wa_api_token']);
            $data = $wa->parseWebhook($json); 
        } catch (\Exception $e) {
            log_message('error', 'WA Driver Error: ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }

        // Normalisasi Nomor HP
        $senderPhone = preg_replace('/[^0-9]/', '', $data['phone']); 
        $userMessage = trim($data['message']);
        $senderName  = $data['name'] ?? 'Unknown';

        // Validasi Payload & Self Message
        if (empty($senderPhone) || empty($userMessage)) return $this->fail('Invalid Payload');
        if ($settings['wa_provider'] == 'fonnte' && isset($json['device_id'])) return $this->respond('Self status ignored');

        // --- LOGIC 1: MANUAL OVERRIDE (Bot On/Off via WA oleh Admin) ---
        // Cek admin berdasarkan phone_number di tabel users
        $adminUser = $db->table('users')->where('phone_number', $senderPhone)->get()->getRowArray();
        
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

        // --- LOGIC 2: DATABASE LEADS & TOGGLE AUTO SAVE ---
        $db->transStart();
        
        $lead = $db->table('leads')->where('phone', $senderPhone)->get()->getRowArray();
        $leadId = null;

        if ($lead) {
            // Lead Lama: Update Timestamp
            $db->table('leads')->where('id', $lead['id'])->update(['last_interaction_at' => date('Y-m-d H:i:s')]);
            $leadId = $lead['id'];

            // GUARD: Cek Status Lead (Human Handling)
            if (in_array($lead['status'], ['booking', 'survey', 'closing'])) {
                $db->transComplete();
                return $this->respond('Lead handled by human, bot skipped.');
            }
        } else {
            // Lead Baru: Cek Toggle Auto Save
            if ($settings['auto_save_leads'] == 1) {
                $db->table('leads')->insert([
                    'name'   => $senderName != 'Unknown' ? $senderName : "Lead $senderPhone",
                    'phone'  => $senderPhone,
                    'source' => 'WA Bot',
                    'status' => 'new',
                    'last_interaction_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $leadId = $db->insertID();
            } else {
                // Jika OFF, biarkan leadId null (Chat tetap masuk history tanpa terikat ID Lead, atau buat logic khusus)
                // Untuk kesederhanaan relasi database, kita bisa buat "Ghost Lead" atau biarkan NULL di chat_histories (pastikan kolom lead_id nullable)
                $leadId = null; 
            }
        }

        // Simpan Chat Masuk (User)
        $db->table('chat_histories')->insert([
            'lead_id'      => $leadId,
            'phone_number' => $senderPhone,
            'sender'       => 'user',
            'message'      => $userMessage,
            'created_at'   => date('Y-m-d H:i:s')
        ]);
        
        $db->transComplete();

        // --- LOGIC 3: AI PROCESSING & TOKEN CALCULATION ---

        // Tentukan Model & Cost
        // Mapping: standard -> gpt-4o-mini, advanced -> gpt-4o
        $aiModelName = ($settings['ai_model'] == 'advanced') ? 'gpt-4o' : 'gpt-4o-mini';
        $tokenCost   = ($settings['ai_model'] == 'advanced') ? 5 : 1; // 5 Credit vs 1 Credit

        // Cek lagi saldo cukup gak buat reply ini?
        if ($settings['token_balance'] < $tokenCost) {
            // Saldo tidak cukup untuk reply ini
            return $this->fail('Insufficient Token Balance');
        }

        // Persiapan Knowledge Base
        $knowledgeData = $db->table('bot_knowledge')->where('is_active', 1)->get()->getResultArray();
        $knowledgeText = "";
        if (!empty($knowledgeData)) {
            foreach ($knowledgeData as $k) {
                $knowledgeText .= "--- INFO: {$k['title']} ---\n{$k['content_text']}\n\n";
            }
        } else {
            $knowledgeText = "Belum ada data spesifik. Jawablah sopan sebagai CS.";
        }

        // Susun System Prompt
        $systemPrompt = $settings['behavior_prompt'] . "\n\n" . 
                        "DATABASE PENGETAHUAN:\n" . $knowledgeText . "\n" . 
                        "INSTRUKSI: Jawablah berdasarkan data di atas. Jika tidak tahu, arahkan ke Admin.";

        // Panggil AI (Pakai Master Key)
        try {
            $ai = AIFactory::create($settings['ai_provider'], $settings['master_api_key'], $aiModelName);
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

            // --- POTONG SALDO TOKEN ---
            $newBalance = $settings['token_balance'] - $tokenCost;
            $db->table('bot_settings')->where('id', $settings['id'])->update(['token_balance' => $newBalance]);

            return $this->respond("Replied via AI ($aiModelName). Cost: $tokenCost Credits. Remaining: $newBalance");

        } catch (\Exception $e) {
            log_message('error', 'AI Processing Error: ' . $e->getMessage());
            return $this->fail('AI Error');
        }
    }
}