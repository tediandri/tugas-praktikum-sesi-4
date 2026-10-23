<?php
/**
 * config.php — koneksi database (SQLite), helper, dan flash message.
 * Database dibuat otomatis di folder /data saat pertama kali dijalankan.
 */
session_start();

const PRODI  = ['Informatika', 'Sistem Informasi', 'Teknik Elektro', 'Manajemen'];
const STATUS = ['Aktif' => 'success', 'Cuti' => 'warning', 'Nonaktif' => 'secondary'];

// --- Koneksi & skema ---
if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0777, true);
$pdo = new PDO('sqlite:' . __DIR__ . '/data/mahasiswa.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('CREATE TABLE IF NOT EXISTS mahasiswa (
    nim TEXT PRIMARY KEY, nama TEXT NOT NULL, prodi TEXT NOT NULL, status TEXT NOT NULL DEFAULT "Aktif")');

if ((int)$pdo->query('SELECT COUNT(*) FROM mahasiswa')->fetchColumn() === 0) {
    $seed = $pdo->prepare('INSERT INTO mahasiswa VALUES (?,?,?,?)');
    foreach ([['23010101','Naila Putri','Informatika','Aktif'],
              ['23010102','Bima Arya','Sistem Informasi','Aktif'],
              ['23010103','Sari Dewi','Informatika','Cuti']] as $row) $seed->execute($row);
}

// --- Helper ---
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function flash(string $type, string $message): void { $_SESSION['flash'] = compact('type', 'message'); }

function pull_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_valid(): bool {
    return hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '');
}
