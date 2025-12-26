<?php
include "koneksi.php";

$filter = $_GET['filter'] ?? "minggu";

/* ===============================
   QUERY BERDASARKAN FILTER
================================ */
if ($filter == "minggu") {
    $sql = "
        SELECT * FROM pesanan
        WHERE status='Lunas'
        AND tanggal_pesanan >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY tanggal_pesanan DESC
    ";
    $periode_text = "7 Hari Terakhir";
} elseif ($filter == "bulan") {
    $sql = "
        SELECT * FROM pesanan
        WHERE status='Lunas'
        AND MONTH(tanggal_pesanan)=MONTH(CURDATE())
        AND YEAR(tanggal_pesanan)=YEAR(CURDATE())
        ORDER BY tanggal_pesanan DESC
    ";
    $periode_text = "Bulan Ini";
} else { // tahun
    $sql = "
        SELECT * FROM pesanan
        WHERE status='Lunas'
        AND YEAR(tanggal_pesanan)=YEAR(CURDATE())
        ORDER BY tanggal_pesanan DESC
    ";
    $periode_text = "Tahun Ini";
}

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

/* ===============================
   HITUNG STATISTIK
================================ */
$total_penjualan = 0;
$jumlah_transaksi = 0;
$rata_transaksi = 0;
$tunai = 0;
$non_tunai = 0;
$transaksi_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $total_penjualan += $row['total_harga'];
    $jumlah_transaksi++;
    $transaksi_list[] = $row;

    if (strtolower($row['metode_pembayaran']) == "tunai") {
        $tunai++;
    } else {
        $non_tunai++;
    }
}

if ($jumlah_transaksi > 0) {
    $rata_transaksi = $total_penjualan / $jumlah_transaksi;
}

/* ===============================
   HITUNG PRODUK TERLARIS
================================ */
// Check if detail_pesanan table exists and get the correct column names
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'detail_pesanan'");

if(mysqli_num_rows($check_table) > 0) {
    // Table exists, let's check the structure
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM detail_pesanan");
    $columns = [];
    while($col = mysqli_fetch_assoc($check_columns)) {
        $columns[] = $col['Field'];
    }
    
    // Determine the correct column names
    $produk_col = 'nama_produk';
    $jumlah_col = 'jumlah';
    $subtotal_col = 'subtotal';
    $pesanan_id_col = 'id_pesanan';
    
    if(in_array('produk', $columns)) $produk_col = 'produk';
    if(in_array('qty', $columns)) $jumlah_col = 'qty';
    if(in_array('quantity', $columns)) $jumlah_col = 'quantity';
    if(in_array('harga', $columns)) $subtotal_col = 'harga';
    if(in_array('pesanan_id', $columns)) $pesanan_id_col = 'pesanan_id';
    if(in_array('id_order', $columns)) $pesanan_id_col = 'id_order';
    
    $produk_query = mysqli_query($conn, "
        SELECT 
            dp.$produk_col as nama_produk,
            SUM(dp.$jumlah_col) as total_terjual,
            SUM(dp.$subtotal_col) as total_pendapatan
        FROM detail_pesanan dp
        JOIN pesanan p ON dp.$pesanan_id_col = p.id
        WHERE p.status = 'Lunas'
        " . ($filter == "minggu" ? "AND p.tanggal_pesanan>= DATE_SUB(CURDATE(), INTERVAL 7 DAY)" : 
             ($filter == "bulan" ? "AND MONTH(p.tanggal_pesanan)=MONTH(CURDATE()) AND YEAR(p.tanggal_pesanan)=YEAR(CURDATE())" : 
              "AND YEAR(p.tanggal_pesanan)=YEAR(CURDATE())")) . "
        GROUP BY dp.$produk_col
        ORDER BY total_terjual DESC
        LIMIT 5
    ");
} else {
    // If table doesn't exist, create empty result
    $produk_query = mysqli_query($conn, "SELECT NULL as nama_produk, 0 as total_terjual, 0 as total_pendapatan LIMIT 0");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Penjualan - Zarali's Catering</title>
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
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filter-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-purple);
    display: flex;
    align-items: center;
    gap: 10px;
}

.periode-badge {
    background: linear-gradient(135deg, var(--secondary-gold), #FFC700);
    color: var(--primary-purple);
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 700;
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: center;
}

.filter-select {
    flex: 1;
    padding: 12px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    max-width: 300px;
}

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
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(123, 44, 191, 0.4);
}

