<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BotKnowledgeModel;
use Smalot\PdfParser\Parser; // Load Library PDF

class KnowledgeBaseController extends BaseController
{
    protected $knowledgeModel;

    public function __construct()
    {
        $this->knowledgeModel = new BotKnowledgeModel();
    }

    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'owner', 'manager'])) return redirect()->to('/dashboard');

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
        // 1. Cek apakah ada file PDF yang diupload
        $file = $this->request->getFile('pdf_file');
        $content = $this->request->getPost('content_text');
        $title   = $this->request->getPost('title');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validasi Tipe File
            if ($file->getMimeType() != 'application/pdf') {
                return redirect()->back()->with('error', 'Hanya file PDF yang diperbolehkan!');
            }

            // Proses Ekstrak Teks PDF
            try {
                $parser = new Parser();
                $pdf    = $parser->parseFile($file->getTempName());
                $extractedText = $pdf->getText(); // Ambil semua teks

                // Gabungkan dengan catatan manual (jika ada)
                $content = $extractedText . "\n\n" . "[Catatan Tambahan]: " . $content;
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal membaca PDF: ' . $e->getMessage());
            }
        }

        if (empty(trim($content))) {
            return redirect()->back()->with('error', 'Konten pengetahuan tidak boleh kosong!');
        }

        // Simpan ke Database
        $this->knowledgeModel->save([
            'title'        => $title,
            'content_text' => $content, // Teks hasil ekstrak PDF masuk sini
            'is_active'    => 1,
            'created_at'   => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/knowledge-base')->with('success', 'Data berhasil ditambahkan!');
    }

    // ... (Fungsi edit, update, delete sama seperti sebelumnya) ...
    public function edit($id) {
        $data = ['title' => 'Edit Hafalan', 'item' => $this->knowledgeModel->find($id)];
        return view('bot/knowledge_form', $data);
    }

    public function update($id) {
        $this->knowledgeModel->update($id, [
            'title'        => $this->request->getPost('title'),
            'content_text' => $this->request->getPost('content_text'),
            'is_active'    => $this->request->getPost('is_active')
        ]);
        return redirect()->to('/knowledge-base')->with('success', 'Data diupdate.');
    }

    public function delete($id) {
        $this->knowledgeModel->delete($id);
        return redirect()->to('/knowledge-base')->with('success', 'Dihapus.');
    }
}