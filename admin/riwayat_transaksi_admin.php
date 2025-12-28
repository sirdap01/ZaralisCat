<?php
include "koneksi.php";

/* ==========================
   FILTER STATUS
========================== */
$status = "";
$where_status = "";

if (!empty($_GET['status'])) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where_status = "AND status='$status'";
}

/* ==========================
   FILTER TANGGAL
========================== */
$tanggal = "";
$where_date = "";

if (!empty($_GET['tanggal'])) {
    $tanggal = mysqli_real_escape_string($conn, $_GET['tanggal']);
    $where_date = "AND DATE(tanggal_pesanan)='$tanggal'";
}

/* ==========================
   FILTER METODE PEMBAYARAN
========================== */
$metode = "";
$where_metode = "";

if (!empty($_GET['metode'])) {
    $metode = mysqli_real_escape_string($conn, $_GET['metode']);
    $where_metode = "AND metode_pembayaran='$metode'";
}

/* ==========================
   QUERY TRANSAKSI
========================== */
$query = mysqli_query($conn, "
    SELECT * FROM pesanan 
    WHERE 1=1 $where_status $where_date $where_metode
    ORDER BY id_pesanan DESC
");

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

/* ==========================
   STATISTIK TRANSAKSI
========================== */
$total_transaksi = mysqli_num_rows($query);

// GLOBAL STATISTICS (Always show all data - tidak terpengaruh filter)
$global_stat_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as jumlah_global,
        SUM(CASE WHEN status='Lunas' THEN 1 ELSE 0 END) as jumlah_lunas_global,
        SUM(CASE WHEN status='Lunas' THEN total_harga ELSE 0 END) as pendapatan_global
    FROM pesanan
");

if (!$global_stat_query) {
    die("Query error: " . mysqli_error($conn));
}

$global_stat = mysqli_fetch_assoc($global_stat_query);
$jumlah_global = $global_stat['jumlah_global'] ?? 0;
$jumlah_lunas_global = $global_stat['jumlah_lunas_global'] ?? 0;
$pendapatan_global = $global_stat['pendapatan_global'] ?? 0;

// FILTERED STATISTICS (Based on selected filters)
$stat_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as jumlah,
        SUM(total_harga) as total_pendapatan,
        SUM(CASE WHEN status='Lunas' THEN 1 ELSE 0 END) as jumlah_lunas,
        SUM(CASE WHEN status='Lunas' THEN total_harga ELSE 0 END) as pendapatan_lunas
    FROM pesanan 
    WHERE 1=1 $where_status $where_date $where_metode
");

if (!$stat_query) {
    die("Query error: " . mysqli_error($conn));
}

$stat = mysqli_fetch_assoc($stat_query);
$total_pendapatan = $stat['total_pendapatan'] ?? 0;
$jumlah_lunas = $stat['jumlah_lunas'] ?? 0;
$pendapatan_lunas = $stat['pendapatan_lunas'] ?? 0;

// Check if filter is active
$is_filtered = !empty($_GET['status']) || !empty($_GET['tanggal']) || !empty($_GET['metode']);

/* ==========================
   GET METODE PEMBAYARAN UNIK
========================== */
$metode_query = mysqli_query($conn, "SELECT DISTINCT metode_pembayaran FROM pesanan ORDER BY metode_pembayaran");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Transaksi - Zarali's Catering</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary-purple: #7B2CBF;
    --secondary-gold: #FFD700;
    --accent-purple: #9D4EDD;
    --text-dark: #222;
    --background-light: #FFFDF8;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: var(--background-light);
    min-height: 100vh;
}

/* =====================================
   SIDEBAR
===================================== */
.sidebar {
    width: 280px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    height: 100vh;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 30px 0;
    box-shadow: 4px 0 20px rgba(123, 44, 191, 0.2);
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    text-align: center;
    padding: 0 20px 30px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 20px;
}

.sidebar-logo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid var(--secondary-gold);
    background: white;
    margin: 0 auto 15px;
    display: block;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.sidebar-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--secondary-gold);
    margin-bottom: 5px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.sidebar-subtitle {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 400;
}

.sidebar-menu {
    padding: 10px 0;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    padding: 15px 25px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.sidebar-menu a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: var(--secondary-gold);
    padding-left: 30px;
}

.sidebar-menu a.active {
    background-color: var(--secondary-gold);
    color: var(--primary-purple);
    font-weight: 700;
    border-left-color: var(--primary-purple);
}

