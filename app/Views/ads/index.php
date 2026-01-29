<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<style>
    /* Styling Kustom */
    .card-summary { border: none; border-radius: 12px; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .card-summary:hover { transform: translateY(-3px); }
    .icon-box { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .bg-blue-light { background-color: #e3f2fd; color: #1976d2; }
    .bg-green-light { background-color: #e8f5e9; color: #2e7d32; }
    .bg-purple-light { background-color: #f3e5f5; color: #7b1fa2; }
    .bg-orange-light { background-color: #fff3e0; color: #ef6c00; }
    .col-money { font-family: 'Consolas', monospace; font-weight: 600; color: #444; }
    .col-lead { font-weight: 700; color: #198754; background-color: #f9fff9; }
    .col-msg { font-weight: 700; color: #0d6efd; background-color: #f0f7ff; }
</style>

<div class="container mt-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Ads Performance</h3>
            <small class="text-muted">Pantau efektivitas iklan Meta Ads dari n8n.</small>
        </div>
        
        <form method="GET" class="d-flex gap-2 bg-white p-2 rounded shadow-sm">
            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= $startDate ?>">
            <span class="align-self-center text-muted">-</span>
            <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= $endDate ?>">
            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-funnel"></i> Filter</button>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card-summary p-3 h-100 d-flex align-items-center">
                <div class="icon-box bg-blue-light me-3"><i class="bi bi-wallet2"></i></div>
                <div>
                    <p class="text-muted small mb-1 text-uppercase fw-bold">Total Biaya</p>
                    <h5 class="mb-0 fw-bold">Rp <?= number_format($totalBiaya, 0,',','.') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-summary p-3 h-100 d-flex align-items-center">
                <div class="icon-box bg-green-light me-3"><i class="bi bi-whatsapp"></i></div>
                <div>
                    <p class="text-muted small mb-1 text-uppercase fw-bold">Total Leads (WA)</p>
                    <h5 class="mb-0 fw-bold"><?= number_format($totalLeads) ?></h5>
                    <small class="text-success" style="font-size: 10px;">Kontak + Pesan</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-summary p-3 h-100 d-flex align-items-center">
                <div class="icon-box bg-purple-light me-3"><i class="bi bi-tag"></i></div>
                <div>
                    <p class="text-muted small mb-1 text-uppercase fw-bold">Rata-rata CPL</p>
                    <h5 class="mb-0 fw-bold">Rp <?= number_format($avgCPL, 0,',','.') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-summary p-3 h-100 d-flex align-items-center">
                <div class="icon-box bg-orange-light me-3"><i class="bi bi-mouse"></i></div>
                <div>
                    <p class="text-muted small mb-1 text-uppercase fw-bold">Total Klik</p>
                    <h5 class="mb-0 fw-bold"><?= number_format($totalKlik) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-secondary">Rincian Harian Campaign</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="bg-light text-uppercase text-muted small">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Campaign</th>
                        <th class="text-end">Biaya</th>
                        <th class="text-end">Impresi</th>
                        <th class="text-end">Klik</th>
                        <th class="text-center">CTR</th>
                        <th class="text-end text-primary">LP View</th>
                        
                        <th class="text-end">Kontak</th>
                        <th class="text-end">CPL Kontak</th>
                        <th class="text-end">Pesan</th>
                        <th class="text-end pe-4">CPL Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ads)): ?>
                        <tr><td colspan="11" class="text-center py-5 text-muted">Tidak ada data iklan pada periode ini.</td></tr>
                    <?php else: ?>
                        <?php foreach($ads as $row): 
                            $ctr_color = $row['ctr'] < 1.0 ? 'text-danger' : 'text-dark';
                        ?>
                        <tr>
                            <td class="ps-4 fw-medium text-muted"><?= date('d M', strtotime($row['tanggal'])) ?></td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;" title="<?= $row['nama_campaign'] ?>">
                                    <?= $row['nama_campaign'] ?>
                                </span>
                            </td>
                            <td class="text-end col-money">Rp <?= number_format($row['biaya'], 0,',','.') ?></td>
                            <td class="text-end text-muted"><?= number_format($row['impresi']) ?></td>
                            <td class="text-end"><?= number_format($row['klik']) ?></td>
                            <td class="text-center <?= $ctr_color ?> fw-bold"><?= $row['ctr'] ?>%</td>
                            
                            <td class="text-end fw-bold text-primary bg-light"><?= number_format($row['lp_view']) ?></td>
                            
                            <td class="text-end col-lead"><?= number_format($row['kontak']) ?></td>
                            <td class="text-end small"><?= $row['kontak']>0 ? number_format($row['cpl_kontak'],0,',','.') : '-' ?></td>
                            
                            <td class="text-end col-msg"><?= number_format($row['pesan']) ?></td>
                            <td class="text-end small pe-4"><?= $row['pesan']>0 ? number_format($row['cpl_pesan'],0,',','.') : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            <?= $pager->links('ads', 'default_full') ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>