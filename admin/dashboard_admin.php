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
<title>Dashboard Admin - Zarali’s Catering</title>

<style>
body { margin:0; font-family:Arial,sans-serif; background:#fff; }
.sidebar {
    width:160px; background:#8A2BE2; height:100vh; color:white;
    position:fixed; padding-top:20px; text-align:center;
}
.sidebar img { width:80px; border-radius:50%; background:#fff; }
.sidebar a {
    display:block; color:white; padding:10px; text-decoration:none;
    font-size:13px;
}
.sidebar a.active, .sidebar a:hover {
    background:#FFE500; color:black;
}
.main-content {
    margin-left:180px; padding:30px;
}
.header {
    display:flex; justify-content:space-between; align-items:center;
}
.cards {
    display:flex; gap:20px; margin:25px 0;
}
.card {
    background:#FFE500; padding:15px; width:30%; border-radius:6px;
}
.table-section {
    display:flex; gap:30px;
}
.table-box {
    background:#FFE500; padding:15px; width:48%; border-radius:6px;
}
table { width:100%; border-collapse:collapse; font-size:13px; }
th, td { padding:6px; border-bottom:1px solid #ddd; }
</style>
</head>

<body>

<div class="sidebar">
    <img src="logo.png">
    <h4>Zarali’s Catering</h4>
    <a href="dashboard_admin.php" class="active">Dashboard</a>
    <a href="produk_admin.php">Produk</a>
    <a href="pesanan_admin.php">Pesanan</a>
    <a href="riwayat_transaksi_admin.php">Transaksi</a>
    <a href="laporan_penjualan_admin.php">Laporan</a>
    <a href="logout_admin.php">Logout</a>
</div>

<div class="main-content">

    <div class="header">
        <h2>Dashboard</h2>
        <span>Hari ini : <?= date('d M Y') ?></span>
    </div>

    <!-- KARTU RINGKASAN -->
    <div class="cards">
        <div class="card">
            <h4>Total Pendapatan</h4>
            <p>Rp <?= number_format($total_pendapatan,0,',','.') ?></p>
        </div>
    </div>

    <!-- TABEL -->
    <div class="table-section">

        <!-- PESANAN TERBARU -->
        <div class="table-box">
            <h4>Pesanan Terbaru</h4>
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
                <?php while($p = mysqli_fetch_assoc($q_pesanan)): ?>
                <tr>
                    <td><?= $p['tanggal'] ?></td>
                    <td><?= $p['nama_pelanggan'] ?></td>
                    <td><?= $p['status'] ?></td>
                    <td>Rp <?= number_format($p['total_harga'],0,',','.') ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- PRODUK TERBARU -->
        <div class="table-box">
            <h4>Produk Terbaru</h4>
            <table>
                <tr>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                </tr>
                <?php while($pr = mysqli_fetch_assoc($q_produk)): ?>
                <tr>
                    <td><?= $pr['nama'] ?></td>
                    <td><?= $pr['kategori'] ?></td>
                    <td>Rp <?= number_format($pr['harga'],0,',','.') ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>

</div>

</body>
</html>