.menu-icon {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

/* =====================================
   MAIN CONTENT
===================================== */
.main-content {
    margin-left: 280px;
    padding: 0;
    min-height: 100vh;
}

/* =====================================
   HEADER
===================================== */
.content-header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 30px 40px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.content-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: var(--secondary-gold);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.header-date {
    font-size: 15px;
    font-weight: 500;
    background-color: rgba(255, 255, 255, 0.2);
    padding: 10px 20px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* =====================================
   CONTENT BODY
===================================== */
.content-body {
    padding: 40px;
}

/* =====================================
   STATISTICS CARDS
===================================== */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(123, 44, 191, 0.15);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200px;
    height: 200px;
    border-radius: 50%;
}

.stat-card.all::before {
    background: rgba(123, 44, 191, 0.05);
}

.stat-card.revenue::before {
    background: rgba(0, 200, 83, 0.05);
}

.stat-card.paid::before {
    background: rgba(33, 150, 243, 0.05);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(123, 44, 191, 0.2);
}

.stat-card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    position: relative;
    z-index: 1;
}

.stat-icon {
    font-size: 40px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    color: white;
}

.stat-icon.all {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
}

.stat-icon.revenue {
    background: linear-gradient(135deg, #00C853, #00E676);
}

.stat-icon.paid {
    background: linear-gradient(135deg, #2196F3, #42A5F5);
}

.stat-card h4 {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    margin: 0;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    position: relative;
    z-index: 1;
    margin-top: 10px;
}

.stat-card.all .stat-value {
    color: var(--primary-purple);
}

.stat-card.revenue .stat-value {
    color: #00C853;
}

.stat-card.paid .stat-value {
    color: #2196F3;
}

.stat-subtitle {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
    position: relative;
    z-index: 1;
}

/* =====================================
   FILTER BOX
===================================== */
.filter-box {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    margin-bottom: 30px;
}

.filter-header {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 12px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.btn-filter {
    padding: 12px 30px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
    white-space: nowrap;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(123, 44, 191, 0.4);
}

.btn-reset {
    padding: 12px 25px;
    background: white;
    color: var(--text-dark);
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.btn-reset:hover {
    background: #F5F5F5;
    border-color: #BDBDBD;
}

/* =====================================
   TABLE
===================================== */
.table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    overflow: hidden;
}

.table-header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h3 {
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.result-count {
    background-color: rgba(255, 255, 255, 0.2);
    padding: 6px 18px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background-color: var(--background-light);
}

th {
    padding: 18px 20px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: var(--primary-purple);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--secondary-gold);
}

td {
    padding: 16px 20px;
    font-size: 14px;
    color: var(--text-dark);
    border-bottom: 1px solid #E0E0E0;
}

tbody tr {
    transition: background-color 0.2s ease;
}

tbody tr:hover {
    background-color: #F8F8F8;
}

tbody tr:last-child td {
    border-bottom: none;
}

/* ===== STATUS BADGES ===== */
.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
    text-transform: capitalize;
}

.status-pending {
    background: linear-gradient(135deg, #FFF3CD, #FFE69C);
    color: #856404;
}

.status-proses {
    background: linear-gradient(135deg, #D1ECF1, #BEE5EB);
    color: #0C5460;
}

.status-selesai {
    background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
    color: #155724;
}

.status-lunas {
    background: linear-gradient(135deg, #C8E6C9, #A5D6A7);
    color: #2E7D32;
    box-shadow: 0 2px 8px rgba(46, 125, 50, 0.2);
}

.status-batal {
    background: linear-gradient(135deg, #F8D7DA, #F5C2C7);
    color: #721C24;
}

/* ===== PAYMENT BADGES ===== */
.payment-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    background: #F0F0F0;
    color: #666;
}

.payment-cash {
    background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
    color: #2E7D32;
}

.payment-qris {
    background: linear-gradient(135deg, #E3F2FD, #BBDEFB);
    color: #1565C0;
}

.payment-transfer {
    background: linear-gradient(135deg, #F3E5F5, #E1BEE7);
    color: #6A1B9A;
}

/* ===== ACTION BUTTONS ===== */
.btn-action {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-view {
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    color: white;
}

.btn-view:hover {
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
    transform: translateY(-2px);
}

/* =====================================
   EMPTY STATE
===================================== */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.empty-state h3 {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 10px;
}

.empty-state p {
    font-size: 16px;
    color: #666;
    line-height: 1.6;
}

/* =====================================
   RESPONSIVE DESIGN
===================================== */
@media (max-width: 1024px) {
    .filter-form {
        grid-template-columns: 1fr 1fr;
    }
    
    .filter-actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main-content {
        margin-left: 0;
    }

    .content-header {
        flex-direction: column;
        gap: 15px;
        padding: 25px 20px;
        text-align: center;
    }

    .content-body {
        padding: 20px;
    }

    .stats-container {
        grid-template-columns: 1fr;
    }

    .filter-form {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .btn-filter,
    .btn-reset {
        width: 100%;
    }

    th, td {
        padding: 12px 10px;
        font-size: 12px;
    }
    
    .table-wrapper {
        overflow-x: scroll;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="logo.png" alt="Logo Zarali's Catering" class="sidebar-logo">
        <div class="sidebar-title">Zarali's Catering</div>
        <div class="sidebar-subtitle">Admin Panel</div>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard_admin.php">
            <span class="menu-icon">📊</span>
            <span>Dashboard</span>
        </a>
        <a href="produk_admin.php">
            <span class="menu-icon">🍽️</span>
            <span>Produk</span>
        </a>
        <a href="pesanan_admin.php">
            <span class="menu-icon">📦</span>
            <span>Pesanan</span>
        </a>
        <a href="riwayat_transaksi_admin.php" class="active">
            <span class="menu-icon">💳</span>
            <span>Transaksi</span>
        </a>
        <a href="laporan_penjualan_admin.php">
            <span class="menu-icon">📈</span>
            <span>Laporan</span>
        </a>
        <a href="logout_admin.php">
            <span class="menu-icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    
    <!-- HEADER -->
    <div class="content-header">
        <h2>Riwayat Transaksi</h2>
        <div class="header-date">
            <span>📅</span>
            <span><?= date('d M Y') ?></span>
        </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content-body">

        <!-- GLOBAL STATISTICS (Always visible) -->
        <div style="background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(255,215,0,0.05)); border-radius: 15px; padding: 25px; margin-bottom: 30px; border: 2px solid var(--secondary-gold); box-shadow: 0 4px 15px rgba(255,215,0,0.2);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <span style="font-size: 28px;">🌐</span>
                <h3 style="font-size: 20px; font-weight: 700; color: var(--primary-purple); margin: 0;">
                    Statistik Keseluruhan
                </h3>
                <span style="background: var(--secondary-gold); color: var(--primary-purple); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-left: auto;">
                    GLOBAL
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="font-size: 13px; color: #666; margin-bottom: 8px; font-weight: 600;">📊 Total Transaksi</div>
                    <div style="font-size: 32px; font-weight: 700; color: var(--primary-purple);">
                        <?= $jumlah_global ?>
                    </div>
                    <div style="font-size: 11px; color: #999; margin-top: 5px;">Semua waktu</div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="font-size: 13px; color: #666; margin-bottom: 8px; font-weight: 600;">✓ Transaksi Lunas</div>
                    <div style="font-size: 32px; font-weight: 700; color: #2196F3;">
                        <?= $jumlah_lunas_global ?>
                    </div>
                    <div style="font-size: 11px; color: #999; margin-top: 5px;">
                        <?= $jumlah_global > 0 ? round(($jumlah_lunas_global / $jumlah_global) * 100, 1) : 0 ?>% dari total
                    </div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="font-size: 13px; color: #666; margin-bottom: 8px; font-weight: 600;">💰 Total Pendapatan</div>
                    <div style="font-size: 28px; font-weight: 700; color: #00C853;">
                        Rp <?= number_format($pendapatan_global, 0, ',', '.') ?>
                    </div>
                    <div style="font-size: 11px; color: #999; margin-top: 5px;">Dari transaksi lunas</div>
                </div>
            </div>
        </div>

        <?php if ($is_filtered): ?>
        <!-- FILTER ACTIVE INDICATOR -->
        <div style="background: linear-gradient(135deg, #E3F2FD, #BBDEFB); padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #2196F3; display: flex; align-items: center; gap: 15px; box-shadow: 0 2px 10px rgba(33,150,243,0.1);">
            <span style="font-size: 24px;">🔍</span>
            <div style="flex: 1;">
                <strong style="color: #1565C0; font-size: 14px;">Filter Aktif:</strong>
                <span style="color: #424242; font-size: 13px; margin-left: 8px;">
                    Statistik di bawah menampilkan data sesuai filter yang dipilih
                </span>
            </div>
            <a href="riwayat_transaksi_admin.php" style="background: white; color: #2196F3; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; box-shadow: 0 2px 8px rgba(33,150,243,0.2); transition: all 0.3s ease;">
                🔄 Reset Filter
            </a>
        </div>
        <?php endif; ?>

        <!-- FILTERED STATISTICS -->
        <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <h3 style="font-size: 18px; font-weight: 700; color: var(--primary-purple); margin: 0;">
                <?= $is_filtered ? '📊 Statistik Terfilter' : '📊 Statistik' ?>
            </h3>
            <?php if ($is_filtered): ?>
            <span style="background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple)); color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                FILTERED
            </span>
            <?php endif; ?>
        </div>

        <!-- STATISTICS -->
        <div class="stats-container">
            <div class="stat-card all">
                <div class="stat-card-header">
                    <div class="stat-icon all">💳</div>
                    <h4>Total Transaksi</h4>
                </div>
                <div class="stat-value"><?= $total_transaksi ?></div>
                <div class="stat-subtitle">Transaksi sesuai filter yang dipilih</div>
            </div>

            <div class="stat-card paid">
                <div class="stat-card-header">
                    <div class="stat-icon paid">✓</div>
                    <h4>Transaksi Lunas</h4>
                </div>
                <div class="stat-value"><?= $jumlah_lunas ?></div>
                <div class="stat-subtitle">Dari transaksi yang ditampilkan</div>
            </div>

            <div class="stat-card revenue">
                <div class="stat-card-header">
                    <div class="stat-icon revenue">💰</div>
                    <h4>Pendapatan</h4>
                </div>
                <div class="stat-value">
                    Rp <?= number_format($pendapatan_lunas, 0, ',', '.') ?>
                </div>
                <div class="stat-subtitle">Dari transaksi lunas yang ditampilkan</div>
            </div>
        </div>

        <!-- FILTER BOX -->
        <div class="filter-box">
            <div class="filter-header">
                <span>🔍</span>
                <span>Filter Transaksi</span>
            </div>

            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>📊 Status Pesanan</label>
                    <select name="status" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="Pending" <?= ($status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Proses" <?= ($status == 'Proses') ? 'selected' : '' ?>>Proses</option>
                        <option value="Selesai" <?= ($status == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                        <option value="Lunas" <?= ($status == 'Lunas') ? 'selected' : '' ?>>Lunas</option>
                        <option value="Batal" <?= ($status == 'Batal') ? 'selected' : '' ?>>Batal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>📅 Tanggal Pesanan</label>
                    <input 
                        type="date" 
                        name="tanggal" 
                        class="filter-input"
                        value="<?= htmlspecialchars($tanggal) ?>"
                    >
                </div>

                <div class="form-group">
                    <label>💳 Metode Pembayaran</label>
                    <select name="metode" class="filter-select">
                        <option value="">Semua Metode</option>
                        <option value="Tunai" <?= ($metode == 'Tunai') ? 'selected' : '' ?>>Tunai</option>
                        <option value="QRIS" <?= ($metode == 'QRIS') ? 'selected' : '' ?>>QRIS</option>
                        <option value="Transfer" <?= ($metode == 'Transfer') ? 'selected' : '' ?>>Transfer Bank</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">🔍 Tampilkan</button>
                    <a href="riwayat_transaksi_admin.php" class="btn-reset">🔄 Reset</a>
                </div>
            </form>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <div class="table-header">
                <h3>
                    <span>📋</span>
                    <span>Daftar Transaksi</span>
                </h3>
                <div class="result-count"><?= $total_transaksi ?> transaksi</div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Metode</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_transaksi > 0): ?>
                            <?php 
                            mysqli_data_seek($query, 0);
                            while ($row = mysqli_fetch_assoc($query)): 
                                // Determine payment badge class
                                $payment_class = 'payment-badge';
                                if (strtolower($row['metode_pembayaran']) == 'tunai') {
                                    $payment_class .= ' payment-cash';
                                } elseif (strtolower($row['metode_pembayaran']) == 'qris') {
                                    $payment_class .= ' payment-qris';
                                } elseif (strtolower($row['metode_pembayaran']) == 'transfer') {
                                    $payment_class .= ' payment-transfer';
                                }
                            ?>
                            <tr>
                                <td><strong>#<?= $row['id_pesanan'] ?></strong></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_pesanan'])) ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                                <td>
                                    <span class="<?= $payment_class ?>">
                                        <?= htmlspecialchars($row['metode_pembayaran']) ?>
                                    </span>
                                </td>
                                <td><strong>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($row['status']) ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="detail_pesanan_admin.php?id=<?= $row['id_pesanan'] ?>" class="btn-action btn-view">
                                        👁️ Lihat
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-icon">💳</div>
                                    <h3>Tidak Ada Transaksi</h3>
                                    <p>Belum ada transaksi yang sesuai dengan filter yang dipilih.<br>Coba ubah filter atau reset untuk melihat semua transaksi.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

</body>
</html>