<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'koneksi.php';

$nama = $_POST['nama'];
$metode = $_POST['metode'];
$tanggal = date('Y-m-d');

mysqli_query($conn, "INSERT INTO pesanan 
(nama_pelanggan, metode_pembayaran, tanggal, total_harga)
VALUES ('$nama','$metode','$tanggal',0)");

$id_pesanan = mysqli_insert_id($conn);
$total = 0;

foreach ($_POST['produk'] as $i => $id_produk) {
    $qty = $_POST['qty'][$i];
    $p = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT harga FROM produk WHERE id=$id_produk"));

    $subtotal = $p['harga'] * $qty;
    $total += $subtotal;

    mysqli_query($conn, "INSERT INTO pesanan_detail
    (pesanan_id, produk_id, qty, harga)
    VALUES ($id_pesanan,$id_produk,$qty,{$p['harga']})");
}

mysqli_query($conn, "UPDATE pesanan SET total_harga=$total WHERE id=$id_pesanan");

header("Location: pesanan_admin.php");
