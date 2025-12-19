<?php
include 'koneksi.php';

/* =========================
   HAPUS PRODUK
========================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id=$id");
    header("Location: produk_admin.php");
    exit;
}

/* =========================
   FILTER PENCARIAN
========================= */
$where = [];
$query = "SELECT * FROM produk";

if (!empty($_GET['cari'])) {
    $key = mysqli_real_escape_string($conn, $_GET['cari']);
    $where[] = "nama LIKE '%$key%'";
}

if (!empty($_GET['kategori'])) {
    $kat = mysqli_real_escape_string($conn, $_GET['kategori']);
    $where[] = "kategori='$kat'";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY id DESC";
$produk = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen Produk - Zarali’s Catering</title>

<style>
/* ===== GLOBAL ===== */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background-color: #f5f5f5;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 180px;
    background: #8200ff;
    height: 100vh;
    position: fixed;
    color: white;
    padding-top: 20px;
}

.logo-box {
    text-align: center;
    margin-bottom: 30px;
}

.logo-box img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
}

.menu a {
    display: block;
    padding: 12px 20px;
    color: white;
    text-decoration: none;
    font-size: 14px;
}

.menu a.active {
    background: #5e02ba;
    border-radius: 5px;
}

.logout {
    position: absolute;
    bottom: 20px;
    width: 100%;
    text-align: center;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: 200px;
    padding: 20px;
}

/* ===== HEADER ===== */
.header {
    background: #FFE500;
    padding: 15px 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* ===== HEADER SECTION ===== */
.header-section {
    margin-top: 20px;
    background: #FFE500;
    padding: 15px 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.search-form {
    display: flex;
    gap: 10px;
}

.search-form input {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #999;
}

.search-form button {
    padding: 8px 12px;
    background: #2962FF;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-add {
    background: #00C853;
    color: white;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
}

/* ===== PRODUK ===== */
.product-container {
    background: #FFE500;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}

.product-card img {
    width: 100%;
    height: 170px;
    object-fit: cover;
}

.product-info {
    padding: 15px;
}

.product-info h4 {
    margin: 0 0 5px;
}

.product-info p {
    margin: 4px 0;
}

.price {
    font-weight: bold;
    color: #00C853;
}

.action-buttons {
    display: flex;
    justify-content: space-between;
    padding: 15px;
}

.edit-btn {
    background: #0d6efd;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
}

.delete-btn {
    background: #e53935;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
}
</style>
</head>

<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="logo-box">
        <img src="logo.png">
        <h3>Zarali’s Catering</h3>
    </div>
    <div class="menu">
        <a href="dashboard_admin.php">Dashboard</a>
        <a href="produk_admin.php" class="active">Produk</a>
        <a href="pesanan_admin.php">Pesanan</a>
        <a href="riwayat_transaksi_admin.php">Transaksi</a>
        <a href="laporan_penjualan_admin.php">Laporan Penjualan</a>
        <a href="logout_admin.php">Logout</a>
    </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <div class="header">
        <h1>Manajemen Produk</h1>
        <span>Hari ini : <?= date('d M Y') ?></span>
    </div>
    <div class="image-box">
</div>

    <div class="header-section">
        <h2>Daftar Produk</h2>

        <form method="GET" class="search-form">
            <input type="text" name="cari" placeholder="Cari produk..."
                   value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">
            <button type="submit">Cari</button>
        </form>

        <a href="tambah_produk_admin.php" class="btn-add">Tambah Produk</a>
    </div>

    <div class="product-container">
        <div class="product-grid">

            <?php while ($p = mysqli_fetch_assoc($produk)) : ?>
            <div class="product-card">

                <img src="uploads/<?= $p['gambar'] ?>">

                <div class="product-info">
                    <h4><?= $p['nama'] ?></h4>
                    <p>Kategori: <?= $p['kategori'] ?></p>
                    <p class="price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                </div>

                <div class="action-buttons">
                    <a class="edit-btn" href="edit_produk.php?id=<?= $p['id'] ?>">Edit</a>
                    <a class="delete-btn" href="?hapus=<?= $p['id'] ?>"
                       onclick="return confirm('Hapus produk ini?')">Hapus</a>
                </div>

            </div>
            <?php endwhile; ?>

        </div>
    </div>

</div>

</body>
</html>
