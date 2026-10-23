<?php
require __DIR__ . '/config.php';

// Mode ubah jika ada ?nim=
$editNim = $_GET['nim'] ?? null;
$data = ['nim' => '', 'nama' => '', 'prodi' => '', 'status' => 'Aktif'];
if ($editNim !== null) {
    $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE nim = ?');
    $stmt->execute([$editNim]);
    $data = $stmt->fetch();
    if (!$data) { flash('danger', 'Data mahasiswa tidak ditemukan.'); header('Location: index.php'); exit; }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { flash('danger', 'Permintaan tidak valid. Coba lagi.'); header('Location: index.php'); exit; }

    $data = [
        'nim'    => trim($_POST['nim'] ?? ''),
        'nama'   => trim($_POST['nama'] ?? ''),
        'prodi'  => $_POST['prodi'] ?? '',
        'status' => $_POST['status'] ?? 'Aktif',
    ];

    // --- Validasi sisi server ---
    if (!preg_match('/^\d{8}$/', $data['nim'])) $errors['nim'] = 'NIM harus 8 digit angka.';
    else {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM mahasiswa WHERE nim = ? AND nim <> ?');
        $cek->execute([$data['nim'], $editNim ?? '']);
        if ($cek->fetchColumn() > 0) $errors['nim'] = 'NIM sudah terdaftar.';
    }
    if (mb_strlen($data['nama']) < 3) $errors['nama'] = 'Nama minimal 3 karakter.';
    if (!in_array($data['prodi'], PRODI, true)) $errors['prodi'] = 'Pilih salah satu program studi.';
    if (!isset(STATUS[$data['status']])) $errors['status'] = 'Status tidak valid.';

    if (!$errors) {
        if ($editNim !== null) {
            $pdo->prepare('UPDATE mahasiswa SET nim=?, nama=?, prodi=?, status=? WHERE nim=?')
                ->execute([$data['nim'], $data['nama'], $data['prodi'], $data['status'], $editNim]);
            flash('success', "Data {$data['nama']} berhasil diperbarui.");
        } else {
            $pdo->prepare('INSERT INTO mahasiswa VALUES (?,?,?,?)')
                ->execute([$data['nim'], $data['nama'], $data['prodi'], $data['status']]);
            flash('success', "Data {$data['nama']} berhasil disimpan. Mahasiswa sudah muncul di daftar.");
        }
        header('Location: index.php'); exit;
    }
}

$isEdit = $editNim !== null;
$title = $isEdit ? 'Ubah Mahasiswa' : 'Tambah Mahasiswa';
$cls = fn(string $f) => isset($errors[$f]) ? ' is-invalid' : '';
require __DIR__ . '/partials/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="index.php">Daftar mahasiswa</a></li>
  <li class="breadcrumb-item active" aria-current="page"><?= e($title) ?></li>
</ol></nav>

<div class="row g-4">
  <div class="col-12 col-lg-7 col-xl-6">
    <div class="card"><div class="card-body p-4">
      <h1 class="h4 fw-bold mb-1"><?= e($title) ?></h1>
      <p class="text-secondary mb-4">Kolom bertanda <span class="text-danger">*</span> wajib diisi.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i>Data belum lengkap. Periksa kolom yang ditandai merah.</div>
      <?php endif; ?>

      <form method="post" class="needs-validation" novalidate>
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="mb-3">
          <label for="nim" class="form-label fw-medium">NIM <span class="text-danger">*</span></label>
          <input type="text" id="nim" name="nim" value="<?= e($data['nim']) ?>" class="form-control<?= $cls('nim') ?>"
                 placeholder="Contoh: 23010101" inputmode="numeric" pattern="\d{8}" maxlength="8" required aria-describedby="nimHelp">
          <div class="invalid-feedback"><?= e($errors['nim'] ?? 'NIM harus 8 digit angka.') ?></div>
          <div id="nimHelp" class="form-text">8 digit angka, sesuai kartu mahasiswa.</div>
        </div>

        <div class="mb-3">
          <label for="nama" class="form-label fw-medium">Nama lengkap <span class="text-danger">*</span></label>
          <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>" class="form-control<?= $cls('nama') ?>"
                 placeholder="Masukkan nama sesuai identitas" minlength="3" required>
          <div class="invalid-feedback"><?= e($errors['nama'] ?? 'Nama minimal 3 karakter.') ?></div>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-12 col-sm-7">
            <label for="prodi" class="form-label fw-medium">Program studi <span class="text-danger">*</span></label>
            <select id="prodi" name="prodi" class="form-select<?= $cls('prodi') ?>" required>
              <option value="">Pilih program studi</option>
              <?php foreach (PRODI as $p): ?><option <?= $data['prodi'] === $p ? 'selected' : '' ?>><?= e($p) ?></option><?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= e($errors['prodi'] ?? 'Pilih salah satu program studi.') ?></div>
          </div>
          <div class="col-12 col-sm-5">
            <label for="status" class="form-label fw-medium">Status</label>
            <select id="status" name="status" class="form-select">
              <?php foreach (STATUS as $s => $_): ?><option <?= $data['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
          <a href="index.php" class="btn btn-outline-secondary">Batal</a>
          <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Simpan perubahan' : 'Simpan data' ?></button>
        </div>
      </form>
    </div></div>
  </div>

  <aside class="col-12 col-lg-5 col-xl-4">
    <div class="card bg-success-subtle"><div class="card-body">
      <h2 class="h6 fw-bold"><i class="bi bi-lightbulb me-1"></i>Panduan singkat</h2>
      <ul class="small mb-0 ps-3">
        <li>NIM tidak boleh sama dengan data yang sudah ada.</li>
        <li>Tulis nama sesuai identitas resmi.</li>
        <li>Pilih status <em>Cuti</em> bila mahasiswa sedang tidak aktif kuliah sementara.</li>
        <li>Setelah disimpan, Anda akan kembali ke daftar dengan notifikasi.</li>
      </ul>
    </div></div>
  </aside>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
