<?php
include 'koneksi.php';

// Hapus produk
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM produk WHERE id=$id");
  header("Location: produk.php");
}
$where = "";

if (isset($_GET['cari']) && $_GET['cari'] != "") {
    $key = $_GET['cari'];
    $where = "WHERE nama LIKE '%$key%'";
}

if (isset($_GET['kategori']) && $_GET['kategori'] != "") {
    $kat = $_GET['kategori'];
    $where = "WHERE kategori='$kat'";
}

$produk = mysqli_query($conn, "SELECT * FROM produk $where ORDER BY id DESC");
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen Produk - Zarali’s Catering</title>

<style>
/* LAYOUT UTAMA */
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background-color: #f5f5f5;
}

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

.main-content {
  margin-left: 200px;
  padding: 20px;
}

/* HEADER */
.header {
  background: #FFE500;
  padding: 15px 20px;
  border-radius: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-section {
  margin-top: 20px;
  background: #FFE500;
  padding: 15px 20px;
  border-radius: 10px;
  display: flex;
  justify-content: space-between;

  <form method="GET" style="display:flex; gap:10px;">
    <input type="text" name="cari" placeholder="Cari produk..." 
           style="padding:8px; border-radius:5px; border:1px solid #999;">
    <button style="padding:8px 12px; background:#2962FF; color:white; border:none; border-radius:5px;">Cari</button>
</form>

}

.btn-add {
  background: #00C853;
  color: white;
  padding: 8px 15px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
}

/* PRODUK GRID */
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

<!-- SIDEBAR -->
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
  <div class="logout"><a href="#">Logout</a></div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

  <div class="header">
    <h1>Manajemen Produk</h1>
    <span>Hari ini : <?= date('d M Y') ?></span>
    <button class="admin-btn">Admin</button>
  </div>

  <div class="header-section">
    <h2>Daftar Produk</h2>

    <!-- Tombol menuju halaman tambah produk -->
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
          <p class="price">Rp. <?= number_format($p['harga'], 0, ',', '.') ?></p>
        </div>

        <div class="action-buttons">
          <a class="edit-btn" href="editproduk.php?id=<?= $p['id'] ?>">Edit</a>
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
