<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Libraries\WhatsApp\WhatsAppFactory;
use App\Libraries\AI\AIFactory;

class WebhookController extends BaseController {
    
    use ResponseTrait;

    public function index() {
        
        // 1. LOAD SETTINGS & DATABASE
        $db = \Config\Database::connect();
        $settings = $db->table('bot_settings')->get()->getRowArray();

        if (!$settings) return $this->fail('Bot Settings Not Found', 500);

        // 2. VERIFIKASI META (SALAMAN)
        if ($this->request->getMethod() === 'get') {
            $hubMode = $this->request->getGet('hub_mode');
            $hubToken = $this->request->getGet('hub_verify_token');
            $hubChallenge = $this->request->getGet('hub_challenge');
            $myVerifyToken = $settings['wa_verify_token'] ?? 'estato_secret';

            if ($hubMode === 'subscribe' && $hubToken === $myVerifyToken) {
                return $this->response->setStatusCode(200)->setBody($hubChallenge);
            }
            return $this->failForbidden('Invalid Verify Token');
        }

        // 3. AMBIL DATA RAW
        $rawInput = $this->request->getJSON(true) ?? $this->request->getPost();
        if (empty($rawInput)) {
            $stream = file_get_contents('php://input');
            if (!empty($stream)) $rawInput = json_decode($stream, true);
        }
        
        if (empty($rawInput)) return $this->respond('No Data'); // Jangan 400, biar Meta gak retry terus

        // 4. PARSING & FILTERING (STEP KRUSIAL)
        try {
            $wa = WhatsAppFactory::create(
                $settings['wa_provider'], 
                $settings['wa_api_token'],
                $settings['wa_phone_id']
            );
            
            $data = $wa->parseWebhook($rawInput); 
            
            // FILTER 1: Jika data null (berarti itu Status Update/Read Receipt), STOP!
            // Ini mencegah bot bocor ke user lain.
            if (!$data) {
                return $this->respond('Status update ignored');
            }

            // Normalisasi
            $senderPhone = preg_replace('/[^0-9]/', '', $data['phone']); 
            $userMessage = trim($data['message']);
            $msgId       = $data['id'] ?? null; // ID Unik dari Meta

            // FILTER 2: Cek apakah Pesan Kosong?
            if (empty($userMessage)) return $this->respond('Empty message ignored');

            // FILTER 3: IDEMPOTENCY CHECK (ANTI SPAM LOOP)
            // Cek di database apakah Message ID ini sudah pernah diproses?
            if ($msgId) {
                $isExist = $db->table('chat_histories')->where('wa_message_id', $msgId)->countAllResults();
                if ($isExist > 0) {
                    // SUDAH PERNAH DIPROSES. STOP.
                    return $this->respond('Message already processed (Idempotency)');
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'WA Error: ' . $e->getMessage());
            return $this->respond('Internal Error Handled'); // Return 200 biar Meta gak retry
        }

        // --- MULAI DARI SINI LOGIKA PROSES PESAN (AMAN) ---

        // 5. CEK STATUS BOT & SALDO
        if ($settings['is_active'] == 0) return $this->respond('Bot Off');
        if ($settings['token_balance'] <= 0) return $this->respond('Token Habis');

        // 6. MANAJEMEN LEADS
        $db->transStart();
        
        $lead = $db->table('leads')->where('phone', $senderPhone)->get()->getRowArray();
        $leadId = null;

        if ($lead) {
            $db->table('leads')->where('id', $lead['id'])->update(['last_interaction_at' => date('Y-m-d H:i:s')]);
            $leadId = $lead['id'];
            
            // Stop jika sedang dihandle manusia
            if (in_array($lead['status'], ['booking', 'survey', 'closing'])) {
                $db->transComplete();
                return $this->respond('Lead handled by human');
            }
        } else {
            if ($settings['auto_save_leads'] == 1) {
                $db->table('leads')->insert([
                    'name'   => $data['name'] ?? "Lead $senderPhone",
                    'phone'  => $senderPhone,
                    'source' => 'WA Bot',
                    'status' => 'new',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $leadId = $db->insertID();
            }
        }

        // SIMPAN PESAN USER KE DB (DENGAN MESSAGE ID)
        $db->table('chat_histories')->insert([
            'lead_id'       => $leadId,
            'phone_number'  => $senderPhone,
            'sender'        => 'user',
            'message'       => $userMessage,
            'wa_message_id' => $msgId, // <--- PENTING: Simpan ID ini
            'created_at'    => date('Y-m-d H:i:s')
        ]);
        
        $db->transComplete();

        // 7. PROSES AI
        // ... (Logika Admin Command #bot on/off bisa ditaruh disini atau sebelum save db) ...

        $aiModelName = ($settings['ai_model'] == 'advanced') ? 'gpt-4o' : 'gpt-4o-mini';
        $tokenCost   = ($settings['ai_model'] == 'advanced') ? 5 : 1;

        // Ambil Knowledge
        $knowledgeData = $db->table('bot_knowledge')->where('is_active', 1)->get()->getResultArray();
        $knowledgeText = "";
        foreach ($knowledgeData as $k) {
            $knowledgeText .= "--- INFO: {$k['title']} ---\n{$k['content_text']}\n\n";
        }

        $systemPrompt = $settings['behavior_prompt'] . "\n\n" . 
                        "DATABASE:\n" . $knowledgeText . "\n" . 
                        "INSTRUKSI: Jawab singkat, padat, persuasif. Jangan mengarang.";

        try {
            $ai = AIFactory::create($settings['ai_provider'], $settings['master_api_key'], $aiModelName);
            $reply = $ai->chat($systemPrompt, $userMessage, (float)$settings['ai_temperature']);

            // Kirim Balasan
            $wa->sendMessage($senderPhone, $reply);

            // Simpan Balasan Bot
            $db->table('chat_histories')->insert([
                'lead_id'      => $leadId,
                'phone_number' => $senderPhone,
                'sender'       => 'bot',
                'message'      => $reply,
                'created_at'   => date('Y-m-d H:i:s')
            ]);

            // Potong Saldo
            $db->table('bot_settings')->where('id', $settings['id'])->decrement('token_balance', $tokenCost);

            return $this->respond("Replied success");

        } catch (\Exception $e) {
            return $this->fail('AI Error');
        }
    }
}