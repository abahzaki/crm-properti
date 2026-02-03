<?php namespace App\Models;

use CodeIgniter\Model;

class SiteSettingModel extends Model
{
    protected $table = 'site_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['site_name', 'company_name', 'site_logo', 'site_icon', 'footer_text'];
}