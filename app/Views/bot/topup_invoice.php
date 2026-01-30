<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden" id="invoiceArea">
                <div class="bg-success text-white text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-wallet2" style="font-size: 3.5rem;"></i>
                    </div>
                    <h4 class="fw-bold">Topup Token Estato</h4>
                    <p class="mb-0 text-white-50">Invoice #INV-<?= date('ymd') ?>-<?= session()->get('id') ?></p>
                </div>

                <div class="card-body p-4 bg-light">
                    
                    <div class="alert alert-warning border-warning d-flex align-items-start shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill me-3 mt-1 text-warning fs-5"></i>
                        <div style="font-size: 0.9rem;">
                            <strong>Instruksi:</strong> Pilih salah satu paket di bawah ini, lalu transfer nominalnya ke rekening yang tertera.
                        </div>
                    </div>

                    <h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">PILIHAN PAKET TOKEN</h6>
                    <div class="table-responsive bg-white rounded shadow-sm border mb-4">
                        <table class="table table-borderless mb-0 align-middle">
                            <thead class="bg-light border-bottom">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-3 py-2">Nama Paket</th>
                                    <th class="text-center">Jumlah Credit</th>
                                    <th class="text-end pe-3">Harga (IDR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3 fw-bold text-secondary">
                                        <i class="bi bi-star me-2"></i> STARTER
                                    </td>
                                    <td class="text-center font-monospace">500</td>
                                    <td class="text-end pe-3 fw-bold">Rp 50.000</td>
                                </tr>
                                <tr class="bg-primary bg-opacity-10 border-start border-primary border-4">
                                    <td class="ps-3 fw-bold text-primary">
                                        <i class="bi bi-briefcase-fill me-2"></i> BUSINESS
                                        <span class="badge bg-danger ms-1" style="font-size: 9px;">POPULAR</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-primary">2.500</td>
                                    <td class="text-end pe-3 fw-bold text-primary">Rp 200.000</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold text-secondary">
                                        <i class="bi bi-gem me-2"></i> SULTAN
                                    </td>
                                    <td class="text-center font-monospace">15.000</td>
                                    <td class="text-end pe-3 fw-bold">Rp 1.000.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center my-4">
                        <h6 class="text-muted text-uppercase fw-bold" style="letter-spacing: 1px;">Rekening Resmi</h6>
                        <h3 class="text-primary fw-bold my-1">Trendi Media Digital</h3>
                    </div>

                    <div class="vstack gap-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white p-2 rounded shadow-sm me-3 border">
                                        <span class="fw-bold text-primary">BCA</span>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">BCA</h6>
                                        <small class="text-muted" style="font-size: 11px;">A.n M. Arie Iswadi</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold font-monospace fs-5">1200515486</div>
                                    <button class="btn btn-sm btn-light border text-primary fw-bold btn-copy py-0 mt-1 d-print-none" data-clipboard-text="1200515486" style="font-size: 11px;">
                                        <i class="bi bi-files me-1"></i> SALIN
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white p-2 rounded shadow-sm me-3 border">
                                        <span class="fw-bold text-warning" style="color: #f37021;">MANDIRI</span>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">MANDIRI</h6>
                                        <small class="text-muted" style="font-size: 11px;">A.n M. Arie Iswadi</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold font-monospace fs-5">1430029991690</div>
                                    <button class="btn btn-sm btn-light border text-primary fw-bold btn-copy py-0 mt-1 d-print-none" data-clipboard-text="1430029991690" style="font-size: 11px;">
                                        <i class="bi bi-files me-1"></i> SALIN
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4 pt-3 border-top">
                        <small class="text-muted fst-italic">
                            *Harap cantumkan User ID (#<?= session()->get('id') ?>) pada berita transfer untuk mempercepat verifikasi.
                        </small>
                    </div>

                </div>
            </div>

            <div class="d-grid gap-2 mt-4 d-print-none">
                
                <?php 
                    $namaUser = urlencode($user['full_name']);
                    $idUser   = session()->get('id');
                    $waText   = "Halo Admin Estato, saya (ID: $idUser - $namaUser) ingin konfirmasi Topup Token.\n\n" . 
                                "Saya memilih Paket: [SEBUTKAN PAKET]\n" . 
                                "Nominal Transfer: Rp ...\n\n" . 
                                "Mohon segera diproses. Terima kasih.";
                    $waLink   = "https://wa.me/62895627470470?text=" . $waText;
                ?>
                
                <a href="<?= $waLink ?>" target="_blank" class="btn btn-success btn-lg fw-bold shadow-sm">
                    <i class="bi bi-whatsapp me-2"></i> KONFIRMASI PEMBAYARAN
                </a>

                <div class="row g-2">
                    <div class="col-6">
                         <button onclick="window.print()" class="btn btn-outline-dark w-100 fw-bold">
                            <i class="bi bi-download me-2"></i> Download Invoice
                        </button>
                    </div>
                    <div class="col-6">
                        <a href="/bot-settings" class="btn btn-outline-secondary w-100 fw-bold">
                            <i class="bi bi-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-copy').forEach(button => {
        button.addEventListener('click', function() {
            let text = this.getAttribute('data-clipboard-text');
            navigator.clipboard.writeText(text).then(() => {
                let originalContent = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check-lg text-success"></i> Disalin!';
                setTimeout(() => { this.innerHTML = originalContent; }, 2000);
            });
        });
    });
</script>

<style>
    @media print {
        /* Sembunyikan elemen dashboard bawaan (Sidebar, Navbar, Footer) */
        .sidebar, .navbar, .main-footer, .d-print-none {
            display: none !important;
        }
        /* Pastikan layout invoice penuh */
        body, .content-wrapper {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .container {
            width: 100% !important;
            max-width: 100% !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        /* Cetak background warna (Header hijau, dll) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<?= $this->endSection() ?>