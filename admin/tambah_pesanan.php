<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'koneksi.php';

$produk = mysqli_query($conn, "SELECT * FROM produk");
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Pesanan</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
}

.container {
    width: 500px;
    background: #FFE500;
    margin: 30px auto;
    padding: 20px;
    border-radius: 10px;
}

h2 {
    text-align: center;
}

input, select {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
}

button {
    width: 100%;
    padding: 10px;
    background: green;
    color: white;
    border: none;
    border-radius: 20px;
}
</style>
</head>

<body>

<div class="container">
<h2>Tambah Pesanan</h2>

<form method="POST" action="simpan_pesanan.php">

    <label>Nama Pelanggan</label>
    <input type="text" name="nama" required>

    <label>Metode Pembayaran</label>
    <select name="metode" required>
        <option value="Tunai">Tunai</option>
        <option value="Debit">Debit</option>
    </select>

    <label>Produk</label>
    <select name="produk[]">
        <?php while($p = mysqli_fetch_assoc($produk)): ?>
        <option value="<?= $p['id'] ?>">
            <?= $p['nama'] ?> - Rp <?= number_format($p['harga'],0,',','.') ?>
        </option>
        <?php endwhile; ?>
    </select>

    <label>Qty</label>
    <input type="number" name="qty[]" value="1" min="1">

    <button type="submit">Simpan Pesanan</button>

</form>
</div>

</body>
</html>
