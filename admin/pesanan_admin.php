<?php
// PHP Sederhana: Contoh data pesanan yang seharusnya diambil dari database
$orders = [
    ['no' => 12, 'nama' => 'Ian', 'harga' => 'Rp45,000', 'status' => 'Lunas', 'metode' => 'Debit', 'tanggal' => '2090-09-01'],
    ['no' => 13, 'nama' => 'Kiki', 'harga' => 'Rp95,000', 'status' => 'Lunas', 'metode' => 'Tunai', 'tanggal' => '2090-09-01'],
    ['no' => 14, 'nama' => 'Kipli', 'harga' => 'Rp201,000', 'status' => 'Lunas', 'metode' => 'Debit', 'tanggal' => '2090-09-01'],
    ['no' => 15, 'nama' => 'Jahroni', 'harga' => 'Rp405,000', 'status' => 'Lunas', 'metode' => 'Tunai', 'tanggal' => '2029-09-01'],
    ['no' => 16, 'nama' => 'Ahman', 'harga' => 'Rp40,000', 'status' => 'Lunas', 'metode' => 'Tunai', 'tanggal' => '2090-09-01'],
    ['no' => 17, 'nama' => 'Putra', 'harga' => 'Rp70,000', 'status' => 'Lunas', 'metode' => 'Tunai', 'tanggal' => '2090-09-01'],
];

// Untuk tanggal hari ini
$today = '28 Okt 2023'; // Karena di gambar tertulis 28 Okt 2023
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Zarali's Catering</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar (Bagian Ungu) --- */
        .sidebar {
            width: 250px;
            background-color: #6a0dad; /* Warna Ungu */
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 5px;
            border: 3px solid white;
        }

        .logo-text {
            display: block;
            font-size: 1.2em;
            font-weight: bold;
        }

        .nav-menu ul {
            list-style: none;
            padding: 0;
        }

        .nav-menu li a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background-color 0.2s;
        }

        .nav-menu li a:hover {
            background-color: #8a2be2;
        }

        .nav-menu li.active {
            background-color: #8a2be2;
            margin: 5px 0;
            padding: 0;
            border-left: 5px solid yellow;
        }

        .nav-menu li.active a {
            font-weight: bold;
        }

        .logout-area {
            margin-top: auto;
            padding: 20px;
        }

        .logout-btn {
            display: block;
            text-align: center;
            background-color: #ff0000;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        /* --- Konten Utama --- */
        .main-content {
            flex-grow: 1;
            padding: 20px 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .date-info {
            margin-right: 15px;
            font-size: 0.9em;
            color: #555;
        }

        .admin-btn {
            background-color: #ffd700;
            color: black;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        /* --- Area Kontrol (Filter dan Tombol Tambah) --- */
        .controls-top, .controls-date {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 250px;
            margin-right: 15px;
        }

        .tambah-pesanan-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .controls-date label {
            margin-right: 10px;
        }
        
        .controls-date .date-input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 120px; /* Diperkecil sedikit */
            margin: 0 10px;
            background-color: #ffd700; /* Latar belakang kuning di input tanggal */
            text-align: center;
            font-weight: bold;
        }

        .controls-date .tampilkan-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        /* --- Tabel Pesanan (Background Kuning Besar) --- */
        .order-table-container {
            background-color: #fff066; /* Kuning Terang */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th, .order-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ffcc00;
        }

        .order-table th {
            background-color: #ffe840;
            font-weight: bold;
            color: #333;
        }

        .order-table td {
            background-color: #fff8b3;
        }
        
        .order-table tbody tr:nth-child(even) td {
            /* Sedikit variasi warna pada baris genap jika diperlukan,
               tapi di gambar tidak terlalu terlihat perbedaannya */
            background-color: #fffaf0; 
        }

        /* Tombol Aksi */
        .action-btn {
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            margin-right: 5px;
            display: inline-block;
            cursor: pointer;
            border: none;
        }

        .detail-btn {
            background-color: #00bfff; /* Biru muda/Cyan untuk Detail */
        }

        .cetak-btn {
            background-color: #007bff; /* Biru untuk Cetak */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo-area">
                <img src="logo.png" alt="Zarali's Catering Logo" class="logo-img">
                <span class="logo-text">Zarali's Catering</span>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="dashboard_admin.php">Dashboard</a></li>
                    <li><a href="#produk_admin.php">Produk</a></li>
                    <li class="active"><a href="pesanan_admin.php">Pesanan</a></li>
                    <li><a href="riwayat_transaksi_admin.php">Transaksi</a></li>
                    <li><a href="#">Laporan Penjualan</a></li>
                    <li><a href="laporan_penjualan_admin.php">Laporan Penjualan</a>
                    <li><a href="logout_admin.php">Logout</a>
                </ul>
            </nav>
            <div class="logout-area">
                <a href="#" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="main-content">
            <header class="header">
                <h1>Daftar Pesanan</h1>
                <div class="header-right">
                    <span class="date-info">Hari ini : <?php echo $today; ?></span>
                    <button class="admin-btn">Admin</button>
                </div>
            </header>

            <div class="controls-top">
                <input type="text" placeholder="Cari nama pelanggan..." class="search-input">
                <button class="tambah-pesanan-btn">+ Tambahkan Pesanan</button>
            </div>

            <div class="controls-date">
                <label for="tanggal">Tanggal :</label>
                <input type="text" id="tanggal" value="09/01/2025" class="date-input">
                <span style="font-size: 1.5em; cursor: pointer; margin-right: 15px;">🗓️</span> 
                <button class="tampilkan-btn">Tampilkan</button>
            </div>

            <div class="order-table-container">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Metode Pembayaran</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Loop PHP untuk menampilkan data
                        foreach ($orders as $order): 
                        ?>
                        <tr>
                            <td><?php echo $order['no']; ?></td>
                            <td><?php echo $order['nama']; ?></td>
                            <td><?php echo $order['harga']; ?></td>
                            <td><?php echo $order['status']; ?></td>
                            <td><?php echo $order['metode']; ?></td>
                            <td><?php echo $order['tanggal']; ?></td>
                            <td>
                                <button class="action-btn detail-btn">Detail</button>
                                <button class="action-btn cetak-btn">Cetak</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>