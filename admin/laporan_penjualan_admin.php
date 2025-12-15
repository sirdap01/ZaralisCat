<?php
include "koneksi.php";  // file koneksi database

$filter = isset($_GET['filter']) ? $_GET['filter'] : "minggu";

// Default nilai
$total_penjualan = 0;
$jumlah_transaksi = 0;
$rata_transaksi = 0;
$tunai = 0;
$non_tunai = 0;

// Query berdasarkan filter
if ($filter == "minggu") {
    // 7 hari terakhir
    $sql = "SELECT * FROM transaksi WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($filter == "bulan") {
    // bulan berjalan
    $sql = "SELECT * FROM transaksi WHERE MONTH(tanggal) = MONTH(CURDATE())";
} elseif ($filter == "tahun") {
    // tahun berjalan
    $sql = "SELECT * FROM transaksi WHERE YEAR(tanggal) = YEAR(CURDATE())";
}

// Jalankan query
$result = mysqli_query($conn, $sql);

// Hitung statistik
if (mysqli_num_rows($result) > 0) {

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
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #fffbe6;
        }

        /* Sidebar */
        .sidebar {
            width: 200px;
            height: 100vh;
            background: #8000ff;
            padding: 20px;
            position: fixed;
            color: white;
        }

        .sidebar h3 {
            margin-top: 40px;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            margin: 15px 0;
            text-decoration: none;
        }

        /* Content */
        .content {
            margin-left: 230px;
            padding: 20px;
        }

        .section {
            background: #ffe600;
            padding: 15px;
            border-radius: 8px;
        }

        .stats-box {
            margin-top: 20px;
            display: flex;
            gap: 25px;
        }

        .card {
            background: #ffe600;
            width: 230px;
            padding: 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
        }

        .btn {
            padding: 8px 15px;
            background: green;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        select {
            padding: 5px 10px;
        }

    </style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h3>Zarali’s Catering</h3>
    <a href="dashboard_admin.php">Dashboard</a>
    <a href="produk_admin.php">Produk</a>
    <a href="pesanan_admin.php">Pesanan</a>
    <a href="riwayat_transaksi_admin.php">Transaksi</a>
    <a href="laporan_penjualan_admin.php" class="active">Laporan Penjualan</a>
    <a href="#" style="color:red;">Logout</a>
</div>

<div class="content">

    <h2>Laporan Penjualan</h2>

    <div class="section">
        <form method="GET">

            <label><b>Laporan Penjualan</b></label>

            &nbsp;&nbsp;&nbsp;

            <select name="filter">
                <option value="minggu" <?= ($filter == "minggu") ? "selected" : "" ?>>Per Minggu</option>
                <option value="bulan" <?= ($filter == "bulan") ? "selected" : "" ?>>Per Bulan</option>
                <option value="tahun" <?= ($filter == "tahun") ? "selected" : "" ?>>Per Tahun</option>
            </select>

            <button class="btn" type="submit">Tampilkan</button>
        </form>
    </div>

    <!-- Statistik -->
    <div class="stats-box">

        <div class="card">
            Total Penjualan<br>
            Rp <?= number_format($total_penjualan, 0, ',', '.') ?>
        </div>

        <div class="card">
            Jumlah Transaksi<br>
            <?= $jumlah_transaksi ?>
        </div>

        <div class="card">
            Rata-rata Transaksi<br>
            Rp <?= number_format($rata_transaksi, 0, ',', '.') ?>
        </div>

        <div class="card">
            Metode Pembayaran<br>
            Tunai: <?= $tunai ?> | Non-tunai: <?= $non_tunai ?>
        </div>

    </div>

</div>

</body>
</html>
