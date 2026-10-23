<?php
require __DIR__ . '/config.php';

// --- Hapus data (POST + CSRF) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_valid()) {
        $pdo->prepare('DELETE FROM mahasiswa WHERE nim = ?')->execute([$_POST['nim'] ?? '']);
        flash('success', 'Data mahasiswa berhasil dihapus.');
    } else {
        flash('danger', 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
    header('Location: index.php'); exit;
}

// --- Cari & filter ---
$q = trim($_GET['q'] ?? '');
$st = $_GET['status'] ?? '';
$sql = 'SELECT * FROM mahasiswa WHERE (nim LIKE :q OR nama LIKE :q)';
$params = [':q' => "%$q%"];
if (isset(STATUS[$st])) { $sql .= ' AND status = :s'; $params[':s'] = $st; }
$stmt = $pdo->prepare($sql . ' ORDER BY nim');
$stmt->execute($params);
$rows = $stmt->fetchAll();

$count = fn(string $s) => (int)$pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE status = '$s'")->fetchColumn();
$stats = [
    ['Total mahasiswa', (int)$pdo->query('SELECT COUNT(*) FROM mahasiswa')->fetchColumn(), 'bi-people-fill', 'bg-primary-subtle text-primary'],
    ['Aktif', $count('Aktif'), 'bi-person-check-fill', 'bg-success-subtle text-success'],
    ['Cuti', $count('Cuti'), 'bi-pause-circle-fill', 'bg-warning-subtle text-warning-emphasis'],
];

$title = 'Daftar Mahasiswa';
require __DIR__ . '/partials/header.php';
?>
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
  <div>
    <h1 class="h3 fw-bold mb-1">Daftar Mahasiswa</h1>
    <p class="text-secondary mb-0">Kelola data mahasiswa: cari, tambah, ubah, dan hapus.</p>
  </div>
  <a href="form.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah mahasiswa</a>
</div>

<section class="row g-3 mb-4" aria-label="Ringkasan">
  <?php foreach ($stats as [$label, $val, $icon, $cls]): ?>
    <div class="col-12 col-sm-4">
      <div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon <?= $cls ?>"><i class="bi <?= $icon ?>"></i></div>
        <div><div class="stat-number"><?= $val ?></div><div class="text-secondary small"><?= $label ?></div></div>
      </div></div>
    </div>
  <?php endforeach; ?>
</section>

<div class="card">
  <div class="card-body">
    <form class="row g-2 mb-3" method="get" role="search">
      <div class="col-12 col-md">
        <label for="q" class="visually-hidden">Cari</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="search" id="q" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Cari NIM atau nama">
        </div>
      </div>
      <div class="col-7 col-md-3">
        <label for="status" class="visually-hidden">Status</label>
        <select id="status" name="status" class="form-select">
          <option value="">Semua status</option>
          <?php foreach (STATUS as $s => $_): ?>
            <option <?= $st === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-5 col-md-auto d-flex gap-2">
        <button class="btn btn-outline-primary flex-fill">Terapkan</button>
        <?php if ($q !== '' || $st !== ''): ?><a href="index.php" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
      </div>
    </form>

    <?php if (!$rows): ?>
      <div class="text-center text-secondary py-5">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <p class="mb-3">Tidak ada data yang cocok.</p>
        <a href="form.php" class="btn btn-primary btn-sm">Tambah mahasiswa</a>
      </div>
    <?php else: ?>
      <!-- Tabel: tablet ke atas -->
      <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>NIM</th><th>Nama</th><th>Program studi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $m): ?>
            <tr>
              <td class="font-monospace"><?= e($m['nim']) ?></td>
              <td><div class="d-flex align-items-center gap-2">
                <span class="avatar"><?= e(strtoupper(mb_substr($m['nama'], 0, 1))) ?></span><?= e($m['nama']) ?></div></td>
              <td><?= e($m['prodi']) ?></td>
              <td><span class="badge text-bg-<?= STATUS[$m['status']] ?>"><?= e($m['status']) ?></span></td>
              <td class="text-end text-nowrap">
                <a href="form.php?nim=<?= e($m['nim']) ?>" class="btn btn-sm btn-outline-primary" aria-label="Ubah <?= e($m['nama']) ?>"><i class="bi bi-pencil"></i> Ubah</a>
                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapusModal"
                        data-nim="<?= e($m['nim']) ?>" data-nama="<?= e($m['nama']) ?>" aria-label="Hapus <?= e($m['nama']) ?>"><i class="bi bi-trash"></i> Hapus</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Kartu: layar kecil -->
      <div class="d-md-none d-grid gap-2">
        <?php foreach ($rows as $m): ?>
          <div class="border rounded-3 p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="avatar"><?= e(strtoupper(mb_substr($m['nama'], 0, 1))) ?></span>
              <div class="flex-grow-1"><div class="fw-semibold"><?= e($m['nama']) ?></div>
                <div class="small text-secondary font-monospace"><?= e($m['nim']) ?></div></div>
              <span class="badge text-bg-<?= STATUS[$m['status']] ?>"><?= e($m['status']) ?></span>
            </div>
            <div class="small text-secondary mb-2"><?= e($m['prodi']) ?></div>
            <div class="d-flex gap-2">
              <a href="form.php?nim=<?= e($m['nim']) ?>" class="btn btn-sm btn-outline-primary flex-fill">Ubah</a>
              <button class="btn btn-sm btn-outline-danger flex-fill" data-bs-toggle="modal" data-bs-target="#hapusModal"
                      data-nim="<?= e($m['nim']) ?>" data-nama="<?= e($m['nama']) ?>">Hapus</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="hapusModal" tabindex="-1" aria-labelledby="hapusLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="nim">
      <div class="modal-header"><h2 class="modal-title h5" id="hapusLabel">Hapus data mahasiswa?</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
      <div class="modal-body">Data <strong id="hapusNama"></strong> akan dihapus permanen dan tidak bisa dikembalikan.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-danger">Ya, hapus</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
