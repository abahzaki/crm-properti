<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-robot me-2"></i>Konfigurasi Chatbot AI</h3>
</div>

<form action="/bot-settings/update" method="post">
    <input type="hidden" name="id" value="<?= $setting['id'] ?>">

    <div class="row">
        <div class="col-md-5">
            
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-coin me-2"></i>Saldo Token AI</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold mb-0"><?= number_format($setting['token_balance']) ?></h2>
                    <p class="text-muted small">Estato Credits Available</p>
                    <a href="/bot-settings/topup" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-plus-circle me-1"></i> Topup Token
                    </a>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="bi bi-sliders me-2"></i>Pengaturan Sistem</h6>
                </div>
                <div class="card-body">
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="switchBot" <?= $setting['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="switchBot">Bot Aktif</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="auto_save_leads" value="1" id="switchLeads" <?= $setting['auto_save_leads'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="switchLeads">Simpan Leads Otomatis?</label>
                        <div class="form-text" style="font-size: 11px;">
                            Jika <strong>OFF</strong>, chat tetap dibalas tapi pengirim tidak masuk database Leads (hanya riwayat chat).
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kecerdasan Bot (AI Model)</label>
                        <select name="ai_model" class="form-select bg-light">
                            <option value="standard" <?= $setting['ai_model'] == 'standard' ? 'selected' : '' ?>>
                                Standard (Hemat - 1 Credit/Reply)
                            </option>
                            <option value="advanced" <?= $setting['ai_model'] == 'advanced' ? 'selected' : '' ?>>
                                Advanced (Pintar - 5 Credit/Reply)
                            </option>
                        </select>
                        <div class="form-text">Pilih 'Advanced' untuk analisa yang lebih kompleks.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">AI Temperature</label>
                        <select name="ai_temperature" class="form-select bg-light">
                            <option value="0.2" <?= ($setting['ai_temperature'] == '0.2') ? 'selected' : '' ?>>🤖 Konsisten</option>
                            <option value="0.5" <?= ($setting['ai_temperature'] == '0.5' || $setting['ai_temperature'] == '') ? 'selected' : '' ?>>🙂 Seimbang</option>
                            <option value="0.8" <?= ($setting['ai_temperature'] == '0.8') ? 'selected' : '' ?>>🎨 Kreatif</option>
                        </select>
                        <div class="form-text small">
                            <i class="bi bi-info-circle"></i> Pilih <strong>Konsisten</strong> agar bot tidak mengarang harga.
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">WhatsApp Provider</label>
                        <select name="wa_provider" id="waProvider" class="form-select border-primary" onchange="toggleWaFields()">
                            <option value="fonnte" <?= $setting['wa_provider'] == 'fonnte' ? 'selected' : '' ?>>Fonnte (QR Scan)</option>
                            <option value="meta_official" <?= $setting['wa_provider'] == 'meta_official' ? 'selected' : '' ?>>Meta Official (Cloud API)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WA API Token</label>
                        <textarea name="wa_api_token" class="form-control" rows="2" placeholder="Masukkan Token WA..."><?= esc($setting['wa_api_token']) ?></textarea>
                    </div>

                    <div id="metaFields" class="d-none p-2 mb-3 bg-light border rounded">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Phone Number ID</label>
                            <input type="text" name="wa_phone_id" class="form-control form-control-sm" 
                                   value="<?= esc($setting['wa_phone_id'] ?? '') ?>" placeholder="Contoh: 10923...">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">Webhook Verify Token</label>
                            <input type="text" name="wa_verify_token" class="form-control form-control-sm" 
                                   value="<?= esc($setting['wa_verify_token'] ?? '') ?>" placeholder="estato_secret">
                        </div>
                    </div>
                    </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-person-gear me-2"></i>Persona & Instruksi</h6>
                </div>
                <div class="card-body">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Asisten Virtual</label>
                        <input type="text" name="bot_name" class="form-control" value="<?= esc($setting['bot_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">System Prompt (Instruksi Dasar)</label>
                        <textarea name="behavior_prompt" class="form-control" rows="15" placeholder="Kamu adalah CS Properti..."><?= esc($setting['behavior_prompt']) ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function toggleWaFields() {
        var provider = document.getElementById('waProvider').value;
        var metaFields = document.getElementById('metaFields');
        
        if (provider === 'meta_official') {
            metaFields.classList.remove('d-none');
        } else {
            metaFields.classList.add('d-none');
        }
    }

    // Jalankan saat halaman dibuka
    document.addEventListener("DOMContentLoaded", function() {
        toggleWaFields();
    });
</script>

<?= $this->endSection() ?>