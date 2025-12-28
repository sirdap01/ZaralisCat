<?php
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php");
    exit;
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];

// Get form data
$no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
$alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
$tanggal_acara = mysqli_real_escape_string($koneksi, $_POST['tanggal_acara']);
$waktu_acara = mysqli_real_escape_string($koneksi, $_POST['waktu_acara'] ?? '');
$metode_pembayaran = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
$catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');

// Validate minimum date (H+3)
$min_date = date('Y-m-d', strtotime('+3 days'));
if ($tanggal_acara < $min_date) {
    $_SESSION['error'] = "Tanggal acara minimal H+3 dari hari ini!";
    header("Location: checkout.php");
    exit;
}

// Handle file upload for bukti transfer
$bukti_transfer_filename = null;

if (($metode_pembayaran === 'QRIS' || $metode_pembayaran === 'Transfer') && isset($_FILES['bukti_transfer'])) {
    $file = $_FILES['bukti_transfer'];
    
    // Check if file was uploaded
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Format file harus JPG, PNG, atau PDF!";
            $_SESSION['old_checkout'] = $_POST;
            header("Location: checkout.php");
            exit;
        }
        
        // Validate file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = "Ukuran file maksimal 2MB!";
            $_SESSION['old_checkout'] = $_POST;
            header("Location: checkout.php");
            exit;
        }
        
        // Generate unique filename
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $bukti_transfer_filename = 'bukti_' . time() . '_' . uniqid() . '.' . $file_ext;
        
        // Set upload directory (relative from proses_checkout.php in users/checkout/)
        $upload_dir = 'uploads/bukti_transfer/';
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Move uploaded file
        $upload_path = $upload_dir . $bukti_transfer_filename;
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            $_SESSION['error'] = "Gagal upload file bukti transfer!";
            $_SESSION['old_checkout'] = $_POST;
            header("Location: checkout.php");
            exit;
        }
    } else if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        // File upload required for QRIS/Transfer
        $_SESSION['error'] = "Bukti transfer wajib diupload untuk metode QRIS/Transfer Bank!";
        $_SESSION['old_checkout'] = $_POST;
        header("Location: checkout.php");
        exit;
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat upload file (Error code: " . $file['error'] . ")";
        $_SESSION['old_checkout'] = $_POST;
        header("Location: checkout.php");
        exit;
    }
} else if (($metode_pembayaran === 'QRIS' || $metode_pembayaran === 'Transfer')) {
    // No file uploaded but required
    $_SESSION['error'] = "Bukti transfer wajib diupload untuk metode QRIS/Transfer Bank!";
    $_SESSION['old_checkout'] = $_POST;
    header("Location: checkout.php");
    exit;
}

// Get cart items
$query_cart = mysqli_query($koneksi, "
    SELECT 
        k.id_produk,
        k.jumlah,
        p.nama,
        p.harga,
        (k.jumlah * p.harga) as subtotal
    FROM keranjang k
    JOIN produk p ON k.id_produk = p.id
    WHERE k.id_pengguna = $id_pengguna
");

// Check if cart is empty
if (mysqli_num_rows($query_cart) == 0) {
    $_SESSION['error'] = "Keranjang Anda kosong!";
    header("Location: ../keranjang/keranjang.php");
    exit;
}

// Calculate total
$cart_items = [];
$total_harga = 0;

while ($item = mysqli_fetch_assoc($query_cart)) {
    $cart_items[] = $item;
    $total_harga += $item['subtotal'];
}

// Get user data
$user_query = mysqli_query($koneksi, "SELECT nama, email FROM users WHERE id = $id_pengguna");
$user_data = mysqli_fetch_assoc($user_query);
$nama_pelanggan = $user_data['nama'];

// Start transaction
mysqli_begin_transaction($koneksi);

try {
    // 1. Insert into pesanan table
    $bukti_sql_column = $bukti_transfer_filename ? "bukti_transfer," : "";
    $bukti_sql_value = $bukti_transfer_filename ? "'$bukti_transfer_filename'," : "";
    

    if (empty($tanggal_acara)) {
    $_SESSION['error'] = "Tanggal acara wajib diisi!";
    $_SESSION['old_checkout'] = $_POST;
    header("Location: checkout.php");
    exit;
}

    $insert_pesanan = mysqli_query($koneksi, "
        INSERT INTO pesanan (
            id_pengguna,
            nama_pelanggan,
            total_harga,
            status,
            metode_pembayaran,
            tanggal_pesanan,
            catatan,
            bukti_transfer,
            tanggal_acara
        ) VALUES (
            $id_pengguna,
            '$nama_pelanggan',
            $total_harga,
            'Pending',
            '$metode_pembayaran',
            NOW(),
            '$catatan',
            ".($bukti_transfer_filename ? "'$bukti_transfer_filename'" : "NULL").",
            '$tanggal_acara'
        )

    ");

    if (!$insert_pesanan) {
        throw new Exception("Gagal membuat pesanan: " . mysqli_error($koneksi));
    }

    // Get the inserted order ID
    $id_pesanan = mysqli_insert_id($koneksi);

    // 2. Insert each item into pesanan_detail table
    foreach ($cart_items as $item) {
        $id_produk = (int) $item['id_produk'];
        $nama_produk = mysqli_real_escape_string($koneksi, $item['nama']);
        $qty = (int) $item['jumlah'];
        $harga = (float) $item['harga'];
        $subtotal = (float) $item['subtotal'];

        $insert_detail = mysqli_query($koneksi, "
            INSERT INTO pesanan_detail (
                pesanan_id,
                produk_id,
                nama_produk,
                qty,
                harga,
                subtotal
            ) VALUES (
                $id_pesanan,
                $id_produk,
                '$nama_produk',
                $qty,
                $harga,
                $subtotal
            )
        ");

        if (!$insert_detail) {
            throw new Exception("Gagal menyimpan detail pesanan: " . mysqli_error($koneksi));
        }
    }

    // 3. Clear the cart
    $delete_cart = mysqli_query($koneksi, "
        DELETE FROM keranjang 
        WHERE id_pengguna = $id_pengguna
    ");

    if (!$delete_cart) {
        throw new Exception("Gagal mengosongkan keranjang: " . mysqli_error($koneksi));
    }

    // Commit transaction
    mysqli_commit($koneksi);

    // Store order info in session for confirmation page
    $_SESSION['order_success'] = [
        'id_pesanan' => $id_pesanan,
        'total_harga' => $total_harga,
        'tanggal_acara' => $tanggal_acara,
        'waktu_acara' => $waktu_acara,
        'alamat' => $alamat,
        'metode_pembayaran' => $metode_pembayaran,
        'total_items' => count($cart_items),
        'bukti_transfer' => $bukti_transfer_filename
    ];

    // Redirect to success page
    header("Location: konfirmasi_pesanan.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($koneksi);
    
    // Delete uploaded file if exists
    if ($bukti_transfer_filename && file_exists('uploads/bukti_transfer/' . $bukti_transfer_filename)) {
        unlink('uploads/bukti_transfer/' . $bukti_transfer_filename);
    }
    
    $_SESSION['error'] = $e->getMessage();
    $_SESSION['old_checkout'] = $_POST;
    header("Location: checkout.php");
    exit;
}
?>