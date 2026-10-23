<?php

/** @var string $title */ $flash = pull_flash(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · SIMAHASISWA</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-expand navbar-dark app-nav sticky-top">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="index.php"><i
          class="bi bi-mortarboard-fill me-2"></i>SIMAHASISWA</a>
      <span class="navbar-text d-none d-sm-inline small">BY TEDI ANDRI PERMANA</span>
    </div>
  </nav>
  <main class="container py-4">
    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center"
        role="alert">
        <i
          class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
        <div><?= e($flash['message']) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>