<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $table            = 'leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $allowedFields    = [
        'name', 
        'phone', 
        'city',          
        'lead_date',     
        'source', 
        'product',       
        'status',
        'assigned_to', 
        'notes',
        'last_interaction_at'
    ];

    // Aktifkan timestamp otomatis
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Helper untuk mengambil leads berdasarkan user yang login
    public function getLeadsByUser($role, $userId)
    {
        // Jika ADMIN/OWNER/MANAGER: Bisa lihat SEMUA leads
        if (in_array($role, ['admin', 'owner', 'manager'])) {
            return $this->findAll();
        } 
        
        // Jika SPV: Bisa lihat leads milik timnya (Logic Parent ID nanti disini)
        // Sementara kita anggap SPV hanya lihat punya sendiri dulu utk simplifikasi
        
        // Jika MARKETING: Hanya lihat leads miliknya sendiri
        return $this->where('assigned_to', $userId)->findAll();
    }
}