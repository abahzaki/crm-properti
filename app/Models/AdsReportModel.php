<?php

namespace App\Models;

use CodeIgniter\Model;

class AdsReportModel extends Model
{
    protected $table            = 'laporan_ads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'tanggal', 'nama_campaign', 'biaya', 'impresi', 'klik', 
        'ctr', 'cpc', 'lp_view', 'add_to_cart', 'kontak', 
        'cpl_kontak', 'pesan', 'cpl_pesan'
    ];
}