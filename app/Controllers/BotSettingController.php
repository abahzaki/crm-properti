<?php namespace App\Controllers;

use App\Controllers\BaseController;

class BotSettingController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Cek Role (Hanya Admin/Owner/Manager yg boleh akses)
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'owner', 'manager'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $data = [
            'title'   => 'Konfigurasi AI Bot',
            // Ambil data setting baris pertama
            'setting' => $this->db->table('bot_settings')->get()->getRowArray()
        ];

        return view('bot/settings', $data);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        
        $data = [
            'bot_name'        => $this->request->getPost('bot_name'),
            'is_active'       => $this->request->getPost('is_active'),
            'wa_provider'     => $this->request->getPost('wa_provider'),
            'wa_api_token'    => $this->request->getPost('wa_api_token'),
            'ai_model'        => $this->request->getPost('ai_model'),
            'ai_temperature'  => $this->request->getPost('ai_temperature'),
            'auto_save_leads' => $this->request->getPost('auto_save_leads'),
            'behavior_prompt' => $this->request->getPost('behavior_prompt'),
            'updated_by'      => session()->get('id') // Catat siapa yg ubah
        ];

        $this->db->table('bot_settings')->where('id', $id)->update($data);

        return redirect()->to('/bot-settings')->with('success', 'Konfigurasi Bot berhasil diperbarui!');
    }

    public function topup()
    {
        // Ambil data user yang sedang login untuk ditampilkan di invoice
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', session()->get('id'))->get()->getRowArray();
        
        $data = [
            'title' => 'Invoice Topup Token',
            'user'  => $user
        ];
        
        return view('bot/topup_invoice', $data);
    }
}