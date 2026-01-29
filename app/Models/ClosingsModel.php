<?php

namespace App\Models;

use CodeIgniter\Model;

class ClosingsModel extends Model
{
    protected $table            = 'closings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $allowedFields    = [
        'booking_id',
        'akad_date',
        'payment_method',
        'final_price',
        'file_ktp', 'file_npwp', 'file_kk', 'file_sp3k', // File upload
        'status', 
        'approved_by', 
        'approved_at', 
        'notes'
    ];

    protected $useTimestamps = true;

    // Helper untuk mengambil data lengkap (Join ke Booking, Unit, Lead, Sales)
    public function getClosingsLengkap($id = null)
    {
        $builder = $this->builder();
        $builder->select('
            closings.*,
            bookings.booking_fee,
            bookings.created_at as booking_date,
            units.block, units.unit_number, units.project_name,
            leads.name as customer_name, leads.phone as customer_phone,
            users.full_name as sales_name
        ');

        // Join Berantai: Closing -> Booking -> Unit/Lead/User
        $builder->join('bookings', 'bookings.id = closings.booking_id');
        $builder->join('units', 'units.id = bookings.unit_id');
        $builder->join('leads', 'leads.id = bookings.lead_id');
        $builder->join('users', 'users.id = bookings.marketing_id');

        $builder->orderBy('closings.created_at', 'DESC');

        if ($id) {
            return $builder->where('closings.id', $id)->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }
}