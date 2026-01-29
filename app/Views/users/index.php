<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Manajemen User / Tim</h4>
        <p class="text-muted mb-0">Kelola akun Sales, SPV, CS, dan Manager.</p>
    </div>
    <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah User
    </button>
</div>

<div class="card border-0 shadow-sm p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="bg-light">
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Atasan (Parent)</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-weight:bold;">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <span class="fw-bold d-block"><?= $u['full_name'] ?></span>
                                <small class="text-muted">@<?= $u['username'] ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php 
                            $badge = 'bg-secondary';
                            if($u['role'] == 'admin' || $u['role'] == 'owner') $badge = 'bg-dark';
                            if($u['role'] == 'cs') $badge = 'bg-info text-dark';
                            if($u['role'] == 'spv') $badge = 'bg-primary';
                            if($u['role'] == 'marketing') $badge = 'bg-success';
                        ?>
                        <span class="badge <?= $badge ?> text-uppercase"><?= $u['role'] ?></span>
                    </td>
                    <td>
                        <?php 
                            $parentName = '-';
                            if(!empty($u['parent_id'])) {
                                foreach($parents as $p) {
                                    if($p['id'] == $u['parent_id']) {
                                        $parentName = $p['full_name'];
                                        break;
                                    }
                                }
                            }
                            echo '<small class="text-muted">'.$parentName.'</small>';
                        ?>
                    </td>
                    <td>
                        <?php if(!empty($u['phone_number'])): ?>
                            <small class="text-muted"><i class="bi bi-whatsapp text-success"></i> <?= $u['phone_number'] ?></small>
                        <?php else: ?>
                            <small class="text-muted">-</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($u['is_active']): ?>
                            <span class="badge bg-light text-success border border-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-light text-danger border border-danger">Non-Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light text-primary" onclick="openEditUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAddUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/users/save" method="post">
                <?= csrf_field() ?>
                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">No. WhatsApp</label>
                        <input type="number" name="phone_number" class="form-control" placeholder="08123456789">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Role</label>
                            <select name="role" id="role_add" class="form-select" onchange="toggleParentInput('add')" required>
                                <option value="marketing">Marketing</option>
                                <option value="spv">SPV (Supervisor)</option>
                                <option value="cs">CS</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="text" name="password" class="form-control" placeholder="123456" required>
                        </div>
                    </div>

                    <div class="mb-3" id="parent_input_add" style="display:block;"> 
                        <label class="form-label small fw-bold text-primary">Atasan Langsung (Lapor Ke Siapa?)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- Pilih Atasan (SPV/Manager) --</option>
                            <?php foreach($parents as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['full_name'] ?> (<?= strtoupper($p['role']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size:10px">*Wajib diisi jika Role adalah Marketing</small>
                    </div>

                </div>
                <div class="modal-footer bg-white">
                    <button type="submit" class="btn btn-primary rounded-pill w-100">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/users/update" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="full_name" id="edit_fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">No. WhatsApp</label>
                        <input type="number" name="phone_number" id="edit_phone" class="form-control">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Role</label>
                            <select name="role" id="edit_role" class="form-select" onchange="toggleParentInput('edit')" required>
                                <option value="marketing">Marketing</option>
                                <option value="spv">SPV (Supervisor)</option>
                                <option value="cs">CS</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="is_active" id="edit_status" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3" id="parent_input_edit" style="display:none;"> 
                        <label class="form-label small fw-bold text-primary">Atasan Langsung (SPV)</label>
                        <select name="parent_id" id="edit_parent_id" class="form-select">
                            <option value="">-- Tidak Ada / Sendiri --</option>
                            <?php foreach($parents as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['full_name'] ?> (<?= strtoupper($p['role']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-warning">
                        <label class="form-label small fw-bold mb-0">Reset Password</label>
                        <input type="text" name="password" class="form-control form-control-sm mt-1" placeholder="Isi jika ingin ganti password...">
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="submit" class="btn btn-primary rounded-pill w-100">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // FUNGSI TOGGLE INPUT PARENT
    // Hanya muncul jika role = marketing
    function toggleParentInput(mode) {
        var role = document.getElementById(mode === 'add' ? 'role_add' : 'edit_role').value;
        var div = document.getElementById(mode === 'add' ? 'parent_input_add' : 'parent_input_edit');
        
        if (role === 'marketing') {
            div.style.display = 'block';
        } else {
            div.style.display = 'none';
        }
    }

    function openEditUser(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_username').value = data.username;
        document.getElementById('edit_fullname').value = data.full_name;
        document.getElementById('edit_phone').value = data.phone_number;
        document.getElementById('edit_role').value = data.role;
        document.getElementById('edit_status').value = data.is_active;
        document.getElementById('edit_parent_id').value = data.parent_id;
        
        // Trigger toggle biar input parent muncul/hilang sesuai role
        toggleParentInput('edit');
        
        var myModal = new bootstrap.Modal(document.getElementById('modalEditUser'));
        myModal.show();
    }
    
    // Jalankan sekali saat load page untuk form add
    document.addEventListener("DOMContentLoaded", function() {
        toggleParentInput('add');
    });
</script>
<?= $this->endSection() ?>