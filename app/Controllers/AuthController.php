<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class AuthController extends BaseController
{
    public function index()
    {
        // Jika sudah login, lempar langsung ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function loginProcess()
    {
        $session = session();
        $model = new UsersModel();
        
        // Ambil input dari form
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        
        // Cari user berdasarkan username
        $data = $model->where('username', $username)->first();

        if ($data) {
            // Cek apakah user aktif?
            if($data['is_active'] == 0){
                 $session->setFlashdata('error', 'Akun Anda dinonaktifkan. Hubungi Admin.');
                 return redirect()->to('/');
            }

            // Cek Password (Hash Verification)
            $pass = $data['password_hash'];
            // verify password input vs hash di database
            $verify_pass = password_verify($password, $pass); 

            if ($verify_pass) {
                // Password Benar! Simpan data ke SESSION
                $ses_data = [
                    'id'        => $data['id'],
                    'username'  => $data['username'],
                    'full_name' => $data['full_name'],
                    'role'      => $data['role'],
                    'parent_id' => $data['parent_id'], // Penting untuk hirarki SPV/Manager
                    'isLoggedIn'=> TRUE
                ];
                $session->set($ses_data);

                $activityModel = new \App\Models\ActivityLogModel();
                $activityModel->save([
                    'user_id'    => $data['id'],  // <--- GANTI '$user' JADI '$data' & KEY JADI 'user_id'
                    'lead_id'    => null,         // Login tidak berhubungan dengan lead
                    'action'     => 'login',
                    'log_type'   => 'system',     // Tandai sebagai log sistem
                    'details'    => 'User berhasil login (Role: ' . $data['role'] . ')', // Pakai $data juga disini
                    'ip_address' => $this->request->getIPAddress(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // Lempar ke dashboard
                return redirect()->to('/dashboard');
            } else {
                // Password Salah
                $session->setFlashdata('error', 'Password Salah!');
                return redirect()->to('/');
            }
        } else {
            // Username tidak ditemukan
            $session->setFlashdata('error', 'Username tidak ditemukan!');
            return redirect()->to('/');
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/');
    }
}