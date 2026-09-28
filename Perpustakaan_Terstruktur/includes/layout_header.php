<?php
$pageTitle = $pageTitle ?? 'Perpustakaan';
$pageDescription = $pageDescription ?? '';
require_once __DIR__ . '/../config/app.php';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> - Perpustakaan</title>
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <?php include __DIR__ . '/sidebar.php'; ?>
    </aside>
    <main class="main">
        <header class="topbar">
            <button class="menu-btn" type="button" onclick="toggleSidebar()" aria-label="Buka menu">☰</button>
            <div class="topbar-title">
                <h2><?= e($pageTitle) ?></h2>
                <?php if ($pageDescription): ?><p class="muted"><?= e($pageDescription) ?></p><?php endif; ?>
            </div>
            <div class="user-pill">
                <span class="user-dot"></span>
                <?= e($_SESSION['nama']) ?>
                <span class="user-level"><?= e(ucfirst($_SESSION['level'])) ?></span>
            </div>
        </header>
