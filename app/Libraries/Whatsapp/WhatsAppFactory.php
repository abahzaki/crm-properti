<?php namespace App\Libraries\WhatsApp;

use App\Libraries\WhatsApp\Drivers\FonnteDriver;
use App\Libraries\WhatsApp\Drivers\MetaDriver; // <--- Pastikan file MetaDriver sudah dibuat

class WhatsAppFactory {
    
    /**
     * Factory untuk membuat instance driver WhatsApp.
     * * @param string $provider  Nama provider (sesuai database: 'fonnte', 'meta_official')
     * @param string $token     API Token
     * @param string|null $phoneId Phone Number ID (Wajib untuk Meta Official)
     */
    public static function create($provider, $token, $phoneId = null) {
        
        switch ($provider) {
            
            case 'fonnte':
                // Fonnte cukup pakai Token
                return new FonnteDriver($token);
            
            case 'meta_official': 
                // Meta Official WAJIB punya Phone ID
                if (empty($phoneId)) {
                    throw new \Exception("Error: Provider 'meta_official' membutuhkan Phone ID (wa_phone_id).");
                }
                // Kirim Token dan Phone ID ke Driver
                return new MetaDriver($token, $phoneId);
            
            // case 'wablas': return new WablasDriver($token); // Persiapan next dev
            
            default:
                throw new \Exception("Provider WhatsApp '$provider' tidak didukung.");
        }
    }
}