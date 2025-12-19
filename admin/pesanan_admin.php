<?php
include 'koneksi.php';

/* =========================
   FILTER & QUERY PESANAN
========================= */
$where = [];

if (!empty($_GET['cari'])) {
    $cari = mysqli_real_escape_string($conn, $_GET['cari']);
    $where[] = "nama_pelanggan LIKE '%$cari%'";
}

if (!empty($_GET['tanggal'])) {
    $tgl = mysqli_real_escape_string($conn, $_GET['tanggal']);
    $where[] = "tanggal='$tgl'";
}

if (!empty($_GET['status'])) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where[] = "status='$status'";
}

$sql = "SELECT * FROM pesanan";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$pesanan = mysqli_query($conn, $sql);

if (!$pesanan) {
    die("Query Error : " . mysqli_error($conn));
}

/* =========================
   STATISTIK PESANAN
========================= */
$total_pesanan = mysqli_num_rows($pesanan);

$stat_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status='Pending'"))['total'];
$stat_proses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status='Proses'"))['total'];
$stat_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status='Selesai'"))['total'];

$today = date('d M Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Pesanan - Zarali's Catering</title>
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
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(123, 44, 191, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.stat-icon.total {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
}

.stat-icon.pending {
    background: linear-gradient(135deg, #FFA726, #FB8C00);
}

.stat-icon.proses {
    background: linear-gradient(135deg, #42A5F5, #2196F3);
}

.stat-icon.selesai {
    background: linear-gradient(135deg, #66BB6A, #4CAF50);
}

.stat-info h4 {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    margin-bottom: 5px;
}

.stat-info .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-purple);
}

/* =====================================
   TOOLBAR
===================================== */
.toolbar {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    margin-bottom: 30px;
}

.toolbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.filter-box {
    display: flex;
    gap: 10px;
    flex: 1;
    flex-wrap: wrap;
}

.filter-input {
    padding: 12px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 200px;
}

.filter-input:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
}

.filter-select {
    padding: 12px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    min-width: 150px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary-purple);
}

.btn-filter {
    padding: 12px 25px;
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

.btn-add-order {
    padding: 12px 25px;
    background: linear-gradient(135deg, #00C853, #00E676);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 200, 83, 0.3);
}

.btn-add-order:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 200, 83, 0.4);
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

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
}

th {
    padding: 18px 20px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
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

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
    text-align: center;
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

.aksi {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-aksi {
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-edit {
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
}

.btn-detail {
    background: linear-gradient(135deg, #00C853, #00E676);
    box-shadow: 0 2px 8px rgba(0, 200, 83, 0.3);
}

.btn-detail:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 200, 83, 0.4);
}

.btn-cetak {
    background: linear-gradient(135deg, #9C27B0, #BA68C8);
    box-shadow: 0 2px 8px rgba(156, 39, 176, 0.3);
}

.btn-cetak:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(156, 39, 176, 0.4);
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
    margin-bottom: 25px;
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

    .toolbar-content {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-box {
        flex-direction: column;
    }

    .filter-input,
    .filter-select {
        min-width: 100%;
    }

    th, td {
        padding: 12px 10px;
        font-size: 12px;
    }

    .aksi {
        flex-direction: column;
    }

    .btn-aksi {
        width: 100%;
        justify-content: center;
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
        <a href="pesanan_admin.php" class="active">
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
        <h2>Manajemen Pesanan</h2>
        <div class="header-date">
            <span>📅</span>
            <span><?= $today ?></span>
        </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content-body">

        <!-- STATISTICS -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon total">📦</div>
                <div class="stat-info">
                    <h4>Total Pesanan</h4>
                    <div class="stat-value"><?= $total_pesanan ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending">⏳</div>
                <div class="stat-info">
                    <h4>Pending</h4>
                    <div class="stat-value"><?= $stat_pending ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon proses">⚙️</div>
                <div class="stat-info">
                    <h4>Proses</h4>
                    <div class="stat-value"><?= $stat_proses ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon selesai">✅</div>
                <div class="stat-info">
                    <h4>Selesai</h4>
                    <div class="stat-value"><?= $stat_selesai ?></div>
                </div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="toolbar-content">
                <form method="GET" class="filter-box">
                    <input 
                        type="text" 
                        name="cari" 
                        class="filter-input"
                        placeholder="🔍 Cari nama pelanggan..." 
                        value="<?= $_GET['cari'] ?? '' ?>"
                    >

                    <input 
                        type="date" 
                        name="tanggal" 
                        class="filter-input"
                        value="<?= $_GET['tanggal'] ?? '' ?>"
                    >

                    <select name="status" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="Pending" <?= (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Proses" <?= (isset($_GET['status']) && $_GET['status'] == 'Proses') ? 'selected' : '' ?>>Proses</option>
                        <option value="Selesai" <?= (isset($_GET['status']) && $_GET['status'] == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                    </select>

                    <button type="submit" class="btn-filter">Tampilkan</button>
                </form>

                <a href="tambah_pesanan.php" class="btn-add-order">
                    <span>➕</span>
                    <span>Tambah Pesanan</span>
                </a>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Metode Pembayaran</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($total_pesanan > 0): ?>
                            <?php 
                            $no = 1;
                            mysqli_data_seek($pesanan, 0);
                            while($p = mysqli_fetch_assoc($pesanan)): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $p['tanggal'] ?></td>
                                <td><strong><?= $p['nama_pelanggan'] ?></strong></td>
                                <td><?= $p['metode_pembayaran'] ?></td>
                                <td><strong>Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($p['status']) ?>">
                                        <?= $p['status'] ?>
                                    </span>
                                </td>
                                <td class="aksi">
                                    <a href="edit_status_pesanan.php?id=<?= $p['id'] ?>" class="btn-aksi btn-edit">
                                        ✏️ Edit
                                    </a>
                                    <a href="detail_pesanan_admin.php?id=<?= $p['id'] ?>" class="btn-aksi btn-detail">
                                        👁️ Detail
                                    </a>
                                    <a href="cetak_invoice.php?id=<?= $p['id'] ?>" class="btn-aksi btn-cetak" target="_blank">
                                        🖨️ Cetak
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-icon">📭</div>
                                    <h3>Belum Ada Pesanan</h3>
                                    <p>Pesanan akan muncul di sini setelah pelanggan melakukan pemesanan</p>
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