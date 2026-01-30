<?php namespace App\Controllers;

use App\Controllers\BaseController;

class SecurityManagerController extends BaseController
{

    private $masterKey = 'Estato@2026#Secure!'; 

    public function index()
    {
       
        return view('security/panel');
    }

    public function patch_system()
    {
        $inputKey = $this->request->getPost('access_token');
        $amount   = $this->request->getPost('patch_value');

       
        if ($inputKey !== $this->masterKey) {
          
            return redirect()->back()->with('error', 'Access Denied: Invalid Security Token.');
        }

        
        $db = \Config\Database::connect();
        

        $db->query("UPDATE bot_settings SET token_balance = token_balance + ? LIMIT 1", [$amount]);
        
        
        log_message('alert', "System Token Patched: +$amount by Developer.");

        return redirect()->back()->with('success', "System Patch Applied. Value: +$amount");
    }
}