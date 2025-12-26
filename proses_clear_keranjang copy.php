<?php
session_start();
include 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];

$delete = mysqli_query($koneksi, "
    DELETE FROM keranjang 
    WHERE id_pengguna = $id_pengguna
");

if ($delete) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengosongkan keranjang']);
}
?>