<?php

namespace App\Controllers;

use App\Models\LeadsModel;
use App\Models\UnitModel;
use App\Models\BookingsModel;
use App\Models\UsersModel;
use App\Models\AdsReportModel; // Pastikan ini ada

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $role = session()->get('role');
        $userId = session()->get('id');

        // 1. LOAD SEMUA MODEL DI SINI
        // -------------------------------------------------
        $leadModel = new LeadsModel();
        $unitModel = new UnitModel();
        $bookingModel = new BookingsModel();
        $userModel = new UsersModel();
        $adsModel = new AdsReportModel(); // Load Ads Model sekalian

        // 2. LOGIKA DATA CARD STATISTIK
        // -------------------------------------------------
        
        // A. Total Leads (Filter jika Sales)
        if($role == 'marketing') {
            $totalLeads = $leadModel->where('assigned_to', $userId)->countAllResults();
            $newLeads   = $leadModel->where('assigned_to', $userId)->where('status', 'cold')->countAllResults();
        } else {
            $totalLeads = $leadModel->countAll();
            $newLeads   = $leadModel->where('status', 'cold')->countAllResults();
        }

        // B. Stok Unit (Semua bisa lihat stok global)
        $totalUnits = $unitModel->countAll();
        $soldUnits  = $unitModel->where('status', 'sold')->countAllResults();
        $availUnits = $unitModel->where('status', 'available')->countAllResults();

        // C. Omzet / Booking Fee
        $bookingBuilder = $bookingModel->selectSum('booking_fee');
        if($role == 'marketing') {
            $bookingBuilder->where('marketing_id', $userId);
        }
        $revenueData = $bookingBuilder->get()->getRow();
        $totalRevenue = $revenueData->booking_fee ?? 0;

        // D. DATA ADS SPEND (Bulan Ini) - PINDAHKAN KE SINI (SEBELUM return)
        $adsCost = 0;
        // Hanya Owner/Admin/Manager yang bisa lihat biaya iklan
        if(in_array($role, ['owner', 'admin', 'manager'])) {
            $currentMonthStart = date('Y-m-01');
            $currentMonthEnd   = date('Y-m-t');
            
            $adsData = $adsModel->selectSum('biaya')
                                ->where('tanggal >=', $currentMonthStart)
                                ->where('tanggal <=', $currentMonthEnd)
                                ->get()->getRow();
                                
            $adsCost = $adsData->biaya ?? 0;
        }


        // 3. LOGIKA CHART
        // -------------------------------------------------
        
        // Chart 1: Sumber Leads
        $sourceBuilder = $leadModel->select('source, COUNT(id) as total')->groupBy('source');
        if($role == 'marketing') {
            $sourceBuilder->where('assigned_to', $userId);
        }
        $sourceStats = $sourceBuilder->findAll();

        $chartSourceLabel = [];
        $chartSourceData  = [];
        foreach($sourceStats as $row) {
            $chartSourceLabel[] = $row['source'];
            $chartSourceData[]  = $row['total'];
        }

        // Chart 2: Top Sales (Khusus Admin/Owner)
        $topSalesLabels = [];
        $topSalesData   = [];

        if(in_array($role, ['admin', 'owner', 'manager', 'spv'])) {
            $salesStats = $db->table('leads')
                             ->select('users.full_name, COUNT(leads.id) as total_leads')
                             ->join('users', 'users.id = leads.assigned_to')
                             ->groupBy('leads.assigned_to')
                             ->orderBy('total_leads', 'DESC')
                             ->limit(5)
                             ->get()->getResultArray();
            
            foreach($salesStats as $row) {
                $topSalesLabels[] = $row['full_name'];
                $topSalesData[]   = $row['total_leads'];
            }
        }

        // 4. PACKING DATA KE VIEW
        // -------------------------------------------------
        $data = [
            'title'        => 'Dashboard Utama',
            'role'         => $role,
            // Data Cards
            'totalLeads'   => $totalLeads,
            'newLeads'     => $newLeads,
            'soldUnits'    => $soldUnits,
            'availUnits'   => $availUnits,
            'totalRevenue' => $totalRevenue,
            'adsCost'      => $adsCost, // <--- Variabel ini sekarang sudah aman dikirim
            // Data Charts
            'sourceLabels' => json_encode($chartSourceLabel),
            'sourceData'   => json_encode($chartSourceData),
            'salesLabels'  => json_encode($topSalesLabels),
            'salesData'    => json_encode($topSalesData)
        ];

        // 5. RETURN VIEW (PALING BAWAH)
        return view('dashboard/index', $data);
    }
}