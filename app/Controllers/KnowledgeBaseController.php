<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BotKnowledgeModel;

class KnowledgeBaseController extends BaseController
{
    protected $knowledgeModel;

    public function __construct()
    {
        $this->knowledgeModel = new BotKnowledgeModel();
    }

    public function index()
    {
        // Cek Role
        if (!in_array(session()->get('role'), ['admin', 'owner', 'manager'])) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Knowledge Base (Otak Bot)',
            'items' => $this->knowledgeModel->orderBy('id', 'DESC')->findAll()
        ];

        return view('bot/knowledge_index', $data);
    }

    public function new()
    {
        return view('bot/knowledge_form', ['title' => 'Tambah Data Hafalan']);
    }

    public function create()
    {
        $this->knowledgeModel->save([
            'title'        => $this->request->getPost('title'),
            'content_text' => $this->request->getPost('content_text'),
            'is_active'    => 1,
            'created_at'   => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/knowledge-base')->with('success', 'Data berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $data = [
            'title' => 'Edit Hafalan',
            'item'  => $this->knowledgeModel->find($id)
        ];

        if (!$data['item']) return redirect()->to('/knowledge-base');

        return view('bot/knowledge_form', $data);
    }

    public function update($id)
    {
        $this->knowledgeModel->update($id, [
            'title'        => $this->request->getPost('title'),
            'content_text' => $this->request->getPost('content_text'),
            'is_active'    => $this->request->getPost('is_active')
        ]);

        return redirect()->to('/knowledge-base')->with('success', 'Data berhasil diupdate!');
    }

    public function delete($id)
    {
        $this->knowledgeModel->delete($id);
        return redirect()->to('/knowledge-base')->with('success', 'Data dihapus.');
    }
}