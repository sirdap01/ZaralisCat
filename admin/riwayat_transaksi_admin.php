<?php
include "koneksi.php";

/* ==========================
   FILTER TANGGAL
========================== */
$tanggal = "";
$where_date = "";

if (!empty($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];
    $where_date = "AND tanggal='$tanggal'";
}

/* ==========================
   FILTER METODE PEMBAYARAN
========================== */
$metode = "";
$where_metode = "";

if (!empty($_GET['metode'])) {
    $metode = $_GET['metode'];
    $where_metode = "AND metode_pembayaran='$metode'";
}

/* ==========================
   QUERY TRANSAKSI
========================== */
$query = mysqli_query($conn, "
    SELECT * FROM pesanan 
    WHERE status='Lunas' $where_date $where_metode
    ORDER BY id DESC
");

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

/* ==========================
   STATISTIK TRANSAKSI
========================== */
$total_transaksi = mysqli_num_rows($query);

$stat_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as jumlah,
        SUM(total_harga) as total_pendapatan
    FROM pesanan 
    WHERE status='Lunas' $where_date $where_metode
");
$stat = mysqli_fetch_assoc($stat_query);
$total_pendapatan = $stat['total_pendapatan'] ?? 0;

/* ==========================
   GET METODE PEMBAYARAN UNIK
========================== */
$metode_query = mysqli_query($conn, "SELECT DISTINCT metode_pembayaran FROM pesanan WHERE status='Lunas' ORDER BY metode_pembayaran");
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
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
    background: rgba(123, 44, 191, 0.05);
    border-radius: 50%;
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
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    border-radius: 12px;
    color: white;
}

.stat-icon.revenue {
    background: linear-gradient(135deg, #00C853, #00E676);
}

.stat-card h4 {
    font-size: 16px;
    font-weight: 600;
    color: #666;
    margin: 0;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-purple);
    position: relative;
    z-index: 1;
    margin-top: 10px;
}

.stat-value.revenue {
    color: #00C853;
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

.filter-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
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
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
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
    height: 46px;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(123, 44, 191, 0.4);
}

.btn-reset {
    padding: 12px 25px;
    background: #F5F5F5;
    color: var(--text-dark);
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    height: 46px;
    line-height: 22px;
}

.btn-reset:hover {
    background: #EEEEEE;
    border-color: #CCCCCC;
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
    padding: 5px 15px;
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
    background-color: #F5F5F5;
}

tbody tr:last-child td {
    border-bottom: none;
}

.status-lunas {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
    background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
    color: #155724;
}

.payment-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    background: #F0F0F0;
    color: #666;
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
}

/* =====================================
   RESPONSIVE DESIGN
===================================== */
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
        flex-direction: column;
    }

    .form-group {
        min-width: 100%;
    }

    th, td {
        padding: 12px 10px;
        font-size: 12px;
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

        <!-- STATISTICS -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">💳</div>
                    <h4>Total Transaksi</h4>
                </div>
                <div class="stat-value"><?= $total_transaksi ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon revenue">💰</div>
                    <h4>Total Pendapatan</h4>
                </div>
                <div class="stat-value revenue">
                    Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <!-- FILTER BOX -->
        <div class="filter-box">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>📅 Filter Tanggal</label>
                    <input 
                        type="date" 
                        name="tanggal" 
                        class="filter-input"
                        value="<?= $tanggal ?>"
                    >
                </div>

                <div class="form-group">
                    <label>💰 Metode Pembayaran</label>
                    <select name="metode" class="filter-select">
                        <option value="">Semua Metode</option>
                        <?php while($m = mysqli_fetch_assoc($metode_query)): ?>
                            <option value="<?= $m['metode_pembayaran'] ?>" 
                                <?= ($metode == $m['metode_pembayaran']) ? 'selected' : '' ?>>
                                <?= $m['metode_pembayaran'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn-filter">🔍 Tampilkan</button>
                <a href="riwayat_transaksi_admin.php" class="btn-reset">🔄 Reset</a>
            </form>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <div class="table-header">
                <h3>
                    <span>📋</span>
                    <span>Daftar Transaksi Lunas</span>
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
                            <th>Metode Pembayaran</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_transaksi > 0): ?>
                            <?php 
                            mysqli_data_seek($query, 0);
                            while ($row = mysqli_fetch_assoc($query)): 
                            ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td><?= $row['tanggal'] ?></td>
                                <td><strong><?= $row['nama_pelanggan'] ?></strong></td>
                                <td>
                                    <span class="payment-badge">
                                        <?= $row['metode_pembayaran'] ?>
                                    </span>
                                </td>
                                <td><strong>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <span class="status-lunas">
                                        ✓ <?= $row['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <div class="empty-icon">💳</div>
                                    <h3>Tidak Ada Transaksi</h3>
                                    <p>Belum ada transaksi yang lunas pada filter yang dipilih</p>
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