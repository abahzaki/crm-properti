<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* CSS KANBAN */
    .kanban-container { display: flex; overflow-x: auto; padding-bottom: 20px; gap: 20px; height: calc(100vh - 220px); }
    .kanban-column { min-width: 300px; width: 300px; background-color: #f4f6f9; border-radius: 8px; display: flex; flex-direction: column; border: 1px solid #e9ecef; }
    .kanban-header { padding: 15px; font-weight: 600; border-bottom: 1px solid #e9ecef; background: #fff; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .kanban-body { padding: 10px; overflow-y: auto; flex: 1; }
    
    .lead-card { background: white; padding: 15px; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 10px; border-left: 4px solid transparent; cursor: grab; transition: transform 0.2s; position: relative; }
    .lead-card:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    /* Warna Status */
    .border-cold { border-left-color: #6c757d; }
    .border-warm { border-left-color: #ffc107; }
    .border-survey { border-left-color: #0d6efd; }
    .border-booking { border-left-color: #198754; }
    .border-closing { border-left-color: #6f42c1; }

    /* Tombol Edit Kecil di Kartu */
    .btn-edit-card { position: absolute; top: 10px; right: 10px; color: #adb5bd; cursor: pointer; }
    .btn-edit-card:hover { color: #0d6efd; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Gagal!</strong> <?= session()->getFlashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Berhasil!</strong> <?= session()->getFlashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center justify-content-between">
            
            <div class="col-md-3">
                <h4 class="fw-bold mb-0">
                    Pipeline Leads 
                    <span class="badge bg-primary rounded-pill fs-6 ms-1 align-middle"><?= $totalLeads ?></span>
                </h4>
                <?php if($keyword): ?>
                    <small class="text-success"><i class="bi bi-search"></i> Hasil pencarian: "<?= esc($keyword) ?>"</small>
                <?php else: ?>
                    <small class="text-muted">Periode: <?= date('d M', strtotime($startDate)) ?> - <?= date('d M Y', strtotime($endDate)) ?></small>
                <?php endif; ?>
            </div>

            <div class="col-md-9">
                <form action="" method="get">
                    <div class="d-flex flex-wrap justify-content-md-end gap-2">
                        
                        <div class="input-group" style="max-width: 250px;">
                            <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Cari Nama / No HP..." value="<?= esc($keyword) ?>">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            <?php if($keyword): ?>
                                <a href="/leads" class="btn btn-sm btn-outline-danger" title="Reset"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </div>

                        <div class="vr mx-1 d-none d-md-block"></div>

                        <?php if(!$keyword): ?>
                        <div class="d-flex gap-1 align-items-center bg-light px-2 rounded border">
                            <input type="date" name="start_date" class="form-control form-control-sm border-0 bg-light py-1" value="<?= $startDate ?>" style="width: 110px;">
                            <span class="text-muted small">-</span>
                            <input type="date" name="end_date" class="form-control form-control-sm border-0 bg-light py-1" value="<?= $endDate ?>" style="width: 110px;">
                            <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0"><i class="bi bi-arrow-right-circle-fill fs-5"></i></button>
                        </div>
                        <?php endif; ?>

                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" id="btn-view-kanban" onclick="switchView('kanban')" title="Tampilan Papan">
                                <i class="bi bi-kanban"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-view-table" onclick="switchView('table')" title="Tampilan Tabel">
                                <i class="bi bi-table"></i>
                            </button>
                        </div>

                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddLead">
                            <i class="bi bi-plus-lg"></i> Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="view-kanban" class="kanban-container">
    <?php 
    function renderCard($lead, $colorClass) {
        $waLink = "https://wa.me/" . $lead['phone'] . "?text=Halo%20" . urlencode($lead['name']);
        // Escape quotes for JS
        $jsName = addslashes($lead['name']); 
        $jsonData = htmlspecialchars(json_encode($lead), ENT_QUOTES, 'UTF-8');

        return '
        <div class="lead-card '.$colorClass.'" data-id="'.$lead['id'].'">
            <i class="bi bi-pencil-square btn-edit-card" onclick="openEditModal('.$jsonData.')"></i>
            <div class="d-flex align-items-center mb-2">
                <span class="badge bg-light text-dark border">'.$lead['source'].'</span>
            </div>
            <h6 class="fw-bold mb-0">'.$lead['name'].'</h6>
            <small class="text-muted d-block mb-1" style="font-size:11px"><i class="bi bi-geo-alt"></i> '.$lead['city'].'</small>
            <p class="text-muted small mb-2 text-truncate">
                <span class="fw-bold text-dark">'.$lead['product'].'</span> - '.$lead['notes'].'
            </p>
            
            <div class="row g-1 mt-2">
                <div class="col-6">
                    <a href="'.$waLink.'" target="_blank" class="btn btn-xs btn-outline-success w-100 py-1" style="font-size:11px">
                        <i class="bi bi-whatsapp"></i> WA
                    </a>
                </div>
                <div class="col-6">
                    <button type="button" onclick="openLogModal('.$lead['id'].', \''.$jsName.'\')" class="btn btn-xs btn-outline-primary w-100 py-1" style="font-size:11px">
                        <i class="bi bi-journal-plus"></i> Lapor
                    </button>
                </div>
            </div>
        </div>';
    }
    
    $statuses = [
        ['key' => 'cold', 'label' => 'Leads Masuk', 'color' => 'text-secondary', 'border' => 'border-cold'],
        ['key' => 'warm', 'label' => 'Sedang Follow Up', 'color' => 'text-warning', 'border' => 'border-warm'],
        ['key' => 'survey', 'label' => 'Janji Survey', 'color' => 'text-primary', 'border' => 'border-survey'],
        ['key' => 'booking', 'label' => 'Booking Fee', 'color' => 'text-success', 'border' => 'border-booking'],
        ['key' => 'closing', 'label' => 'Closing / Akad', 'color' => 'text-white bg-success bg-gradient', 'border' => 'border-closing']
    ];
    ?>

    <?php foreach($statuses as $status): ?>
    <div class="kanban-column">
        <div class="kanban-header <?= $status['color'] ?>">
            <span><?= $status['label'] ?></span>
        </div>
        <div class="kanban-body" data-status="<?= $status['key'] ?>">
            <?php foreach($leads as $l): ?>
                <?php if($l['status'] == $status['key']): ?>
                    <?= renderCard($l, $status['border']) ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="view-table" class="card border-0 shadow-sm p-3" style="display: none;">
    <div class="table-responsive">
        <table id="tableLeads" class="table table-hover align-middle w-100">
            <thead class="bg-light">
                <tr>
                    <th>Tgl Masuk</th>
                    <th>Nama</th>
                    <th>No. HP</th> <th>Kota</th>
                    <th>Produk</th>
                    <th>Status</th>
                    <th>Sales</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($leads as $l): ?>
                <tr>
                    <td><?= date('d/m/y', strtotime($l['lead_date'])) ?></td>
                    <td class="fw-bold">
                        <?= $l['name'] ?>
                        <br><small class="text-muted fw-normal"><?= $l['source'] ?></small>
                    </td>
                    <td><a href="https://wa.me/<?= $l['phone'] ?>" target="_blank" class="text-decoration-none text-success"><i class="bi bi-whatsapp"></i> <?= $l['phone'] ?></a></td>
                    
                    <td><?= $l['city'] ?></td>
                    <td><?= $l['product'] ?></td>
                    <td><span class="badge bg-secondary text-uppercase"><?= $l['status'] ?></span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-light text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width:25px; height:25px; font-size:10px;">
                                <?= substr($l['sales_name'], 0, 1) ?>
                            </div>
                            <small class="fw-bold"><?= $l['sales_name'] ?></small>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-light text-primary" onclick="openEditModal(<?= htmlspecialchars(json_encode($l), ENT_QUOTES, 'UTF-8') ?>)">
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        <button class="btn btn-sm btn-light text-info" onclick="openLogModal(<?= $l['id'] ?>, '<?= addslashes($l['name']) ?>')" title="Catat Aktivitas">
                            <i class="bi bi-journal-plus"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAddLead" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold">Input Lead Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/leads/save" method="post">
                <?= csrf_field() ?>
                <div class="modal-body bg-light">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Tanggal Masuk Leads</label>
                            <input type="date" name="lead_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Sumber Iklan</label>
                            <select name="source" class="form-select">
                                <option value="FB Ads">Facebook/IG Ads</option>
                                <option value="Google Ads">Google Ads</option>
                                <option value="TikTok">TikTok</option>
                                <option value="Walk-in">Datang Langsung</option>
                                <option value="Database">Database Lama</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Nama Prospek</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Pak Budi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Nomor WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">62</span>
                                <input type="number" name="phone" class="form-control border-start-0 ps-0" placeholder="81234567890" required>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                         <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Domisili / Kota</label>
                            <input type="text" name="city" class="form-control" placeholder="Contoh: Jakarta Selatan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Produk Diminati</label>
                            <select name="product" class="form-select">
                            <option value="" selected>-- Pilih Unit Available --</option>
                                <?php if(!empty($units)): ?>
                                 <?php foreach($units as $u): ?>
                                     <option value="Blok <?= $u['block'] ?> - <?= $u['unit_number'] ?>">
                                     Blok <?= $u['block'] ?> - <?= $u['unit_number'] ?> (Tipe <?= $u['unit_type'] ?>)
                                     </option>
                                 <?php endforeach; ?>
                                <?php endif; ?>
                            <option value="Belum Tahu">Belum Tahu / Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Budget 500jt..."></textarea>
                    </div>
                    <?php if(in_array($userRole, ['admin', 'cs', 'owner'])): ?>
                    <div class="alert alert-primary d-flex align-items-center" role="alert">
                        <i class="bi bi-shuffle me-2 fs-4"></i>
                        <div class="w-100">
                            <label class="fw-bold small text-uppercase mb-1">Distribusikan Kepada (Sales):</label>
                            <select name="assigned_to" class="form-select border-primary">
                                <option value="" disabled selected>-- Pilih Sales / Marketing --</option>
                                <?php foreach($salesTeam as $sales): ?>
                                    <option value="<?= $sales['id'] ?>"><?= $sales['full_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-white">
                    <button type="submit" class="btn btn-primary rounded-pill">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditLead" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Edit Data Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/leads/update" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body bg-light">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal</label>
                            <input type="date" name="lead_date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sumber</label>
                            <select name="source" id="edit_source" class="form-select">
                                <option value="FB Ads">FB Ads</option>
                                <option value="Google Ads">Google Ads</option>
                                <option value="TikTok">TikTok</option>
                                <option value="Walk-in">Walk-in</option>
                                <option value="Database">Database</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">WhatsApp</label>
                            <input type="number" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kota</label>
                            <input type="text" name="city" id="edit_city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Produk</label>
                        <select name="product" id="edit_product" class="form-select">
                        <option value="">-- Pilih Unit --</option>
                            <?php foreach($units as $u): ?>
                                <option value="Blok <?= $u['block'] ?> - <?= $u['unit_number'] ?>">
                                Blok <?= $u['block'] ?> - <?= $u['unit_number'] ?>
                                </option>
                            <?php endforeach; ?>
                                <option value="Lainnya">Lainnya / Manual</option>
                        </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catatan</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                    </div>
                    <?php if(in_array($userRole, ['admin', 'cs', 'owner'])): ?>
                    <div class="mb-3">
                         <label class="form-label small fw-bold">Pindahkan Sales (Rotator)</label>
                         <select name="assigned_to" id="edit_assigned_to" class="form-select">
                            <option value="">-- Tetap (Jangan Ubah) --</option>
                            <?php foreach($salesTeam as $sales): ?>
                                <option value="<?= $sales['id'] ?>"><?= $sales['full_name'] ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLogActivity" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-clipboard-data me-2"></i>Catat Aktivitas Sales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/activity/save" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="lead_id" id="log_lead_id">
                
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <small class="text-muted d-block">Target Prospek:</small>
                        <strong id="log_lead_name" class="text-primary fs-5">Nama Lead</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Aktivitas</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="action" id="act_wa" value="WhatsApp" checked>
                            <label class="btn btn-outline-success" for="act_wa"><i class="bi bi-whatsapp"></i> WA</label>

                            <input type="radio" class="btn-check" name="action" id="act_call" value="Call">
                            <label class="btn btn-outline-primary" for="act_call"><i class="bi bi-telephone"></i> Telpon</label>

                            <input type="radio" class="btn-check" name="action" id="act_visit" value="Visit">
                            <label class="btn btn-outline-danger" for="act_visit"><i class="bi bi-geo-alt"></i> Visit</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hasil / Catatan</label>
                        <textarea name="details" class="form-control" rows="3" placeholder="Contoh: Beliau minta dikirim brosur Tipe 36, rencana survey minggu depan." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Bukti Foto (Opsional)</label>
                        <input type="file" name="attachment" class="form-control" accept="image/*">
                        <div class="form-text text-muted">Maksimal 5MB. Foto otomatis dikompres sistem.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<script>
    // DEFINE URL GLOBAL
    const API_URL = "<?= site_url('leads/update-status') ?>";

    // Fungsi Buka Modal Aktivitas
    function openLogModal(id, name) {
        document.getElementById('log_lead_id').value = id;
        document.getElementById('log_lead_name').innerText = name;
        var myModal = new bootstrap.Modal(document.getElementById('modalLogActivity'));
        myModal.show();
    }

    // 1. Inisialisasi DataTables
    $(document).ready(function() {
        $('#tableLeads').DataTable({
            "pageLength": 50,
            "language": { "search": "Cari Nama/Kota:", "lengthMenu": "Tampilkan _MENU_ data" }
        });
        
        // --- LOGIC NOTIFIKASI WA (JIKA ADA FLASH DATA) ---
        <?php if(session()->getFlashdata('wa_notify')): ?>
            var waLink = "<?= session()->getFlashdata('wa_notify') ?>";
            var modalHtml = `
            <div class="modal fade" id="modalWa" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content text-center p-3">
                        <div class="mb-3 text-success"><i class="bi bi-check-circle-fill fs-1"></i></div>
                        <h6 class="fw-bold mb-3">Lead Berhasil Disimpan!</h6>
                        <p class="small text-muted mb-3">Silakan kirim notifikasi ke Sales.</p>
                        <a href="${waLink}" target="_blank" class="btn btn-success w-100 rounded-pill mb-2" onclick="$('#modalWa').modal('hide')">
                            <i class="bi bi-whatsapp me-1"></i> Kirim WA Sekarang
                        </a>
                        <button type="button" class="btn btn-sm btn-light text-muted w-100" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>`;
            $('body').append(modalHtml);
            var waModal = new bootstrap.Modal(document.getElementById('modalWa'));
            waModal.show();
        <?php endif; ?>
    });

    // 2. Switch View (Kanban / Table)
    function switchView(view) {
        if(view === 'kanban') {
            $('#view-kanban').show(); $('#view-table').hide();
            $('#btn-view-kanban').addClass('active'); $('#btn-view-table').removeClass('active');
        } else {
            $('#view-kanban').hide(); $('#view-table').show();
            $('#btn-view-kanban').removeClass('active'); $('#btn-view-table').addClass('active');
        }
    }

    // 3. Populate Edit Modal
    function openEditModal(data) {
        $('#edit_id').val(data.id);
        $('#edit_date').val(data.lead_date);
        $('#edit_name').val(data.name);
        $('#edit_phone').val(data.phone);
        $('#edit_city').val(data.city);
        $('#edit_source').val(data.source);
        $('#edit_product').val(data.product);
        $('#edit_notes').val(data.notes);
        var myModal = new bootstrap.Modal(document.getElementById('modalEditLead'));
        myModal.show();
    }

    // 4. Sortable Kanban (DENGAN LOGGING DEBUG)
    const columns = document.querySelectorAll('.kanban-body');
    columns.forEach((column) => {
        new Sortable(column, {
            group: 'shared',
            animation: 150,
            ghostClass: 'bg-light',
            onEnd: function (evt) {
                if (evt.from === evt.to) return;
                const itemEl = evt.item;
                const newStatus = evt.to.getAttribute('data-status');
                const leadId = itemEl.getAttribute('data-id');
                
                // Update Warna Border (Visual UI)
                itemEl.classList.remove('border-cold', 'border-warm', 'border-survey', 'border-booking', 'border-closing');
                if(newStatus === 'cold') itemEl.classList.add('border-cold');
                if(newStatus === 'warm') itemEl.classList.add('border-warm');
                if(newStatus === 'survey') itemEl.classList.add('border-survey');
                if(newStatus === 'booking') itemEl.classList.add('border-booking');
                if(newStatus === 'closing') itemEl.classList.add('border-closing');

                // Siapkan Data
                const data = new FormData();
                data.append('id', leadId);
                data.append('status', newStatus);
                
                // Ambil CSRF
                const csrfName = '<?= csrf_token() ?>'; 
                const csrfInput = document.querySelector('input[name="'+csrfName+'"]');
                const csrfHash = csrfInput ? csrfInput.value : '';
                data.append(csrfName, csrfHash);

                console.log("Mencoba kirim data ke URL:", API_URL);

                fetch(API_URL, { 
                    method: 'POST', 
                    body: data,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                })
                .then(response => {
                    if (!response.ok) { throw new Error("Gagal menyimpan data."); }
                    return response.json();
                })
                .then(d => { 
                    if(d.success) {
                        // Sukses: Update Token CSRF
                        document.querySelectorAll('input[name="'+csrfName+'"]').forEach(el => {
                            el.value = d.token;
                        });
                    } else {
                        alert('Gagal mengubah status: ' + (d.message || 'Kesalahan tidak diketahui'));
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Terjadi kesalahan koneksi!');
                    location.reload();
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>