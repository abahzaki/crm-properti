<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UnitModel;
use App\Models\BookingsModel; // Kita butuh ini untuk cek keamanan hapus

class UnitsController extends BaseController
{
    protected $unitModel;
    protected $bookingsModel;

    public function __construct()
    {
        $this->unitModel = new UnitModel();
        $this->bookingsModel = new BookingsModel();
    }

    /**
     * [GET] /units
     * List semua unit
     */
    public function index()
    {
        $data = [
            'title' => 'Stok Unit Properti',
            // Order by Blok dan Nomor agar rapi (misal A1, A2, B1...)
            'units' => $this->unitModel->orderBy('block', 'ASC')->orderBy('unit_number', 'ASC')->findAll()
        ];
        return view('units/index', $data);
    }

    /**
     * [GET] /units/new
     * Form Tambah Unit (Hanya Admin/Owner)
     */
    public function new()
    {
        // Proteksi Role
        if (!in_array(session()->get('role'), ['admin', 'manager', 'owner'])) {
            return redirect()->to('/units')->with('error', 'Akses ditolak.');
        }

        $data = [
            'title' => 'Tambah Unit Baru',
            'unit'  => null // Kosong karena unit baru
        ];
        return view('units/form', $data);
    }

    /**
     * [POST] /units/save
     * Proses Simpan (Create/Update)
     */
    public function save()
    {
        // Proteksi Role
        if (!in_array(session()->get('role'), ['admin', 'manager', 'owner'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Validasi Sederhana
        if (!$this->validate([
            'block'       => 'required',
            'unit_number' => 'required',
            'price'       => 'required|numeric',
            'status'      => 'required'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Cek kembali inputan wajib (Blok, Nomor, Harga).');
        }

        $id = $this->request->getPost('id'); // Ambil ID (jika mode edit)

        $data = [
            'project_name'  => $this->request->getPost('project_name'),
            'block'         => $this->request->getPost('block'),
            'unit_number'   => $this->request->getPost('unit_number'),
            'unit_type'     => $this->request->getPost('unit_type'),
            'land_area'     => $this->request->getPost('land_area'),
            'building_area' => $this->request->getPost('building_area'),
            'price'         => $this->request->getPost('price'),
            'status'        => $this->request->getPost('status'),
            'description'   => $this->request->getPost('description'),
        ];

        if (empty($id)) {
            // Mode Insert
            $this->unitModel->insert($data);
            $msg = 'Unit berhasil ditambahkan.';
        } else {
            // Mode Update
            $this->unitModel->update($id, $data);
            $msg = 'Data unit berhasil diperbarui.';
        }

        return redirect()->to('/units')->with('success', $msg);
    }

    /**
     * [GET] /units/edit/{id}
     * Form Edit Unit
     */
    public function edit($id)
    {
        if (!in_array(session()->get('role'), ['admin', 'manager', 'owner'])) {
            return redirect()->to('/units')->with('error', 'Akses ditolak.');
        }

        $unit = $this->unitModel->find($id);
        if (!$unit) {
            return redirect()->to('/units')->with('error', 'Unit tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Data Unit',
            'unit'  => $unit // Kirim data lama ke form
        ];
        return view('units/form', $data); // Kita pakai view form yang sama (Reusable)
    }

    /**
     * [POST] /units/delete
     * Hapus Unit
     */
    public function delete()
    {
        if (!in_array(session()->get('role'), ['admin', 'manager', 'owner'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $id = $this->request->getPost('unit_id');

        // SAFETY CHECK: Cek apakah unit ini ada di tabel bookings?
        $countBooking = $this->bookingsModel->where('unit_id', $id)->countAllResults();
        
        if ($countBooking > 0) {
            return redirect()->back()->with('error', 'GAGAL! Unit ini tidak bisa dihapus karena ada riwayat transaksi Booking. Silakan ganti statusnya menjadi Sold/Hold saja.');
        }

        $this->unitModel->delete($id);
        return redirect()->to('/units')->with('success', 'Unit berhasil dihapus.');
    }
}