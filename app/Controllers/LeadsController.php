<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\UnitModel;
use App\Models\BookingsModel; // Tambahkan model booking

class LeadsController extends BaseController
{
    public function index()
    {
        $model = new LeadsModel();
        $db = \Config\Database::connect();
        
        $userId = session()->get('id');
        $role   = session()->get('role');

        // Ambil Keyword Pencarian (Jika Ada)
        $keyword = $this->request->getVar('keyword');

        // Default Filter Tanggal (Default: Bulan Ini)
        $startDate = $this->request->getVar('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getVar('end_date') ?? date('Y-m-t'); 

        // QUERY BUILDER
        $builder = $model->select('leads.*, users.full_name as sales_name') 
                         ->join('users', 'users.id = leads.assigned_to', 'left'); 
        
        if ($keyword) {
            // JIKA SEARCH: Abaikan tanggal, cari Nama atau HP di seluruh DB
            $builder->groupStart()
                    ->like('leads.name', $keyword)
                    ->orLike('leads.phone', $keyword) 
                    ->orLike('leads.city', $keyword)  
                    ->groupEnd();
        } else {
            // JIKA TIDAK SEARCH: Pakai Filter Tanggal
            $builder->where('leads.lead_date >=', $startDate)
                    ->where('leads.lead_date <=', $endDate);
        }

        // --- FILTER ROLE ---
        
        // A. Admin / Owner / CS / Manager -> LIHAT SEMUA
        if (in_array($role, ['admin', 'owner', 'cs', 'manager'])) {
            // Tidak ada filter (Show All)
        }
        
        // B. SPV -> LIHAT LEADS SENDIRI + ANAK BUAH
        elseif ($role == 'spv') {
            $subordinates = $db->table('users')
                               ->select('id')
                               ->where('parent_id', $userId)
                               ->get()
                               ->getResultArray();
            
            $teamIds = array_column($subordinates, 'id');
            $teamIds[] = $userId;
            
            $builder->whereIn('leads.assigned_to', $teamIds);
        }
        
        // C. Marketing -> HANYA LIHAT PUNYA SENDIRI
        else {
            $builder->where('leads.assigned_to', $userId);
        }

        // Ambil Data
        $allLeads = $builder->orderBy('leads.created_at', 'DESC')->findAll();

        // Ambil Tim Sales (Untuk Dropdown di View)
        $salesTeam = $db->table('users')->where('role', 'marketing')->get()->getResultArray();

        $unitModel = new UnitModel();
        $availableUnits = $unitModel->where('status', 'available')
                                    ->orderBy('block', 'ASC')
                                    ->orderBy('unit_number', 'ASC')
                                    ->findAll();

        $data = [
            'title'     => 'Pipeline Leads',
            'leads'     => $allLeads,
            'totalLeads'=> count($allLeads),
            'salesTeam' => $salesTeam,
            'units'     => $availableUnits,
            'userRole'  => $role,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'keyword'   => $keyword
        ];

        return view('leads/index', $data);
    }

    // 2. SIMPAN LEAD BARU (DENGAN WA NOTIFIKASI)
    public function save()
    {
        // Validasi
        if (!$this->validate([
            'name'  => 'required|min_length[3]',
            'phone' => 'required|numeric|min_length[9]',
            'lead_date' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid.');
        }

        // Format No HP Lead (08 -> 62)
        $rawPhone = $this->request->getVar('phone');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if(substr($cleanPhone, 0, 1) == '0') $cleanPhone = '62' . substr($cleanPhone, 1);
        elseif(substr($cleanPhone, 0, 2) != '62') $cleanPhone = '62' . $cleanPhone;

        // 3. CEK DUPLIKAT (SATPAM BARU)
        $model = new LeadsModel();
        $existingLead = $model->where('phone', $cleanPhone)->first();
        
        if ($existingLead) {
            $db = \Config\Database::connect();
            $salesData = $db->table('users')->where('id', $existingLead['assigned_to'])->get()->getRowArray();
            $salesName = $salesData ? $salesData['full_name'] : 'Tanpa Nama';
            
            return redirect()->back()->withInput()->with('error', 'Nomor HP ini sudah terdaftar atas nama: ' . $existingLead['name'] . ' (Sales: ' . $salesName . ')');
        }

        // LOGIC ASSIGNMENT
        $currentUserRole = session()->get('role');
        $inputAssignedTo = $this->request->getVar('assigned_to');
        $assignTo = session()->get('id'); // Default ke diri sendiri
        
        if (in_array($currentUserRole, ['admin', 'cs', 'owner']) && !empty($inputAssignedTo)) {
            $assignTo = $inputAssignedTo;
        }

        $model->save([
            'name'                => $this->request->getVar('name'),
            'phone'               => $cleanPhone,
            'city'                => $this->request->getVar('city'),
            'lead_date'           => $this->request->getVar('lead_date'),
            'source'              => $this->request->getVar('source'),
            'product'             => $this->request->getVar('product'),
            'notes'               => $this->request->getVar('notes'),
            'status'              => 'cold', 
            'assigned_to'         => $assignTo,
            'last_interaction_at' => date('Y-m-d H:i:s')
        ]);

        // WA NOTIFIKASI
        $waUrl = null;
        if ($assignTo != session()->get('id')) {
            $db = \Config\Database::connect();
            $salesData = $db->table('users')->where('id', $assignTo)->get()->getRowArray();
            
            if ($salesData && !empty($salesData['phone_number'])) {
                $salesPhone = preg_replace('/[^0-9]/', '', $salesData['phone_number']);
                if(substr($salesPhone, 0, 1) == '0') $salesPhone = '62' . substr($salesPhone, 1);
                
                $leadName = $this->request->getVar('name');
                $produk   = $this->request->getVar('product');
                
                $msg = "Halo Kak *{$salesData['full_name']}*, ada Leads baru nih!\n\n" .
                       "Nama: *{$leadName}*\n" .
                       "Minat: {$produk}\n" .
                       "WA Lead: wa.me/{$cleanPhone}\n\n" .
                       "Tolong segera di-follow up ya. Semangat!";

                $waUrl = "https://wa.me/{$salesPhone}?text=" . urlencode($msg);
            }
        }

        return redirect()->to('/leads')
            ->with('success', 'Lead berhasil disimpan!')
            ->with('wa_notify', $waUrl);
    }

    // 3. UPDATE DATA (EDIT)
    public function update()
    {
        $id = $this->request->getVar('id');
        $model = new LeadsModel();
        
        $rawPhone = $this->request->getVar('phone');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if(substr($cleanPhone, 0, 1) == '0') $cleanPhone = '62' . substr($cleanPhone, 1);
        elseif(substr($cleanPhone, 0, 2) != '62') $cleanPhone = '62' . $cleanPhone;

        // CEK DUPLIKAT SAAT EDIT
        $existingLead = $model->where('phone', $cleanPhone)
                              ->where('id !=', $id) 
                              ->first();

        if ($existingLead) {
             $db = \Config\Database::connect();
             $salesData = $db->table('users')->where('id', $existingLead['assigned_to'])->get()->getRowArray();
             $salesName = $salesData ? $salesData['full_name'] : 'Tanpa Nama';

             return redirect()->back()->with('error', 'Gagal Update! Nomor HP ini sudah dipakai oleh lead lain: ' . $existingLead['name'] . ' (Sales: ' . $salesName . ')');
        }

        $data = [
            'lead_date' => $this->request->getVar('lead_date'),
            'name'      => $this->request->getVar('name'),
            'phone'     => $cleanPhone,
            'city'      => $this->request->getVar('city'),
            'source'    => $this->request->getVar('source'),
            'product'   => $this->request->getVar('product'),
            'notes'     => $this->request->getVar('notes'),
        ];

        $role = session()->get('role');
        if (in_array($role, ['admin', 'cs', 'owner'])) {
            $assignTo = $this->request->getVar('assigned_to');
            if(!empty($assignTo)) {
                $data['assigned_to'] = $assignTo;
            }
        }

        $model->update($id, $data);
        return redirect()->to('/leads')->with('success', 'Data lead berhasil diperbarui!');
    }

// 4. UPDATE STATUS (GESER KARTU + VALIDASI BOOKING PRESISI)
    public function updateStatus()
    {
         if (!$this->request->isAJAX()) return $this->response->setStatusCode(404);
         
         $leadsModel = new LeadsModel();
         $bookingsModel = new BookingsModel(); 
         $db = \Config\Database::connect();

         $id = $this->request->getPost('id');
         $newStatus = $this->request->getPost('status');

         // --- VALIDASI BOOKING GATE (SESUAI DATABASE) ---
         // Jika mau pindah ke 'booking' atau 'closing'
         if (in_array($newStatus, ['booking', 'closing'])) {
            
            // Cek tabel bookings berdasarkan lead_id
            // Kita cari yang payment_status-nya 'approved' (Sesuai Enum di database)
            $validBooking = $bookingsModel->where('lead_id', $id)
                                          ->where('payment_status', 'approved') 
                                          ->first();
            
            if (!$validBooking) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Gagal! Belum ada pengajuan Booking yang disetujui (Approved) oleh Admin.',
                    'token' => csrf_hash()
                ]);
            }
         }
         // -----------------------------

         // Jika lolos validasi (atau status selain booking/closing), Update Status Lead
         $leadsModel->update($id, [
             'status' => $newStatus,
             'last_interaction_at' => date('Y-m-d H:i:s')
         ]);

         // CATAT KE LOG AKTIVITAS (Otomatis)
         $logData = [
            'user_id'    => session()->get('id'),
            'lead_id'    => $id,
            'action'     => 'Status Update', 
            'log_type'   => 'system',       
            'details'    => 'Mengubah status menjadi: ' . strtoupper($newStatus),
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
         ];
         
         $db->table('activity_logs')->insert($logData);

         return $this->response->setJSON(['success' => true, 'token' => csrf_hash()]);
    }
}