<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\LeadsModel;

class ActivityController extends BaseController
{
    protected $activityModel;
    protected $leadsModel;


    public function index()
    {
        // 1. Proteksi Role
        $role = session()->get('role');
        $currentUserId = session()->get('id');

        if (!in_array($role, ['admin', 'manager', 'owner', 'spv'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        // 2. Base Query (Siapkan Join Dulu)
        $this->activityModel->select('activity_logs.*, users.full_name as user_name, users.role as user_role, leads.name as lead_name');
        $this->activityModel->join('users', 'users.id = activity_logs.user_id', 'left');
        $this->activityModel->join('leads', 'leads.id = activity_logs.lead_id', 'left');

        // ============================================================
        // 3. LOGIKA KHUSUS SPV (Hanya Lihat Tim Sendiri)
        // ============================================================
        if ($role == 'spv') {
            $db = \Config\Database::connect();
            
            // A. Cari siapa saja anak buah si SPV ini (berdasarkan parent_id)
            $subordinates = $db->table('users')
                               ->select('id')
                               ->where('parent_id', $currentUserId)
                               ->get()
                               ->getResultArray();
            
            // B. Kumpulkan ID anak buah ke dalam Array
            $teamIds = array_column($subordinates, 'id');
            
            // C. Masukkan ID SPV itu sendiri (supaya dia bisa lihat log kerjanya sendiri juga)
            $teamIds[] = $currentUserId;

            // D. Terapkan Filter ke Query Utama
            // "Tampilkan log DIMANA user_id ADALAH SALAH SATU DARI teamIds"
            $this->activityModel->whereIn('activity_logs.user_id', $teamIds);
        }
        // ============================================================

        // 4. Eksekusi Query
        $data = [
            'title' => 'Log Aktivitas Tim',
            'logs'  => $this->activityModel->orderBy('activity_logs.created_at', 'DESC')->paginate(20),
            'pager' => $this->activityModel->pager
        ];

        return view('activities/index', $data);
    }


    public function __construct()
    {
        $this->activityModel = new ActivityLogModel();
        $this->leadsModel    = new LeadsModel();
    }

    /**
     * [POST] /activity/save
     * Menyimpan log aktivitas sales + Kompresi Foto
     */
    public function save()
    {
        // 1. Validasi Input
        if (!$this->validate([
            'lead_id'    => 'required|integer',
            'action'     => 'required',
            'details'    => 'required',
            // Validasi Foto: Wajib Gambar, Maks 5MB (sebelum dikompres), Format JPG/PNG
            'attachment' => 'is_image[attachment]|mime_in[attachment,image/jpg,image/jpeg,image/png]|max_size[attachment,5120]', 
        ])) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid atau format foto salah.');
        }

        $leadId = $this->request->getPost('lead_id');
        $action = $this->request->getPost('action');
        
        // 2. PROSES FOTO (KOMPRESI)
        $fileName = null;
        $file = $this->request->getFile('attachment');

        // Cek apakah sales mengupload foto?
        if ($file && $file->isValid() && !$file->hasMoved()) {
            
            // A. Generate nama acak agar aman
            $fileName = $file->getRandomName();
            
            // B. Pindahkan file asli ke folder upload
            // Pastikan folder 'public/uploads/activities' sudah dibuat
            $file->move('uploads/activities', $fileName);

            // C. TEKNIK KOMPRESI (Image Manipulation)
            try {
                // Panggil Service Image CodeIgniter
                $image = \Config\Services::image();

                // Ambil path file yang baru diupload
                $filePath = 'uploads/activities/' . $fileName;

                $image->withFile($filePath)
                    ->resize(800, 800, true, 'height') // Resize: Lebar/Tinggi max 800px, rasio tetap (maintain ratio)
                    ->save($filePath, 60); // Save & Timpa file asli dengan Kualitas 60%
                
                // Hasil: Foto 5MB -> Jadi sekitar 100kb - 200kb saja!

            } catch (\Exception $e) {
                // Jika kompresi gagal, file asli tetap tersimpan (fail-safe)
                log_message('error', 'Gagal kompresi gambar: ' . $e->getMessage());
            }
        }

        // 3. Simpan ke Database
        $this->activityModel->save([
            'user_id'    => session()->get('id'),
            'lead_id'    => $leadId,
            'action'     => $action,     // Call, Visit, WhatsApp
            'log_type'   => 'sales',     // Tandai ini log sales (bukan log sistem)
            'details'    => $this->request->getPost('details'),
            'attachment' => $fileName,   // Nama file yang sudah dikompres
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 4. Update "Last Interaction" di Tabel Leads
        // Agar lead ini naik ke atas di daftar (karena baru di-follow up)
        $this->leadsModel->update($leadId, [
            'last_interaction_at' => date('Y-m-d H:i:s'),
            'status' => 'warm' // Opsi: Otomatis ubah status jadi 'Warm' jika sudah dihubungi
        ]);

        return redirect()->back()->with('success', 'Aktivitas berhasil dicatat!');
    }
}