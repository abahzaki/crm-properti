<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fas fa-robot me-2"></i>Konfigurasi AI Bot</h3>
</div>

<form action="/bot-settings/update" method="post">
    <input type="hidden" name="id" value="<?= $setting['id'] ?>">

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-plug me-2"></i>Koneksi & Provider</h6>
                </div>
                <div class="card-body">
                    
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="switchBot" <?= $setting['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="switchBot">Status Bot Aktif</label>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">WhatsApp Provider</label>
                        <select name="wa_provider" class="form-select">
                            <option value="fonnte" <?= $setting['wa_provider'] == 'fonnte' ? 'selected' : '' ?>>Fonnte</option>
                            <option value="meta_official" <?= $setting['wa_provider'] == 'meta_official' ? 'selected' : '' ?>>Meta Official API</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WA API Token</label>
                        <input type="password" name="wa_api_token" class="form-control" value="<?= esc($setting['wa_api_token']) ?>">
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">AI Brain Provider</label>
                        <select name="ai_provider" class="form-select">
                            <option value="openai" <?= $setting['ai_provider'] == 'openai' ? 'selected' : '' ?>>OpenAI (ChatGPT)</option>
                            <option value="gemini" <?= $setting['ai_provider'] == 'gemini' ? 'selected' : '' ?>>Google Gemini</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">AI Model Name</label>
                        <input type="text" name="ai_model" class="form-control" value="<?= esc($setting['ai_model']) ?>" placeholder="contoh: gpt-4o-mini">
                        <small class="text-muted">Gunakan: <code>gpt-4o-mini</code> atau <code>gemini-pro</code></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">AI API Key</label>
                        <input type="password" name="api_key" class="form-control" value="<?= esc($setting['api_key']) ?>">
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-brain me-2"></i>Persona & Sifat (Behavior)</h6>
                </div>
                <div class="card-body">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Bot</label>
                        <input type="text" name="bot_name" class="form-control" value="<?= esc($setting['bot_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">System Prompt (Instruksi Dasar)</label>
                        <textarea name="behavior_prompt" class="form-control" rows="12" placeholder="Kamu adalah CS Properti..."><?= esc($setting['behavior_prompt']) ?></textarea>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle"></i> <strong>Tips:</strong> Jelaskan di sini siapa dia, gaya bicaranya (ramah/formal), dan batasan apa yang boleh/tidak boleh dijawab.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>