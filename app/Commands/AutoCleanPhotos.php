<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ActivityLogModel;

class AutoCleanPhotos extends BaseCommand
{
    // Nama perintah yang nanti diketik di terminal
    protected $group       = 'Maintenance';
    protected $name        = 'clean:photos'; 
    protected $description = 'Menghapus foto aktivitas sales yang sudah kadaluarsa (lebih dari 3 bulan).';

    public function run(array $params)
    {
        $model = new ActivityLogModel();
        
        // 1. Tentukan Batas Waktu (Misal: 90 Hari / 3 Bulan yang lalu)
        // Ubah '-3 months' sesuai kebutuhan Mas Arie
        $cutoffDate = date('Y-m-d H:i:s', strtotime('-3 months'));

        CLI::write("Memulai pembersihan foto sebelum tanggal: " . $cutoffDate, 'yellow');

        // 2. Cari data log yang:
        //    a. Dibuat sebelum tanggal cutoff
        //    b. Punya attachment (tidak null)
        $oldLogs = $model->where('created_at <', $cutoffDate)
                         ->where('attachment !=', null)
                         ->findAll();

        $count = 0;
        $spaceFreed = 0;

        foreach ($oldLogs as $log) {
            $filePath = FCPATH . 'uploads/activities/' . $log['attachment'];

            // Cek apakah fisiknya ada?
            if (file_exists($filePath)) {
                $fileSize = filesize($filePath);
                
                // HAPUS FILE FISIK
                if (unlink($filePath)) {
                    $count++;
                    $spaceFreed += $fileSize;

                    // 3. Update Database (Opsional)
                    // Kita set kolom attachment jadi NULL dan beri catatan
                    // Supaya di aplikasi tidak error "Image Not Found", tapi tahu kalau sudah dihapus
                    $model->update($log['id'], [
                        'attachment' => null, 
                        'details'    => $log['details'] . ' (Foto expired & dihapus otomatis oleh sistem)'
                    ]);
                }
            } else {
                // Kalau file fisik sudah hilang duluan, bersihkan databasenya saja
                $model->update($log['id'], ['attachment' => null]);
            }
        }

        // Konversi byte ke MB agar mudah dibaca
        $mbFreed = number_format($spaceFreed / 1024 / 1024, 2);

        CLI::write("------------------------------------------------", 'green');
        CLI::write("SUKSES! Berhasil menghapus $count foto.", 'green');
        CLI::write("Total ruang penyimpanan yang dihemat: $mbFreed MB", 'green');
        CLI::write("------------------------------------------------", 'green');
    }
}