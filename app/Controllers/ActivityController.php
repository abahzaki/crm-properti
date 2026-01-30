<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\LeadsModel;

class ActivityController extends BaseController
{
    protected $activityModel;
    protected $leadsModel;

    public function __construct()
    {
        $this->activityModel = new ActivityLogModel();
        $this->leadsModel    = new LeadsModel();
    }

    public function index()
    {
        // 1. Proteksi Role
        $role = session()->get('role');
        $currentUserId = session()->get('id');

        if (!in_array($role, ['admin', 'manager', 'owner', 'spv'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        // 2. Base Query
        $this->activityModel->select('activity_logs.*, users.full_name as user_name, users.role as user_role, leads.name as lead_name');
        $this->activityModel->join('users', 'users.id = activity_logs.user_id', 'left');
        $this->activityModel->join('leads', 'leads.id = activity_logs.lead_id', 'left');

        // 3. LOGIKA KHUSUS SPV
        if ($role == 'spv') {
            $db = \Config\Database::connect();
            
            // Ambil ID bawahan + ID sendiri
            $subordinates = $db->table('users')->select('id')->where('parent_id', $currentUserId)->get()->getResultArray();
            $teamIds = array_column($subordinates, 'id');
            $teamIds[] = $currentUserId;

            $this->activityModel->whereIn('activity_logs.user_id', $teamIds);
        }

        // 4. Eksekusi Query
        $data = [
            'title' => 'Log Aktivitas Tim',
            'logs'  => $this->activityModel->orderBy('activity_logs.created_at', 'DESC')->paginate(20),
            'pager' => $this->activityModel->pager
        ];

        return view('activities/index', $data);
    }

    public function save()
    {
        // 1. Validasi Input
        if (!$this->validate([
            'lead_id'    => 'required|integer',
            'action'     => 'required',
            'details'    => 'required',
            // Validasi Foto: Maks 5MB, JPG/PNG
            'attachment' => 'is_image[attachment]|mime_in[attachment,image/jpg,image/jpeg,image/png]|max_size[attachment,5120]', 
        ])) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid atau format foto salah.');
        }

        $leadId = $this->request->getPost('lead_id');
        
        // 2. PROSES FOTO (KOMPRESI)
        $fileName = null;
        $file = $this->request->getFile('attachment');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            
            $fileName = $file->getRandomName();
            
            // Pastikan folder 'public/uploads/activities' sudah dibuat manual!
            $file->move('uploads/activities', $fileName);

            // TEKNIK KOMPRESI GAMBAR
            try {
                $image = \Config\Services::image();
                $filePath = 'uploads/activities/' . $fileName;

                $image->withFile($filePath)
                    ->resize(800, 800, true, 'height') 
                    ->save($filePath, 60); // Quality 60%
                
            } catch (\Exception $e) {
                log_message('error', 'Gagal kompresi gambar: ' . $e->getMessage());
            }
        }

        // 3. Simpan ke Database Log
        $this->activityModel->save([
            'user_id'    => session()->get('id'),
            'lead_id'    => $leadId,
            'action'     => $this->request->getPost('action'),
            'log_type'   => 'sales',
            'details'    => $this->request->getPost('details'),
            'attachment' => $fileName,
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 4. Update "Last Interaction" SAJA (JANGAN UBAH STATUS)
        // Revisi: Hapus 'status' => 'warm' agar lead Booking tidak turun kasta.
        $this->leadsModel->update($leadId, [
            'last_interaction_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Aktivitas berhasil dicatat!');
    }
}