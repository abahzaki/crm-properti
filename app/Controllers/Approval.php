<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\UnitModel;
use App\Models\LeadModel;

class Approval extends BaseController
{
    protected $bookingModel;
    protected $unitModel;
    protected $leadModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
        $this->unitModel    = new UnitModel();
        $this->leadModel    = new LeadModel();
    }

    /**
     * Logic Approve Booking (Mengunci Unit)
     */
    public function approve($booking_id)
    {
        // 1. Cek Hak Akses (Hanya Admin/Manager/Owner)
        // Sesuaikan role dengan session sistem Anda
        $userRole = session()->get('role'); 
        if (!in_array($userRole, ['admin', 'manager', 'owner'])) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin approval.');
        }

        $booking = $this->bookingModel->find($booking_id);
        
        if (!$booking) {
            return redirect()->back()->with('error', 'Data booking tidak ditemukan.');
        }

        // --- MULAI DATABASE TRANSACTION ---
        // Guna: Jika satu proses gagal, semua dibatalkan (Rollback)
        $this->db->transStart();

        // STEP 1: Cek Ketersediaan Unit (Race Condition Check)
        // Kita kunci baris ini agar tidak dibaca user lain saat proses (Locking)
        // Namun di CI4 standar, kita cukup cek status terkini.
        $unit = $this->unitModel->where('id', $booking['unit_id'])->first();

        if ($unit['status'] != 'available') {
            // Unit sudah keduluan dibooking orang lain saat admin mau klik approve
            $this->db->transRollback();
            return redirect()->back()->with('error', 'GAGAL! Unit ini statusnya bukan Available (Mungkin sudah terjual).');
        }

        // STEP 2: Update Status Booking -> Approved
        $this->bookingModel->update($booking_id, [
            'payment_status' => 'approved',
            'approved_by'    => session()->get('id'),
            'approved_at'    => date('Y-m-d H:i:s')
        ]);

        // STEP 3: Update Status Unit -> Booked (KUNCI UNIT)
        $this->unitModel->update($booking['unit_id'], [
            'status' => 'booked'
        ]);

        // STEP 4: Update Status Lead -> Booking (Opsional, agar funnel sales rapi)
        $this->leadModel->update($booking['lead_id'], [
            'status' => 'booking'
        ]);

        // --- SELESAI TRANSACTION ---
        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem database.');
        }

        return redirect()->back()->with('success', 'Booking Approved! Unit berhasil dikunci.');
    }

    /**
     * Logic Reject Booking
     */
    public function reject()
    {
        // Validasi akses
        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'manager', 'owner'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $bookingId = $this->request->getPost('booking_id');
        $reason    = $this->request->getPost('rejection_reason');

        if(empty($reason)) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        // Update status jadi rejected
        // Unit TIDAK berubah (tetap available)
        $this->bookingModel->update($bookingId, [
            'payment_status'   => 'rejected',
            'approved_by'      => session()->get('id'), // Tetap catat siapa yang reject
            'rejection_reason' => $reason
        ]);

        return redirect()->back()->with('success', 'Booking berhasil ditolak.');
    }
}