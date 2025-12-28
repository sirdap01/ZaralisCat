<?php
include 'koneksi.php';
$id = $_GET['id'];

$p = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM pesanan WHERE id_pesanan=$id"));

$d = mysqli_query($conn,"
    SELECT pr.nama, dt.qty, dt.harga
    FROM pesanan_detail dt
    JOIN produk pr ON dt.produk_id=pr.id
    WHERE dt.pesanan_id=$id
");
?>

<script>window.print()</script>

<h2>INVOICE</h2>
Nama: <?= $p['nama_pelanggan'] ?><br>
Tanggal: <?= $p['tanggal_pesanan'] ?><br><br>

<table border="1" cellpadding="8">
<tr><th>Produk</th><th>Qty</th><th>Harga</th></tr>
<?php while($r=mysqli_fetch_assoc($d)): ?>
<tr>
<td><?= $r['nama'] ?></td>
<td><?= $r['qty'] ?></td>
<td>Rp <?= number_format($r['harga'],0,',','.') ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Total: Rp <?= number_format($p['total_harga'],0,',','.') ?></h3>
