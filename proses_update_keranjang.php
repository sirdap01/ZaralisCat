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
$action = $_POST['action'];

// Verify that this cart item belongs to the user
$verify = mysqli_query($koneksi, "
    SELECT jumlah 
    FROM keranjang 
    WHERE id_keranjang = $id_keranjang AND id_pengguna = $id_pengguna
");

if (mysqli_num_rows($verify) == 0) {
    echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
    exit;
}

$current = mysqli_fetch_assoc($verify);
$new_quantity = $current['jumlah'];

if ($action === 'plus') {
    $new_quantity++;
} elseif ($action === 'minus' && $new_quantity > 1) {
    $new_quantity--;
}

$update = mysqli_query($koneksi, "
    UPDATE keranjang 
    SET jumlah = $new_quantity 
    WHERE id_keranjang = $id_keranjang
");

if ($update) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal update']);
}
?>