<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Tambahkan kolom baru ke allowedFields
    protected $allowedFields    = [
        'user_id', 
        'lead_id',      // Baru
        'action', 
        'log_type',     // Baru
        'details', 
        'attachment',   // Baru
        'ip_address', 
        'created_at'
    ];

    protected $useTimestamps = false; // Karena created_at kita set manual/default timestamp di DB
}