<?php namespace App\Controllers;

use App\Models\SiteSettingModel;

class SiteSettingsController extends BaseController
{
    public function index()
    {
        // Data $site sudah otomatis ada berkat BaseController
        return view('admin/site_settings', [
            'title' => 'Pengaturan Identitas Website'
        ]);
    }

    public function update()
    {
        $model = new SiteSettingModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'site_name'    => $this->request->getPost('site_name'),
            'company_name' => $this->request->getPost('company_name'),
            'footer_text'  => $this->request->getPost('footer_text'),
        ];

        // --- LOGIC UPLOAD LOGO ---
        $fileLogo = $this->request->getFile('site_logo');
        if ($fileLogo && $fileLogo->isValid() && !$fileLogo->hasMoved()) {
            // Beri nama unik agar cache browser refresh
            $newName = 'logo_' . time() . '.' . $fileLogo->getExtension();
            $fileLogo->move('assets/uploads/', $newName);
            $data['site_logo'] = $newName;
        }

        $model->update($id, $data);

        return redirect()->to('/site-settings')->with('success', 'Identitas Website Berhasil Diupdate!');
    }
}