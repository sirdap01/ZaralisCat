<?php
include "koneksi.php"; // koneksi database

// jika tombol tampilkan ditekan
$tanggal = "";
if (isset($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];
    $query = mysqli_query($conn, "SELECT * FROM transaksi WHERE tanggal = '$tanggal' ORDER BY id ASC");
} else {
    // default tampilkan semua
    $query = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id ASC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Transaksi</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background:#fffbe6;
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

        /* Konten utama */
        .content {
            margin-left: 230px;
            padding: 20px;
        }

        .box {
            background: #ffe600;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        table {
            width: 100%;
            background: #ffe600;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }

        table th {
            background: #ffd900;
        }

        .btn {
            padding: 8px 15px;
            background: green;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0a7a00;
        }

        .input-tgl {
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h3>Zarali's Catering</h3>
     <a href="dashboard_admin.php">Dashboard</a>
    <a href="produk_admin.php" >Produk</a>
    <a href="pesanan_admin.php">Pesanan</a>
    <a href="riwayat_transaksi_admin.php"class="active">Transaksi</a>
    <a href="laporan_penjualan_admin.php">Laporan Penjualan</a>
    <a href="logout_admin.php">Logout</a>
</div>

<!-- Konten -->
<div class="content">
    <h2>Riwayat Transaksi</h2>

    <form method="GET">
        <label>Tanggal :</label>
        <input type="date" name="tanggal" class="input-tgl" value="<?= $tanggal ?>">
        <button type="submit" class="btn">Tampilkan</button>
    </form>

    <div class="box">
        <h4>Riwayat Transaksi</h4>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama Pelanggan</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Metode Pembayaran</th>
                <th>Tanggal</th>
            </tr>

            <?php
            if (mysqli_num_rows($query) > 0) {
                while ($row = mysqli_fetch_assoc($query)) {
                    echo "
                    <tr>
                        <td>{$row['id']}</td>
                        <td>{$row['nama_pelanggan']}</td>
                        <td>Rp" . number_format($row['total_harga'],0,',','.') . "</td>
                        <td>{$row['status']}</td>
                        <td>{$row['metode_pembayaran']}</td>
                        <td>{$row['tanggal']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Tidak ada transaksi</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>
