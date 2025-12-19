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
        AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ";
} elseif ($filter == "bulan") {
    $sql = "
        SELECT * FROM pesanan
        WHERE status='Lunas'
        AND MONTH(tanggal)=MONTH(CURDATE())
        AND YEAR(tanggal)=YEAR(CURDATE())
    ";
} else { // tahun
    $sql = "
        SELECT * FROM pesanan
        WHERE status='Lunas'
        AND YEAR(tanggal)=YEAR(CURDATE())
    ";
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

while ($row = mysqli_fetch_assoc($result)) {

    $total_penjualan += $row['total_harga'];
    $jumlah_transaksi++;

    if (strtolower($row['metode_pembayaran']) == "tunai") {
        $tunai++;
    } else {
        $non_tunai++;
    }
}

if ($jumlah_transaksi > 0) {
    $rata_transaksi = $total_penjualan / $jumlah_transaksi;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan</title>

<style>
body {
    margin:0;
    font-family: Arial, sans-serif;
    background:#fffbe6;
}

/* Sidebar */
.sidebar {
    width:200px;
    height:100vh;
    background:#8000ff;
    padding:20px;
    position:fixed;
    color:white;
}

.sidebar h3 {
    margin-bottom:30px;
}

.sidebar a {
    display:block;
    color:white;
    margin:12px 0;
    text-decoration:none;
}

.sidebar a.active,
.sidebar a:hover {
    background:#ffe600;
    color:black;
    padding:6px;
    border-radius:6px;
}

/* Content */
.content {
    margin-left:230px;
    padding:20px;
}

.section {
    background:#ffe600;
    padding:15px;
    border-radius:8px;
}

.stats-box {
    margin-top:20px;
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}

.card {
    background:#ffe600;
    width:220px;
    padding:15px;
    border-radius:6px;
    font-weight:600;
    font-size:14px;
}

.btn {
    padding:6px 14px;
    background:green;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>Zarali’s Catering</h3>
    <a href="dashboard_admin.php">Dashboard</a>
    <a href="produk_admin.php">Produk</a>
    <a href="pesanan_admin.php">Pesanan</a>
    <a href="riwayat_transaksi_admin.php">Transaksi</a>
    <a href="laporan_penjualan_admin.php" class="active">Laporan Penjualan</a>
    <a href="logout_admin.php" style="color:red;">Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

<h2>Laporan Penjualan</h2>

<div class="section">
<form method="GET">
    <label><b>Filter :</b></label>
    <select name="filter">
        <option value="minggu" <?= ($filter=="minggu")?"selected":"" ?>>Per Minggu</option>
        <option value="bulan" <?= ($filter=="bulan")?"selected":"" ?>>Per Bulan</option>
        <option value="tahun" <?= ($filter=="tahun")?"selected":"" ?>>Per Tahun</option>
    </select>
    <button class="btn">Tampilkan</button>
</form>
</div>

<!-- STATISTIK -->
<div class="stats-box">

    <div class="card">
        Total Penjualan<br>
        Rp <?= number_format($total_penjualan,0,',','.') ?>
    </div>

    <div class="card">
        Jumlah Transaksi<br>
        <?= $jumlah_transaksi ?>
    </div>

    <div class="card">
        Rata-rata Transaksi<br>
        Rp <?= number_format($rata_transaksi,0,',','.') ?>
    </div>

    <div class="card">
        Metode Pembayaran<br>
        Tunai: <?= $tunai ?><br>
        Non-Tunai: <?= $non_tunai ?>
    </div>

</div>

</div>
</body>
</html>
