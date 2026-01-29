<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClosingsModel;
use App\Models\BookingsModel;
use App\Models\UnitModel;
use App\Models\LeadsModel;

class ClosingsController extends BaseController
{
    protected $closingsModel;
    protected $bookingsModel;
    protected $unitModel;
    protected $leadsModel;
    protected $db;

    public function __construct()
    {
        $this->closingsModel = new ClosingsModel();
        $this->bookingsModel = new BookingsModel();
        $this->unitModel     = new UnitModel();
        $this->leadsModel    = new LeadsModel();
        $this->db            = \Config\Database::connect();
    }

    // 1. DASHBOARD APPROVAL (Untuk Owner)
    public function index()
    {
        // Hanya Owner/Admin yang boleh lihat list approval
        if (!in_array(session()->get('role'), ['owner', 'admin', 'manager'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses khusus Owner/Manager.');
        }

        $data = [
            'title'    => 'Approval Akad & Closing',
            'closings' => $this->closingsModel->getClosingsLengkap() // Pakai fungsi join yang kita buat di Model tadi
        ];

        return view('closings/index', $data);
    }

    // 2. FORM PENGAJUAN (Dipanggil dari tombol di menu Booking)
    public function new($booking_id)
    {
        $booking = $this->bookingsModel->find($booking_id);
        
        if (!$booking || $booking['payment_status'] != 'approved') {
            return redirect()->back()->with('error', 'Booking belum valid atau belum diapprove.');
        }

        // Cek apakah sudah pernah diajukan sebelumnya?
        $existing = $this->closingsModel->where('booking_id', $booking_id)->first();
        if ($existing) {
            return redirect()->to('/closings')->with('error', 'Booking ini sudah masuk proses closing.');
        }

        // Ambil data unit untuk info harga
        $unit = $this->unitModel->find($booking['unit_id']);

        $data = [
            'title'   => 'Proses Akad (Pemberkasan)',
            'booking' => $booking,
            'unit'    => $unit
        ];

        return view('closings/form', $data);
    }

    // 3. PROSES SIMPAN BERKAS
    public function create()
    {
        // Validasi Upload
        if (!$this->validate([
            'akad_date'   => 'required',
            'file_ktp'    => 'uploaded[file_ktp]|max_size[file_ktp,2048]|ext_in[file_ktp,jpg,jpeg,png,pdf]',
            'final_price' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Data tidak lengkap atau format file salah.');
        }

        // Helper Upload Function
        $uploadFile = function($fileInputName) {
            $file = $this->request->getFile($fileInputName);
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/docs', $newName);
                return $newName;
            }
            return null;
        };

        $data = [
            'booking_id'     => $this->request->getPost('booking_id'),
            'akad_date'      => $this->request->getPost('akad_date'),
            'payment_method' => $this->request->getPost('payment_method'),
            'final_price'    => $this->request->getPost('final_price'),
            'file_ktp'       => $uploadFile('file_ktp'),
            'file_npwp'      => $uploadFile('file_npwp'), // Opsional
            'file_kk'        => $uploadFile('file_kk'),   // Opsional
            'file_sp3k'      => $uploadFile('file_sp3k'), // Opsional
            'status'         => 'review' // Default status waiting review
        ];

        $this->closingsModel->insert($data);

        return redirect()->to('/bookings')->with('success', 'Berkas berhasil diupload! Menunggu approval Owner.');
    }

    // 4. PROSES APPROVE (OWNER ONLY)
    public function approve()
    {
        if (session()->get('role') != 'owner') {
            return redirect()->back()->with('error', 'Hanya Owner yang bisa Approve Akad.');
        }

        $closingId = $this->request->getPost('id');
        $closing   = $this->closingsModel->find($closingId);
        $booking   = $this->bookingsModel->find($closing['booking_id']);

        // --- TRANSACTION START ---
        $this->db->transStart();

        // A. Update Status Closing -> Approved
        $this->closingsModel->update($closingId, [
            'status'      => 'approved',
            'approved_by' => session()->get('id'),
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        // B. Update Status Unit -> SOLD (Terkunci Selamanya)
        $this->unitModel->update($booking['unit_id'], [
            'status' => 'sold'
        ]);

        // C. Update Status Lead -> Closing (Agar sales senang laporannya hijau)
        $this->leadsModel->update($booking['lead_id'], [
            'status' => 'closing'
        ]);

        $this->db->transComplete();
        // --- TRANSACTION END ---

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses data. Terjadi kesalahan database.');
        }

        return redirect()->to('/closings')->with('success', 'Selamat! Unit resmi SOLD (Terjual).');
    }

    // 5. PROSES REJECT
    public function reject()
    {
        if (session()->get('role') != 'owner') return redirect()->back();

        $id = $this->request->getPost('id');
        $notes = $this->request->getPost('notes');

        $this->closingsModel->update($id, [
            'status' => 'rejected',
            'notes'  => $notes
        ]);

        return redirect()->to('/closings')->with('success', 'Pengajuan ditolak. Silakan minta admin revisi berkas.');
    }
}