<?php namespace App\Models;

use CodeIgniter\Model;

class BotKnowledgeModel extends Model
{
    protected $table            = 'bot_knowledge';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['title', 'content_text', 'is_active', 'created_at'];

    // Dates
    protected $useTimestamps = false; // Kita handle manual saja biar simpel atau ubah true jika kolomnya ada
}