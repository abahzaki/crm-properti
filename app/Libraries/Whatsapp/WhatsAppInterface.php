<?php namespace App\Libraries\WhatsApp;

interface WhatsAppInterface {
    public function sendMessage($target, $message);
    public function parseWebhook($requestData);
}