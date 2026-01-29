<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table            = 'units'; // Nama tabel di database
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Kolom yang boleh diisi (Sesuai screenshot & update query sebelumnya)
    protected $allowedFields    = [
        'project_name', 
        'block',          // Kolom baru
        'unit_number', 
        'unit_type',      // Kolom baru
        'land_area',      // Kolom baru
        'building_area',  // Kolom baru
        'price', 
        'status', 
        'description'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}