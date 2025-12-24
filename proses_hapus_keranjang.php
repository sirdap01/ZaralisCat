<?php
session_start();
include 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];
$id_keranjang = (int) $_POST['id_keranjang'];

// Verify ownership
$verify = mysqli_query($koneksi, "
    SELECT id_keranjang 
    FROM keranjang 
    WHERE id_keranjang = $id_keranjang AND id_pengguna = $id_pengguna
");

if (mysqli_num_rows($verify) == 0) {
    echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
    exit;
}

$delete = mysqli_query($koneksi, "
    DELETE FROM keranjang 
    WHERE id_keranjang = $id_keranjang
");

if ($delete) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus']);
}
?>