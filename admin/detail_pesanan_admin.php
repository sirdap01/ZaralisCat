<?php
include 'koneksi.php';
$id = $_GET['id'];

$pesanan = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM pesanan WHERE id=$id"));

$detail = mysqli_query($conn,"
    SELECT p.nama, d.qty, d.harga
    FROM pesanan_detail d
    JOIN produk p ON d.produk_id=p.id
    WHERE d.pesanan_id=$id
");
?>

<h2>Detail Pesanan</h2>
Nama: <?= $pesanan['nama_pelanggan'] ?><br>
Status: <?= $pesanan['status'] ?><br><br>

<table border="1" cellpadding="8">
<tr><th>Produk</th><th>Qty</th><th>Harga</th></tr>
<?php while($d=mysqli_fetch_assoc($detail)): ?>
<tr>
<td><?= $d['nama'] ?></td>
<td><?= $d['qty'] ?></td>
<td>Rp <?= number_format($d['harga'],0,',','.') ?></td>
</tr>
<?php endwhile; ?>
</table>
