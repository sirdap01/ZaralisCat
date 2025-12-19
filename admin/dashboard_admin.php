<?php
include 'koneksi.php';

/* =======================
   TOTAL PENDAPATAN
======================= */
$q_total = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM pesanan");
$data_total = mysqli_fetch_assoc($q_total);
$total_pendapatan = $data_total['total'] ?? 0;

/* =======================
   PESANAN TERBARU (5)
======================= */
$q_pesanan = mysqli_query($conn, "
    SELECT tanggal, nama_pelanggan, status, total_harga 
    FROM pesanan 
    ORDER BY id DESC 
    LIMIT 5
");

/* =======================
   PRODUK TERBARU (5)
======================= */
$q_produk = mysqli_query($conn, "
    SELECT nama, kategori, harga 
    FROM produk 
    ORDER BY id DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - Zarali's Catering</title>
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
   CARDS SECTION
===================================== */
.cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, var(--secondary-gold), #FFC700);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
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
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(255, 215, 0, 0.4);
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
    background-color: var(--primary-purple);
    border-radius: 12px;
    color: white;
}

.stat-card h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--primary-purple);
    margin: 0;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-purple);
    position: relative;
    z-index: 1;
    margin-top: 10px;
}

/* =====================================
   TABLES SECTION
===================================== */
.tables-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 30px;
}

.table-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(123, 44, 191, 0.15);
    overflow: hidden;
}

.table-card-header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.table-card-header h4 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.table-header-icon {
    font-size: 24px;
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
    padding: 15px 20px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: var(--primary-purple);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--secondary-gold);
}

td {
    padding: 15px 20px;
    font-size: 14px;
    color: var(--text-dark);
    border-bottom: 1px solid #E0E0E0;
}

tbody tr {
    transition: background-color 0.2s ease;
}

tbody tr:hover {
    background-color: #F5F5F5;
}

tbody tr:last-child td {
    border-bottom: none;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-pending {
    background-color: #FFF3CD;
    color: #856404;
}

.status-proses {
    background-color: #D1ECF1;
    color: #0C5460;
}

.status-selesai {
    background-color: #D4EDDA;
    color: #155724;
}

/* =====================================
   EMPTY STATE
===================================== */
.empty-table {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.empty-table-icon {
    font-size: 50px;
    margin-bottom: 15px;
}

/* =====================================
   RESPONSIVE DESIGN
===================================== */
@media (max-width: 1200px) {
    .tables-container {
        grid-template-columns: 1fr;
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

    .cards-container {
        grid-template-columns: 1fr;
    }

    .tables-container {
        grid-template-columns: 1fr;
    }

    th, td {
        padding: 10px 12px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .stat-card {
        padding: 20px;
    }

    .stat-value {
        font-size: 24px;
    }

    table {
        font-size: 11px;
    }

    th, td {
        padding: 8px 10px;
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
        <a href="dashboard_admin.php" class="active">
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
        <a href="riwayat_transaksi_admin.php">
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
        <h2>Dashboard</h2>
        <div class="header-date">
            <span>📅</span>
            <span><?= date('d M Y') ?></span>
        </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content-body">

        <!-- KARTU STATISTIK -->
        <div class="cards-container">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">💰</div>
                    <h4>Total Pendapatan</h4>
                </div>
                <div class="stat-value">
                    Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <!-- TABEL DATA -->
        <div class="tables-container">

            <!-- PESANAN TERBARU -->
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-header-icon">📦</span>
                    <h4>Pesanan Terbaru</h4>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($q_pesanan) > 0): ?>
                                <?php while($p = mysqli_fetch_assoc($q_pesanan)): ?>
                                <tr>
                                    <td><?= $p['tanggal'] ?></td>
                                    <td><?= $p['nama_pelanggan'] ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($p['status']) ?>">
                                            <?= $p['status'] ?>
                                        </span>
                                    </td>
                                    <td><strong>Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-table">
                                        <div class="empty-table-icon">📭</div>
                                        <div>Belum ada pesanan</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PRODUK TERBARU -->
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-header-icon">🍽️</span>
                    <h4>Produk Terbaru</h4>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($q_produk) > 0): ?>
                                <?php while($pr = mysqli_fetch_assoc($q_produk)): ?>
                                <tr>
                                    <td><?= $pr['nama'] ?></td>
                                    <td><?= $pr['kategori'] ?></td>
                                    <td><strong>Rp <?= number_format($pr['harga'], 0, ',', '.') ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="empty-table">
                                        <div class="empty-table-icon">🍽️</div>
                                        <div>Belum ada produk</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>