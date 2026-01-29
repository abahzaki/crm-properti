<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<div class="container mt-4">

    <div class="mb-4">
        <h3>👋 Halo, <?= session()->get('full_name') ?>!</h3>
        <p class="text-muted">Berikut adalah ringkasan performa properti Anda hari ini.</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white bg-primary bg-gradient h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Total Leads</h6>
                            <h2 class="mb-0 fw-bold"><?= $totalLeads ?></h2>
                        </div>
                        <i class="bi bi-people-fill fs-1 opacity-50"></i>
                    </div>
                    <small class="mt-2 d-block" style="font-size: 11px;">
                        <?= $newLeads ?> leads baru (Cold) belum diproses.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white bg-success bg-gradient h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Sisa Unit (Stok)</h6>
                            <h2 class="mb-0 fw-bold"><?= $availUnits ?></h2>
                        </div>
                        <i class="bi bi-house-check-fill fs-1 opacity-50"></i>
                    </div>
                    <small class="mt-2 d-block" style="font-size: 11px;">
                        Dari total stok unit yang ada.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white bg-danger bg-gradient h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Unit Terjual (Sold)</h6>
                            <h2 class="mb-0 fw-bold"><?= $soldUnits ?></h2>
                        </div>
                        <i class="bi bi-key-fill fs-1 opacity-50"></i>
                    </div>
                    <small class="mt-2 d-block" style="font-size: 11px;">
                        Unit status SOLD / Akad.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-dark bg-warning bg-gradient h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Total Booking Fee</h6>
                            <h4 class="mb-0 fw-bold">Rp <?= number_format($totalRevenue / 1000000, 1, ',', '.') ?> Jt</h4>
                        </div>
                        <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                    </div>
                    <small class="mt-2 d-block" style="font-size: 11px;">
                        Akumulasi uang tanda jadi masuk.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <?php if(in_array(session()->get('role'), ['owner', 'admin', 'manager'])): ?>
<div class="col-md-3">
    <div class="card border-0 shadow-sm text-dark bg-info bg-opacity-10 border border-info h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 text-info fw-bold" style="font-size: 12px;">Biaya Iklan (Bulan Ini)</h6>
                    <h4 class="mb-0 fw-bold text-dark">Rp <?= number_format($adsCost / 1000000, 1, ',', '.') ?> Jt</h4>
                </div>
                <i class="bi bi-megaphone-fill fs-1 text-info opacity-50"></i>
            </div>
            <small class="mt-2 d-block text-muted" style="font-size: 11px;">
                Budget Meta Ads terpakai.
            </small>
        </div>
    </div>
</div>
<?php endif; ?>

    <div class="row">
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Sumber Leads</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div style="width: 300px;">
                        <canvas id="chartSource"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <?php if(in_array($role, ['admin', 'owner', 'manager', 'spv'])): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Top Sales (Jumlah Leads)</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartSales"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. KONFIGURASI CHART SUMBER LEADS (PIE)
    const ctxSource = document.getElementById('chartSource');
    
    // Ambil data dari Controller PHP
    const sourceLabels = <?= $sourceLabels ?>; 
    const sourceData   = <?= $sourceData ?>;

    new Chart(ctxSource, {
        type: 'doughnut', // Bisa ganti 'pie'
        data: {
            labels: sourceLabels,
            datasets: [{
                label: 'Jumlah Leads',
                data: sourceData,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 2. KONFIGURASI CHART TOP SALES (BAR) - Jika elemennya ada
    const ctxSales = document.getElementById('chartSales');
    if(ctxSales) {
        const salesLabels = <?= $salesLabels ?>;
        const salesData   = <?= $salesData ?>;

        new Chart(ctxSales, {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Total Leads Ditangani',
                    data: salesData,
                    backgroundColor: '#4e73df',
                    borderColor: '#4e73df',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
</script>
<?= $this->endSection(); ?>