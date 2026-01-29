<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdsReportModel;

class AdsController extends BaseController
{
    protected $adsModel;

    public function __construct()
    {
        $this->adsModel = new AdsReportModel();
    }

    public function index()
    {
        // 1. Cek Role (Hanya Owner, Admin, Manager)
        $role = session()->get('role');
        if (!in_array($role, ['owner', 'admin', 'manager'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses laporan ads ditolak.');
        }

        // 2. Ambil Filter Tanggal
        $startDate = $this->request->getVar('tgl_mulai') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate   = $this->request->getVar('tgl_akhir') ?? date('Y-m-d');

        // 3. Query Dasar
        // Kita gunakan logika "where" untuk filter
        $this->adsModel->where('tanggal >=', $startDate)
                       ->where('tanggal <=', $endDate);

        // 4. Hitung Summary (Kartu Atas)
        // Kita clone builder agar tidak mengganggu query pagination nanti
        $builderSum = $this->adsModel->builder(); 
        $builderSum->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        
        $sumData = $builderSum->selectSum('biaya')
                              ->selectSum('kontak')
                              ->selectSum('pesan')
                              ->selectSum('klik')
                              ->get()->getRowArray();

        $totalBiaya   = $sumData['biaya'] ?? 0;
        $totalLeads   = ($sumData['kontak'] ?? 0) + ($sumData['pesan'] ?? 0);
        $totalKlik    = $sumData['klik'] ?? 0;
        $avgCPL       = $totalLeads > 0 ? ($totalBiaya / $totalLeads) : 0;

        // 5. Ambil Data Tabel (Dengan Pagination)
        // Reset query filter untuk pagination
        $dataAds = $this->adsModel->where('tanggal >=', $startDate)
                                  ->where('tanggal <=', $endDate)
                                  ->orderBy('tanggal', 'DESC')
                                  ->paginate(20, 'ads'); // 20 data per halaman

        $data = [
            'title'       => 'Laporan Ads Performance',
            'ads'         => $dataAds,
            'pager'       => $this->adsModel->pager,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            // Data Summary
            'totalBiaya'  => $totalBiaya,
            'totalLeads'  => $totalLeads,
            'totalKlik'   => $totalKlik,
            'avgCPL'      => $avgCPL
        ];

        return view('ads/index', $data);
    }
}