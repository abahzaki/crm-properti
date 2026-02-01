<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Libraries\WhatsApp\WhatsAppFactory;
use App\Libraries\AI\AIFactory;

class WebhookController extends BaseController {
    
    use ResponseTrait;

    public function index() {
        
        // =========================================================================
        // BAGIAN 0: LOAD SETTINGS DULU (CRITICAL)
        // =========================================================================
        // Kita geser ke atas karena Verifikasi Meta butuh baca token dari DB
        
        $db = \Config\Database::connect();
        $settings = $db->table('bot_settings')->get()->getRowArray();

        if (!$settings) {
            return $this->fail('Bot Settings Not Found', 500);
        }

        // =========================================================================
        // BAGIAN 1: KHUSUS META - VERIFIKASI WEBHOOK (GET REQUEST)
        // =========================================================================
        // Facebook akan kirim sinyal GET untuk tes koneksi ("Salaman")
        
        if ($this->request->getMethod() === 'get') {
            $hubMode = $this->request->getGet('hub_mode');
            $hubToken = $this->request->getGet('hub_verify_token');
            $hubChallenge = $this->request->getGet('hub_challenge');

            // Ambil Verify Token dari Database (Default 'estato_secret' jika null)
            $myVerifyToken = $settings['wa_verify_token'] ?? 'estato_secret';

            if ($hubMode === 'subscribe' && $hubToken === $myVerifyToken) {
                // Salaman Sukses! Balas dengan challenge code
                return $this->response->setStatusCode(200)->setBody($hubChallenge);
            }
            
            // Jika token salah
            return $this->failForbidden('Invalid Verify Token');
        }


        // =========================================================================
        // BAGIAN 2: PENANGKAP DATA (ULTIMATE VERSION)
        // =========================================================================
        
        $rawInput = [];

        // Metode A: Ambil JSON via CI4
        $tryJson = $this->request->getJSON(true);
        if (!empty($tryJson)) $rawInput = $tryJson;
        
        // Metode B: Ambil POST via CI4
        if (empty($rawInput)) {
            $tryPost = $this->request->getPost();
            if (!empty($tryPost)) $rawInput = $tryPost;
        }

        // Metode C: Baca Raw Stream PHP (Jurus Anti-Gagal)
        if (empty($rawInput)) {
            $stream = file_get_contents('php://input');
            if (!empty($stream)) {
                $jsonStream = json_decode($stream, true);
                if (json_last_error() === JSON_ERROR_NONE) $rawInput = $jsonStream;
            }
        }

        // Metode D: Global Variable $_POST
        if (empty($rawInput) && !empty($_POST)) $rawInput = $_POST;

        // --- VALIDASI AKHIR ---
        if (empty($rawInput)) {
            // Jangan log error jika request kosong biasa, spam log nanti
            return $this->fail('No Data Received', 400);
        }


        // =========================================================================
        // BAGIAN 3: CEK STATUS BOT
        // =========================================================================
        
        if ($settings['is_active'] == 0) {
            return $this->fail('Bot is OFF'); 
        }

        if ($settings['token_balance'] <= 0) {
            return $this->fail('Token Habis');
        }


        // =========================================================================
        // BAGIAN 4: PARSING WHATSAPP (SUPPORT META & FONNTE)
        // =========================================================================
        
        try {
            // [UPDATE PENTING] Mengirim 3 Parameter: Provider, Token, dan PhoneID
            $wa = WhatsAppFactory::create(
                $settings['wa_provider'], 
                $settings['wa_api_token'],
                $settings['wa_phone_id'] // <--- Parameter ke-3 (Wajib buat Meta)
            );
            
            $data = $wa->parseWebhook($rawInput); 
            
            // Jika data null (bukan pesan teks valid), stop.
            if (!$data) return $this->respond('Ignored event (not text)');

        } catch (\Exception $e) {
            log_message('error', 'WA Driver Error: ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }

        // Normalisasi Data
        $senderPhone = preg_replace('/[^0-9]/', '', $data['phone']); 
        $userMessage = trim($data['message']);
        $senderName  = $data['name'] ?? 'Unknown';

        // Validasi Payload
        if (empty($senderPhone) || empty($userMessage)) return $this->fail('Invalid Payload');
        
        // Cek Device ID (Mencegah Fonnte Bot bicara sendiri)
        if ($settings['wa_provider'] == 'fonnte' && isset($rawInput['device_id']) && !isset($rawInput['sender'])) {
             return $this->respond('Self status ignored');
        }


        // =========================================================================
        // BAGIAN 5: PERINTAH ADMIN (#bot on / #bot off)
        // =========================================================================
        
        $adminUser = $db->table('users')->where('phone_number', $senderPhone)->get()->getRowArray();
        
        if ($adminUser) {
            $cmd = strtolower($userMessage);
            if ($cmd === '#bot off') {
                $db->table('bot_settings')->update(['is_active' => 0], ['id' => $settings['id']]);
                $wa->sendMessage($senderPhone, "System: Bot DIMATIKAN 🔴");
                return $this->respond('Bot turned off by Admin');
            }
            if ($cmd === '#bot on') {
                $db->table('bot_settings')->update(['is_active' => 1], ['id' => $settings['id']]);
                $wa->sendMessage($senderPhone, "System: Bot DIAKTIFKAN 🟢");
                return $this->respond('Bot turned on by Admin');
            }
        }


        // =========================================================================
        // BAGIAN 6: MANAJEMEN LEADS (SIMPAN DATABASE)
        // =========================================================================
        
        $db->transStart();
        
        $lead = $db->table('leads')->where('phone', $senderPhone)->get()->getRowArray();
        $leadId = null;

        if ($lead) {
            // Lead Lama: Update Timestamp
            $db->table('leads')->where('id', $lead['id'])->update(['last_interaction_at' => date('Y-m-d H:i:s')]);
            $leadId = $lead['id'];

            // GUARD: Human Takeover Logic
            if (in_array($lead['status'], ['booking', 'survey', 'closing'])) {
                $db->transComplete();
                return $this->respond('Lead handled by human, bot skipped.');
            }
        } else {
            // Lead Baru
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
            }
        }

        // Simpan Chat History (Pesan User)
        $db->table('chat_histories')->insert([
            'lead_id'      => $leadId,
            'phone_number' => $senderPhone,
            'sender'       => 'user',
            'message'      => $userMessage,
            'created_at'   => date('Y-m-d H:i:s')
        ]);
        
        $db->transComplete();


        // =========================================================================
        // BAGIAN 7: OTAK AI (CORE LOGIC)
        // =========================================================================

        // Hitung Cost
        $aiModelName = ($settings['ai_model'] == 'advanced') ? 'gpt-4o' : 'gpt-4o-mini';
        $tokenCost   = ($settings['ai_model'] == 'advanced') ? 5 : 1;

        if ($settings['token_balance'] < $tokenCost) {
            return $this->fail('Insufficient Token Balance');
        }

        // Ambil Knowledge Base
        $knowledgeData = $db->table('bot_knowledge')->where('is_active', 1)->get()->getResultArray();
        $knowledgeText = "";
        if (!empty($knowledgeData)) {
            foreach ($knowledgeData as $k) {
                $knowledgeText .= "--- INFO: {$k['title']} ---\n{$k['content_text']}\n\n";
            }
        } else {
            $knowledgeText = "Belum ada data spesifik. Jawablah sopan sebagai CS Properti.";
        }

        // System Prompt
        $systemPrompt = $settings['behavior_prompt'] . "\n\n" . 
                        "DATABASE PENGETAHUAN:\n" . $knowledgeText . "\n" . 
                        "INSTRUKSI:\n" . 
                        "1. Jawab HANYA berdasarkan data di atas.\n" .
                        "2. Jika tidak tahu, arahkan ke Admin.\n" . 
                        "3. Gunakan bahasa Indonesia yang persuasif.\n" .
                        "4. Jangan mengarang data harga/lokasi.";

        // Eksekusi AI
        try {
            $temperature = (float) ($settings['ai_temperature'] ?? 0.5);
            
            // Panggil AI Factory (Pastikan master_api_key benar di DB)
            $ai = AIFactory::create($settings['ai_provider'], $settings['master_api_key'], $aiModelName);
            
            // Generate Jawaban
            $reply = $ai->chat($systemPrompt, $userMessage, $temperature);

            // Kirim Balasan ke WA
            $wa->sendMessage($senderPhone, $reply);

            // Simpan Chat History (Pesan Bot)
            $db->table('chat_histories')->insert([
                'lead_id'      => $leadId,
                'phone_number' => $senderPhone,
                'sender'       => 'bot',
                'message'      => $reply,
                'created_at'   => date('Y-m-d H:i:s')
            ]);

            // Potong Saldo Token
            $newBalance = $settings['token_balance'] - $tokenCost;
            $db->table('bot_settings')->where('id', $settings['id'])->update(['token_balance' => $newBalance]);

            return $this->respond("Replied via AI. Rem: $newBalance");

        } catch (\Exception $e) {
            log_message('error', 'AI Processing Error: ' . $e->getMessage());
            return $this->fail('AI Error');
        }
    }
}