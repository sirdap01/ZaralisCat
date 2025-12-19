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

$sql = "SELECT * FROM pesanan";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$pesanan = mysqli_query($conn, $sql);

if (!$pesanan) {
    die("Query Error : " . mysqli_error($conn));
}

$today = date('d M Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen Pesanan - Zarali’s Catering</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
}

.sidebar {
    width: 200px;
    background: #6a0dad;
    color: white;
    height: 100vh;
    position: fixed;
}

.sidebar h3 {
    text-align: center;
}

.sidebar a {
    display: block;
    padding: 12px 20px;
    color: white;
    text-decoration: none;
}

.sidebar a.active,
.sidebar a:hover {
    background: #8a2be2;
}

.main-content {
    margin-left: 220px;
    padding: 20px;
}

.header {
    background: #FFE500;
    padding: 15px 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
}

.controls {
    display: flex;
    gap: 10px;
    margin: 20px 0;
}

.controls input,
.controls button {
    padding: 8px;
}

.table-box {
    background: #fff066;
    padding: 20px;
    border-radius: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ffcc00;
}

th {
    background: #ffe840;
}

td {
    background: #fff8b3;
}

.aksi {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
    white-space: nowrap;
}

.btn-aksi {
    padding: 6px 12px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    font-size: 13px;
    font-weight: bold;
}


.btn-edit {
    background: #0d6efd;
}

.btn-detail {
    background: #00C853;
}

.btn-cetak {
    background: #6f42c1;
}

.btn-tambah {
    background: #4CAF50;
    padding: 8px 14px;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;

}

.empty {
    text-align: center;
    padding: 20px;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="sidebar">
    <h3>Zarali's Catering</h3>
    <a href="dashboard_admin.php">Dashboard</a>
    <a href="produk_admin.php">Produk</a>
    <a href="pesanan_admin.php" class="active">Pesanan</a>
    <a href="riwayat_transaksi_admin.php">Transaksi</a>
    <a href="laporan_penjualan_admin.php">Laporan</a>
    <a href="logout_admin.php">Logout</a>
</div>

<div class="main-content">

    <div class="header">
        <h2>Daftar Pesanan</h2>
        <span>Hari ini : <?= $today ?></span>
    </div>

    <form method="GET" class="controls">
    <input type="text" name="cari" placeholder="Cari pelanggan..."
           value="<?= $_GET['cari'] ?? '' ?>">

    <input type="date" name="tanggal"
           value="<?= $_GET['tanggal'] ?? '' ?>">

    <button type="submit">Tampilkan</button>

    <!-- TOMBOL TAMBAH PESANAN -->
    <a href="tambah_pesanan.php" class="btn btn-tambah">
        + Tambah Pesanan
    </a>
</form>


    <div class="table-box">
       <table border="1" width="100%">
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Pelanggan</th>
    <th>Metode Pembayaran</th>
    <th>Total</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($p = mysqli_fetch_assoc($pesanan)): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $p['tanggal'] ?></td>
    <td><?= $p['nama_pelanggan'] ?></td>
    <td><?= $p['metode_pembayaran'] ?></td>
    <td>Rp <?= number_format($p['total_harga'],0,',','.') ?></td>
    <td><?= $p['status'] ?>
    <td class="aksi">

    <a href="edit_status_pesanan.php?id=<?= $p['id'] ?>"
       class="btn-aksi btn-edit">
       Edit
    </a>

    <a href="detail_pesanan_admin.php?id=<?= $p['id'] ?>"
       class="btn-aksi btn-detail">
       Detail
    </a>

    <a href="cetak_invoice.php?id=<?= $p['id'] ?>"
       class="btn-aksi btn-cetak"
       target="_blank">
       Cetak
    </a>

</td>

</tr>
<?php endwhile; ?>
</table>

    </div>

</div>

</body>
</html>
