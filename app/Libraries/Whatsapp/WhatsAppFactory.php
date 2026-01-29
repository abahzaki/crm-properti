<?php namespace App\Libraries\WhatsApp;

use App\Libraries\WhatsApp\Drivers\FonnteDriver;

class WhatsAppFactory {
    public static function create($provider, $token) {
        switch ($provider) {
            case 'fonnte':
                return new FonnteDriver($token);
            // case 'meta': return new MetaDriver($token); // Next development
            default:
                throw new \Exception("Provider WhatsApp '$provider' tidak didukung.");
        }
    }
}