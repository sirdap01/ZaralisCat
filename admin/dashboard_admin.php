<?php
include 'koneksi.php';

// Hitung penjualan hari ini
$today = date('Y-m-d');
$q_penjualan = mysqli_query($conn, "SELECT SUM(total) AS total_hari_ini FROM pesanan WHERE tanggal = '$today'");
$d_penjualan = mysqli_fetch_assoc($q_penjualan);
$penjualan_hari_ini = $d_penjualan['total_hari_ini'] ?? 0;

// Hitung pesanan aktif
$q_aktif = mysqli_query($conn, "SELECT COUNT(*) AS total_aktif FROM pesanan WHERE status != 'Selesai'");
$d_aktif = mysqli_fetch_assoc($q_aktif);
$pesanan_aktif = $d_aktif['total_aktif'] ?? 0;

// Ambil pesanan terbaru
$q_pesanan = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY id DESC LIMIT 1");

// Ambil produk ringkasan
$q_produk = mysqli_query($conn, "SELECT * FROM produk");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zarali's Catering - Dashboard Admin</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      background-color: #fff;
    }

    /* Sidebar */
    .sidebar {
      width: 160px;
      background-color: #8A2BE2;
      height: 100vh;
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 30px;
      position: fixed;
    }

    .sidebar img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin-bottom: 15px;
      background-color: white;
      object-fit: contain;
      
    }

    .sidebar h3 {
      font-size: 14px;
      margin-bottom: 25px;
    }

    .menu {
      display: flex;
      flex-direction: column;
      width: 100%;
    }

    .menu a {
      text-decoration: none;
      color: white;
      padding: 10px 20px;
      transition: 0.3s;
      font-size: 13px;
    }

    .menu a:hover, .menu a.active {
      background-color: #f7dc11;
      color: black;
    }

    .logout {
      margin-top: auto;
      margin-bottom: 20px;
    }

    .logout a {
      color: red;
      text-decoration: none;
      font-size: 13px;
    }

    /* Main content */
    .main-content {
      margin-left: 180px;
      padding: 30px;
      width: 100%;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      font-size: 26px;
      font-weight: bold;
    }

    .header .date {
      font-size: 13px;
    }

    .header .admin-btn {
      background-color: yellow;
      border: none;
      padding: 5px 10px;
      font-weight: bold;
      cursor: pointer;
      margin-left: 15px;
    }

    /* Cards Section */
    .cards {
      display: flex;
      gap: 25px;
      margin-top: 25px;
      margin-bottom: 25px;
    }

    .card {
      background-color: #FFE500;
      padding: 15px 20px;
      border-radius: 4px;
      width: 30%;
      height: 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .card h3 {
      font-size: 12px;
      font-weight: 500;
      color: #555;
    }

    .card p {
      font-weight: bold;
      font-size: 14px;
      margin-top: 4px;
    }

    /* Tables */
    .table-section {
      display: flex;
      gap: 40px;
      flex-wrap: wrap;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .table-box {
      background-color: #FFE500;
      padding: 15px;
      border-radius: 4px;
      width: 47%;
    }

    .table-box h4 {
      margin-bottom: 10px;
      font-size: 13px;
      color: #444;
    }

    th, td {
      text-align: left;
      padding: 6px;
    }

    th {
      font-weight: 600;
      color: #333;
    }

    td {
      border-top: 1px solid rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="sidebar">
  <div class="logo-box">
    <img src="logo.png" alt="Logo Zarali’s Catering">
    <h3>Zarali’s Catering</h3>
  </div>

    <div class="menu">
    <a href="dashboard_admin.php" class="active">Dashboard</a>
    <a href="produk_admin.php">Produk</a>
    <a href="pesanan_admin.php">Pesanan</a>
    <a href="riwayat_transaksi_admin.php">Transaksi</a>
    <a href="laporan_penjualan_admin.php">Laporan Penjualan</a>
    <a href="logout_admin.php">Logout</a>
    </div>
    <div class="logout">
      <a href="#">Logout</a>
    </div>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Dashboard</h1>
      <div>
        <span class="date">Hari ini : <?= date('d M Y') ?></span>
        <button class="admin-btn">Admin</button>
      </div>
    </div>

    <div class="cards">
      <div class="card">
        <h3>Penjualan Hari Ini</h3>
        <p>Rp.<?= number_format($penjualan_hari_ini, 0, ',', '.') ?></p>
      </div>
      <div class="card">
        <h3>Pesanan Aktif</h3>
        <p><?= $pesanan_aktif ?></p>
      </div>
      <div class="card">
        <h3>Penjualan Hari Ini</h3>
        <p>Rp.<?= number_format($penjualan_hari_ini, 0, ',', '.') ?></p>
      </div>
    </div>

    <div class="table-section">
      <div class="table-box">
        <h4>Pesanan Terbaru</h4>
        <table>
          <thead>
            <tr>
              <th>Pesanan</th>
              <th>Pelanggan</th>
              <th>Status</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($q_pesanan)): ?>
              <tr>
                <td><?= $row['kode_pesanan'] ?></td>
                <td><?= $row['pelanggan'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>Rp.<?= number_format($row['total'], 0, ',', '.') ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <div class="table-box">
        <h4>Produk Ringkasan</h4>
        <table>
          <thead>
            <tr>
              <th>Produk</th>
              <th>Kategori</th>
              <th>Harga</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = mysqli_fetch_assoc($q_produk)): ?>
              <tr>
                <td><?= $p['nama'] ?></td>
                <td><?= $p['kategori'] ?></td>
                <td>Rp.<?= number_format($p['harga'], 0, ',', '.') ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
