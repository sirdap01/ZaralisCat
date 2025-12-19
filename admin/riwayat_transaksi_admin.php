<?php
include "koneksi.php";

/* ==========================
   FILTER TANGGAL
========================== */
$tanggal = "";

if (!empty($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];
    $query = mysqli_query($conn, "
        SELECT * FROM pesanan 
        WHERE status='Lunas' AND tanggal='$tanggal'
        ORDER BY id DESC
    ");
} else {
    $query = mysqli_query($conn, "
        SELECT * FROM pesanan 
        WHERE status='Lunas'
        ORDER BY id DESC
    ");
}

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Transaksi</title>

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

.sidebar h3 { margin-bottom:30px; }

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

.box {
    background:#ffe600;
    padding:15px;
    border-radius:8px;
    margin-top:15px;
}

table {
    width:100%;
    border-collapse:collapse;
}

th, td {
    padding:10px;
    border:1px solid #000;
    text-align:center;
}

th {
    background:#ffd900;
}

.btn {
    padding:6px 12px;
    background:green;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

.input-tgl {
    padding:6px 10px;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>Zarali's Catering</h3>
    <a href="dashboard_admin.php">Dashboard</a>
    <a href="produk_admin.php">Produk</a>
    <a href="pesanan_admin.php">Pesanan</a>
    <a href="riwayat_transaksi_admin.php" class="active">Transaksi</a>
    <a href="laporan_penjualan_admin.php">Laporan</a>
    <a href="logout_admin.php">Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h2>Riwayat Transaksi</h2>

    <form method="GET">
        <label>Tanggal :</label>
        <input type="date" name="tanggal" class="input-tgl" value="<?= $tanggal ?>">
        <button class="btn">Tampilkan</button>
    </form>

    <div class="box">
        <table>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Metode Pembayaran</th>
                <th>Total Harga</th>
                <th>Status</th>
            </tr>

            <?php if (mysqli_num_rows($query) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['tanggal'] ?></td>
                    <td><?= $row['nama_pelanggan'] ?></td>
                    <td><?= $row['metode_pembayaran'] ?></td>
                    <td>Rp <?= number_format($row['total_harga'],0,',','.') ?></td>
                    <td><?= $row['status'] ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><b>Tidak ada transaksi</b></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