/* =====================================
   STATISTICS CARDS
===================================== */
.stats-grid {
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

.stat-card.revenue::before {
    background: rgba(0, 200, 83, 0.05);
}

.stat-card.transaction::before {
    background: rgba(33, 150, 243, 0.05);
}

.stat-card.average::before {
    background: rgba(255, 152, 0, 0.05);
}

.stat-card.payment::before {
    background: rgba(156, 39, 176, 0.05);
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

.stat-icon.revenue {
    background: linear-gradient(135deg, #00C853, #00E676);
}

.stat-icon.transaction {
    background: linear-gradient(135deg, #2196F3, #42A5F5);
}

.stat-icon.average {
    background: linear-gradient(135deg, #FF9800, #FFB74D);
}

.stat-icon.payment {
    background: linear-gradient(135deg, #9C27B0, #BA68C8);
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

.stat-card.revenue .stat-value {
    color: #00C853;
}

.stat-card.transaction .stat-value {
    color: #2196F3;
}

.stat-card.average .stat-value {
    color: #FF9800;
}

.stat-card.payment .stat-value {
    color: #9C27B0;
}

.payment-detail {
    font-size: 14px;
    color: #666;
    margin-top: 10px;
    position: relative;
    z-index: 1;
}

.payment-detail span {
    display: block;
    margin: 5px 0;
}

/* =====================================
   PRODUK TERLARIS
===================================== */
.products-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    margin-bottom: 30px;
}

.products-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--secondary-gold);
}

.products-header h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-purple);
}

.products-list {
    display: grid;
    gap: 15px;
}

.product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: var(--background-light);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.product-item:hover {
    background: #FFF8E1;
    transform: translateX(5px);
}

.product-info {
    flex: 1;
}

.product-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.product-stats {
    font-size: 14px;
    color: #666;
}

.product-rank {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    margin-right: 15px;
}

.product-revenue {
    text-align: right;
}

.revenue-amount {
    font-size: 18px;
    font-weight: 700;
    color: #00C853;
}

.revenue-label {
    font-size: 12px;
    color: #999;
    margin-top: 3px;
}

/* =====================================
   EMPTY STATE
===================================== */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-icon {
    font-size: 60px;
    margin-bottom: 15px;
}

.empty-state p {
    font-size: 14px;
    color: #999;
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

    .filter-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }

    .filter-form {
        flex-direction: column;
        width: 100%;
    }

    .filter-select {
        max-width: 100%;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .product-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .product-rank {
        margin-right: 0;
    }

    .product-revenue {
        text-align: center;
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
        <a href="riwayat_transaksi_admin.php">
            <span class="menu-icon">💳</span>
            <span>Transaksi</span>
        </a>
        <a href="laporan_penjualan_admin.php" class="active">
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
        <h2>Laporan Penjualan</h2>
        <div class="header-date">
            <span>📅</span>
            <span><?= date('d M Y') ?></span>
        </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content-body">

        <!-- FILTER BOX -->
        <div class="filter-box">
            <div class="filter-header">
                <h3>
                    <span>🔍</span>
                    <span>Filter Periode</span>
                </h3>
                <div class="periode-badge"><?= $periode_text ?></div>
            </div>

            <form method="GET" class="filter-form">
                <select name="filter" class="filter-select">
                    <option value="minggu" <?= ($filter=="minggu")?"selected":"" ?>>📅 Per Minggu (7 Hari Terakhir)</option>
                    <option value="bulan" <?= ($filter=="bulan")?"selected":"" ?>>📆 Per Bulan (Bulan Ini)</option>
                    <option value="tahun" <?= ($filter=="tahun")?"selected":"" ?>>📊 Per Tahun (Tahun Ini)</option>
                </select>
                <button class="btn-filter">Tampilkan</button>
            </form>
        </div>

        <!-- STATISTICS CARDS -->
        <div class="stats-grid">
            <div class="stat-card revenue">
                <div class="stat-card-header">
                    <div class="stat-icon revenue">💰</div>
                    <h4>Total Penjualan</h4>
                </div>
                <div class="stat-value">
                    Rp <?= number_format($total_penjualan, 0, ',', '.') ?>
                </div>
            </div>

            <div class="stat-card transaction">
                <div class="stat-card-header">
                    <div class="stat-icon transaction">🛒</div>
                    <h4>Jumlah Transaksi</h4>
                </div>
                <div class="stat-value">
                    <?= $jumlah_transaksi ?> transaksi
                </div>
            </div>

            <div class="stat-card average">
                <div class="stat-card-header">
                    <div class="stat-icon average">📊</div>
                    <h4>Rata-rata Transaksi</h4>
                </div>
                <div class="stat-value">
                    Rp <?= number_format($rata_transaksi, 0, ',', '.') ?>
                </div>
            </div>

            <div class="stat-card payment">
                <div class="stat-card-header">
                    <div class="stat-icon payment">💳</div>
                    <h4>Metode Pembayaran</h4>
                </div>
                <div class="payment-detail">
                    <span><strong>💵 Tunai:</strong> <?= $tunai ?> transaksi</span>
                    <span><strong>💳 Non-Tunai:</strong> <?= $non_tunai ?> transaksi</span>
                </div>
            </div>
        </div>

        <!-- PRODUK TERLARIS -->
        <div class="products-section">
            <div class="products-header">
                <span style="font-size: 24px;">🏆</span>
                <h3>Top 5 Produk Terlaris</h3>
            </div>

            <div class="products-list">
                <?php if(mysqli_num_rows($produk_query) > 0): ?>
                    <?php 
                    $rank = 1;
                    while($prod = mysqli_fetch_assoc($produk_query)): 
                    ?>
                    <div class="product-item">
                        <div class="product-rank"><?= $rank++ ?></div>
                        <div class="product-info">
                            <div class="product-name"><?= $prod['nama_produk'] ?></div>
                            <div class="product-stats">Terjual: <?= $prod['total_terjual'] ?> porsi</div>
                        </div>
                        <div class="product-revenue">
                            <div class="revenue-amount">
                                Rp <?= number_format($prod['total_pendapatan'], 0, ',', '.') ?>
                            </div>
                            <div class="revenue-label">Total Pendapatan</div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <p>Belum ada data produk terlaris untuk periode ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

</body>
</html>