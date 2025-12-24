<?php
session_start();
include 'includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

// Validate input
if (!isset($_POST['id_produk']) || !isset($_POST['jumlah'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna']; // Ini sebenarnya users.id
$id_produk = (int) $_POST['id_produk'];
$jumlah = (int) $_POST['jumlah'];

// Validate quantity
if ($jumlah < 1 || $jumlah > 100) {
    echo json_encode([
        'success' => false,
        'message' => 'Jumlah tidak valid'
    ]);
    exit;
}

// Check if product exists
$check_produk = mysqli_query($koneksi, "SELECT id FROM produk WHERE id = $id_produk");
if (mysqli_num_rows($check_produk) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Produk tidak ditemukan'
    ]);
    exit;
}

// Check if product already in cart
$check_cart = mysqli_query($koneksi, "
    SELECT id_keranjang, jumlah 
    FROM keranjang 
    WHERE id_pengguna = $id_pengguna AND id_produk = $id_produk
");

if (mysqli_num_rows($check_cart) > 0) {
    // Update quantity
    $cart_item = mysqli_fetch_assoc($check_cart);
    $new_quantity = $cart_item['jumlah'] + $jumlah;
    
    $update = mysqli_query($koneksi, "
        UPDATE keranjang 
        SET jumlah = $new_quantity 
        WHERE id_keranjang = {$cart_item['id_keranjang']}
    ");
    
    if ($update) {
        // Get updated cart count
        $cart_result = mysqli_query($koneksi, "
            SELECT SUM(jumlah) as total 
            FROM keranjang 
            WHERE id_pengguna = $id_pengguna
        ");
        $cart_data = mysqli_fetch_assoc($cart_result);
        
        echo json_encode([
            'success' => true,
            'message' => 'Jumlah produk di keranjang berhasil diperbarui',
            'cart_count' => $cart_data['total']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memperbarui keranjang'
        ]);
    }
} else {
    // Insert new item
    $insert = mysqli_query($koneksi, "
        INSERT INTO keranjang (id_pengguna, id_produk, jumlah) 
        VALUES ($id_pengguna, $id_produk, $jumlah)
    ");
    
    if ($insert) {
        // Get updated cart count
        $cart_result = mysqli_query($koneksi, "
            SELECT SUM(jumlah) as total 
            FROM keranjang 
            WHERE id_pengguna = $id_pengguna
        ");
        $cart_data = mysqli_fetch_assoc($cart_result);
        
        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count' => $cart_data['total']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambahkan ke keranjang'
        ]);
    }
}
?>