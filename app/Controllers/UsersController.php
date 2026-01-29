<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class UsersController extends BaseController
{
    private function checkAccess()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'owner'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function index()
    {
        $this->checkAccess();

        $model = new UsersModel();
        
        // 1. Ambil Semua User
        $users = $model->orderBy('created_at', 'DESC')->findAll();

        // 2. Ambil Daftar Calon Atasan (SPV, Manager, Owner) untuk Dropdown
        // Marketing melapor ke mereka
        $potentialParents = $model->whereIn('role', ['spv', 'manager', 'owner'])
                                  ->orderBy('full_name', 'ASC')
                                  ->findAll();

        $data = [
            'title'     => 'Manajemen User',
            'users'     => $users,
            'parents'   => $potentialParents // Kirim ke View
        ];

        return view('users/index', $data);
    }

    public function save()
    {
        $this->checkAccess();

        if (!$this->validate([
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'full_name' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid / Username sudah ada.');
        }

        // Format HP
        $rawPhone = $this->request->getVar('phone_number');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if(!empty($cleanPhone)) {
             if(substr($cleanPhone, 0, 1) == '0') $cleanPhone = '62' . substr($cleanPhone, 1);
             elseif(substr($cleanPhone, 0, 2) != '62') $cleanPhone = '62' . $cleanPhone;
        }

        // Logic Parent ID (Jika kosong, set null)
        $parentId = $this->request->getVar('parent_id');
        if(empty($parentId)) $parentId = null;

        $model = new UsersModel();
        $model->save([
            'username'      => $this->request->getVar('username'),
            'full_name'     => $this->request->getVar('full_name'),
            'role'          => $this->request->getVar('role'),
            'phone_number'  => $cleanPhone,
            'parent_id'     => $parentId, // Simpan Atasan
            'is_active'     => 1,
            'password_hash' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/users')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function update()
    {
        $this->checkAccess();

        $id = $this->request->getVar('id');
        $model = new UsersModel();
        
        // Format HP
        $rawPhone = $this->request->getVar('phone_number');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if(!empty($cleanPhone)) {
             if(substr($cleanPhone, 0, 1) == '0') $cleanPhone = '62' . substr($cleanPhone, 1);
             elseif(substr($cleanPhone, 0, 2) != '62') $cleanPhone = '62' . $cleanPhone;
        }

        // Logic Parent ID
        $parentId = $this->request->getVar('parent_id');
        if(empty($parentId)) $parentId = null;

        $data = [
            'full_name'    => $this->request->getVar('full_name'),
            'role'         => $this->request->getVar('role'),
            'phone_number' => $cleanPhone,
            'parent_id'    => $parentId, // Update Atasan
            'is_active'    => $this->request->getVar('is_active')
        ];

        // Cek Username
        $oldUser = $model->find($id);
        $newUsername = $this->request->getVar('username');
        if($oldUser['username'] != $newUsername) {
            if($model->where('username', $newUsername)->first()) {
                return redirect()->back()->with('error', 'Username sudah dipakai user lain.');
            }
            $data['username'] = $newUsername;
        }

        // Cek Password
        $newPass = $this->request->getVar('password');
        if(!empty($newPass)) {
            $data['password_hash'] = password_hash($newPass, PASSWORD_DEFAULT);
        }

        $model->update($id, $data);
        return redirect()->to('/users')->with('success', 'Data user berhasil diperbarui.');
    }
}