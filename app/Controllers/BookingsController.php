<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BookingsModel;
use App\Models\UnitModel;
use App\Models\LeadsModel;

// PENTING: Nama Class harus sama dengan nama File
class BookingsController extends BaseController
{
    protected $bookingsModel;
    protected $unitModel;
    protected $leadsModel;
    protected $db;

    public function __construct()
    {
        $this->bookingsModel = new BookingsModel();
        $this->unitModel     = new UnitModel();
        $this->leadsModel     = new LeadsModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * [GET] /bookings
     * Dashboard List Booking
     */
    public function index()
    {
        $data = [
            'title'    => 'Daftar Booking & Approval',
            'bookings' => $this->bookingsModel->getBookingsLengkap()
        ];
        // View tetap mengarah ke folder yang sama
        return view('bookings/index', $data);
    }

    /**
     * [GET] /bookings/new
     * Form Input Sales
     */
    public function new()
    {
        $data = [
            'title' => 'Input Request Booking',
            'units' => $this->unitModel->where('status', 'available')
                                     ->orderBy('block', 'ASC')
                                     ->orderBy('unit_number', 'ASC')
                                     ->findAll(),
                                     
            'leads' => $this->leadsModel->findAll()
        ];

        return view('bookings/form', $data);
    }

    /**
     * [POST] /bookings
     * Proses Simpan (Create)
     */
    public function create()
    {
        if (!$this->validate([
            'unit_id'      => 'required|integer',
            'lead_id'      => 'required|integer',
            'booking_fee'  => 'required|numeric',
            'agreed_price' => 'required|numeric',
            'proof_image'  => 'uploaded[proof_image]|is_image[proof_image]|max_size[proof_image,2048]'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $unit = $this->unitModel->find($this->request->getPost('unit_id'));
        if ($unit['status'] != 'available') {
            return redirect()->back()->with('error', 'Unit sudah tidak available.');
        }

        $file = $this->request->getFile('proof_image');
        $fileName = $file->getRandomName();
        $file->move('uploads/proofs', $fileName);

        $this->bookingsModel->save([
            'unit_id'        => $this->request->getPost('unit_id'),
            'lead_id'        => $this->request->getPost('lead_id'),
            'marketing_id'   => session()->get('id'),
            'booking_fee'    => $this->request->getPost('booking_fee'),
            'agreed_price'   => $this->request->getPost('agreed_price'),
            'proof_image'    => $fileName,
            'payment_status' => 'pending'
        ]);

        return redirect()->to('/bookings')->with('success', 'Request berhasil dikirim.');
    }

    /**
     * [GET] /bookings/approve/{id}
     * Logic Approval
     */
    public function approve($id)
    {
        if (!in_array(session()->get('role'), ['admin', 'manager', 'owner'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $booking = $this->bookingsModel->find($id);

        $this->db->transStart();

        $unit = $this->unitModel->where('id', $booking['unit_id'])->first();
        if ($unit['status'] != 'available') {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Gagal! Unit sudah terjual.');
        }

        $this->bookingsModel->update($id, [
            'payment_status' => 'approved',
            'approved_by'    => session()->get('id'),
            'approved_at'    => date('Y-m-d H:i:s')
        ]);

        $this->unitModel->update($booking['unit_id'], ['status' => 'booked']);
        
        // Opsional: update status lead
        $this->leadsModel->update($booking['lead_id'], ['status' => 'booking']);

        $this->db->transComplete();

        return redirect()->back()->with('success', 'Booking Approved & Unit Terkunci.');
    }

    /**
     * [POST] /bookings/reject
     * Logic Reject
     */
    public function reject()
    {
        if (!in_array(session()->get('role'), ['admin', 'manager', 'owner'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $id = $this->request->getPost('booking_id');
        $reason = $this->request->getPost('rejection_reason');

        $this->bookingsModel->update($id, [
            'payment_status'   => 'rejected',
            'approved_by'      => session()->get('id'),
            'rejection_reason' => $reason
        ]);

        return redirect()->back()->with('success', 'Booking ditolak.');
    }
}