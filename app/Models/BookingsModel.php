<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingsModel extends Model
{
    protected $table            = 'bookings'; // Sesuai nama tabel
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'lead_id', 
        'unit_id', 
        'marketing_id',     
        'booking_fee', 
        'agreed_price',     
        'proof_image',      
        'payment_status',   
        'approved_by', 
        'approved_at', 
        'rejection_reason'
    ];

    protected $useTimestamps = true;

    // Helper untuk mengambil data lengkap (Join)
    public function getBookingsLengkap($id = null)
    {
        $builder = $this->builder();
        $builder->select('
            bookings.*, 
            units.block, units.unit_number, units.unit_type, units.project_name,
            leads.name as lead_name, leads.phone as lead_phone,
            users.full_name as marketing_name
        ');
        
        $builder->join('units', 'units.id = bookings.unit_id');
        $builder->join('leads', 'leads.id = bookings.lead_id');
        $builder->join('users', 'users.id = bookings.marketing_id'); 
        
        $builder->orderBy('bookings.created_at', 'DESC');

        if ($id) {
            return $builder->where('bookings.id', $id)->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }
}