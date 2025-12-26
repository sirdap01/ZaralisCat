<?php
session_start();
include '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../login.php");
    exit;
}

// Get order ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pesanan.php");
    exit;
}

$id_pesanan = (int) $_GET['id'];
$id_pengguna = (int) $_SESSION['id_pengguna'];

// Get order data (only fields that exist in users table)
$query_pesanan = mysqli_query($koneksi, "
    SELECT 
        p.*,
        u.nama as user_nama,
        u.email as user_email,
        u.created_at as user_created
    FROM pesanan p
    LEFT JOIN users u ON p.id_pengguna = u.id
    WHERE p.id_pesanan = $id_pesanan AND p.id_pengguna = $id_pengguna
");

// Check if order exists and belongs to user
if (mysqli_num_rows($query_pesanan) == 0) {
    $_SESSION['error'] = "Pesanan tidak ditemukan atau Anda tidak memiliki akses!";
    header("Location: pesanan.php");
    exit;
}

$pesanan = mysqli_fetch_assoc($query_pesanan);

// Get order items
$query_items = mysqli_query($koneksi, "
    SELECT * FROM pesanan_detail 
    WHERE pesanan_id = $id_pesanan
    ORDER BY id ASC
");

// Calculate cart count for badge
$cart_count = 0;
$cart_result = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM keranjang WHERE id_pengguna = $id_pengguna");
if ($cart_result) {
    $cart_data = mysqli_fetch_assoc($cart_result);
    $cart_count = $cart_data['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pesanan #<?= str_pad($id_pesanan, 4, '0', STR_PAD_LEFT) ?> - Zarali's Catering</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary-purple: #7B2CBF;
    --secondary-gold: #FFD700;
    --accent-purple: #9D4EDD;
    --text-dark: #222;
    --background-light: #FFFDF8;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: var(--background-light);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ===== HEADER ===== */
header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 16px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 85px;
    box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
    position: sticky;
    top: 0;
    z-index: 100;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 14px;
}

.logo {
    max-height: 55px;
    border-radius: 50%;
    border: 3px solid var(--secondary-gold);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

header h1 {
    font-weight: 700;
    font-size: 1.2rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

nav {
    display: flex;
    gap: 30px;
}

nav a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    padding: 8px 12px;
    border-radius: 6px;
}

nav a:hover {
    color: var(--secondary-gold);
    background-color: rgba(255, 255, 255, 0.1);
}

nav a.active {
    background-color: rgba(255, 215, 0, 0.2);
    color: var(--secondary-gold);
}

/* ===== FLOATING CART ===== */
.floating-cart {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 999;
}

.cart-button {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(123, 44, 191, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid var(--secondary-gold);
    text-decoration: none;
    position: relative;
}

.cart-button:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 12px 35px rgba(123, 44, 191, 0.5);
}

.cart-icon {
    font-size: 32px;
}

.cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: linear-gradient(135deg, #F44336, #E57373);
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    border: 2px solid white;
}

.cart-badge.empty {
    display: none;
}

/* ===== BANNER ===== */
.banner {
    background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
    padding: 30px 40px;
    color: white;
    text-align: center;
}

.banner h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--secondary-gold);
    text-shadow: 3px 3px 8px rgba(0,0,0,0.4);
    margin-bottom: 8px;
}

.banner p {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.9);
}

/* ===== MAIN CONTENT ===== */
.detail-container {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 40px;
    flex: 1;
}

.breadcrumb {
    margin-bottom: 30px;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 600;
}

.order-header {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
}

.order-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F0F0F0;
}

.order-number {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-purple);
}

.order-status {
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
}

.status-pending {
    background: linear-gradient(135deg, #FFC107, #FFD54F);
    color: #F57F17;
}

.status-dikonfirmasi {
    background: linear-gradient(135deg, #2196F3, #64B5F6);
    color: white;
}

.status-diproses {
    background: linear-gradient(135deg, #9C27B0, #BA68C8);
    color: white;
}

.status-dikirim {
    background: linear-gradient(135deg, #FF9800, #FFB74D);
    color: white;
}

.status-selesai {
    background: linear-gradient(135deg, #4CAF50, #81C784);
    color: white;
}

.status-dibatalkan {
    background: linear-gradient(135deg, #F44336, #E57373);
    color: white;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-size: 13px;
    color: #999;
    font-weight: 600;
}

.info-value {
    font-size: 15px;
    color: var(--text-dark);
    font-weight: 600;
}

/* ===== ORDER ITEMS ===== */
.order-items-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F0F0F0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table thead {
    background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), rgba(157, 78, 221, 0.05));
}

.items-table th {
    padding: 15px;
    text-align: left;
    font-size: 14px;
    font-weight: 700;
    color: var(--primary-purple);
    border-bottom: 2px solid #E0E0E0;
}

.items-table td {
    padding: 15px;
    border-bottom: 1px solid #F5F5F5;
    font-size: 14px;
}

.items-table tbody tr:last-child td {
    border-bottom: none;
}

.product-name {
    font-weight: 600;
    color: var(--text-dark);
}

.price {
    color: #666;
}

.subtotal {
    font-weight: 700;
    color: var(--accent-purple);
}

/* ===== ORDER SUMMARY ===== */
.order-summary {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    font-size: 15px;
    border-bottom: 1px solid #F5F5F5;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-top: 15px;
    padding-top: 20px;
    border-top: 2px solid #E0E0E0;
    border-bottom: none;
}

/* ===== DELIVERY INFO ===== */
.delivery-info {
    background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), rgba(157, 78, 221, 0.05));
    border-radius: 15px;
    padding: 25px;
    margin-top: 20px;
    border-left: 5px solid var(--secondary-gold);
}

.delivery-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 15px;
}

.delivery-detail {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 14px;
}

.delivery-icon {
    color: var(--accent-purple);
    font-size: 18px;
}

.delivery-text {
    flex: 1;
    color: #666;
    line-height: 1.6;
}

/* ===== NOTES ===== */
.notes-box {
    background: #FFF9E6;
    border-left: 4px solid #FFC107;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.notes-title {
    font-weight: 700;
    color: #F57F17;
    margin-bottom: 10px;
    font-size: 15px;
}

.notes-content {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
}

/* ===== ACTIONS ===== */
.actions-bar {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn {
    padding: 14px 30px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(123, 44, 191, 0.4);
}

.btn-secondary {
    background: white;
    color: var(--primary-purple);
    border: 2px solid var(--primary-purple);
}

.btn-secondary:hover {
    background: var(--primary-purple);
    color: white;
}

/* ===== FOOTER ===== */
footer {
    margin-top: auto;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    padding: 30px 40px;
    text-align: center;
    color: white;
}

.footer-brand {
    font-size: 18px;
    font-weight: 700;
    color: var(--secondary-gold);
    margin-bottom: 12px;
}

.footer-text {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    header {
        flex-direction: column;
        padding: 16px;
        gap: 16px;
    }

    nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
    }

    .detail-container {
        padding: 30px 16px;
    }

    .order-title {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .items-table {
        font-size: 13px;
    }

    .items-table th,
    .items-table td {
        padding: 10px 8px;
    }

    .actions-bar {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

</head>

<body>

<!-- FLOATING CART -->
<div class="floating-cart">
    <a href="keranjang/keranjang.php" class="cart-button" title="Lihat Keranjang">
        <div class="cart-icon">🛒</div>
        <span class="cart-badge <?= $cart_count == 0 ? 'empty' : '' ?>">
            <?= $cart_count ?>
        </span>
    </a>
</div>

<header>
    <div class="logo-container">
        <img src="../gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
        <h1>Zarali's Catering</h1>
    </div>

    <nav>
        <a href="../index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="testi.php">Testimoni</a>
        <a href="pesanan.php" class="active">Pesanan saya</a>
        <a href="contact.php">Hubungi kami</a>
        <a href="../about.html">Tentang kami</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<div class="banner">
    <h2>Detail Pesanan</h2>
    <p>Informasi lengkap pesanan Anda</p>
</div>

<div class="detail-container">
    <div class="breadcrumb">
        <a href="../index.php">🏠 Home</a> › 
        <a href="pesanan.php">Pesanan Saya</a> › 
        <span>Detail Pesanan #<?= str_pad($id_pesanan, 4, '0', STR_PAD_LEFT) ?></span>
    </div>

    <!-- ORDER HEADER -->
    <div class="order-header">
        <div class="order-title">
            <div class="order-number">Pesanan #<?= str_pad($id_pesanan, 4, '0', STR_PAD_LEFT) ?></div>
            <div class="order-status status-<?= strtolower($pesanan['status']) ?>">
                <?= htmlspecialchars($pesanan['status']) ?>
            </div>
        </div>

    <div class="order-info-grid">
        <div class="info-item">
            <div class="info-label">Tanggal Pesanan</div>
            <div class="info-value"><?= date('d F Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?> WIB</div>
        </div>
        <div class="info-item">
            <div class="info-label">Nama Pelanggan</div>
            <div class="info-value"><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Email</div>
            <div class="info-value"><?= htmlspecialchars($pesanan['user_email']) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Metode Pembayaran</div>
            <div class="info-value"><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></div>
        </div>
    </div>

        <!-- DELIVERY INFO -->
        <div class="delivery-info">
            <div class="delivery-title">📍 Informasi Pengiriman</div>
            <div class="delivery-detail">
                <span class="delivery-icon">🏠</span>
                <span class="delivery-text">
                    <strong>Alamat:</strong><br>
                    Data alamat tidak tersimpan di tabel pesanan saat ini
                </span>
            </div>
            <div class="delivery-detail">
                <span class="delivery-icon">💳</span>
                <span class="delivery-text">
                    <strong>Metode Pembayaran:</strong> <?= htmlspecialchars($pesanan['metode_pembayaran']) ?>
                </span>
            </div>
        </div>

        <?php if (!empty($pesanan['catatan'])): ?>
        <!-- NOTES -->
        <div class="notes-box">
            <div class="notes-title">📝 Catatan Pesanan</div>
            <div class="notes-content"><?= nl2br(htmlspecialchars($pesanan['catatan'])) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ORDER ITEMS -->
    <div class="order-items-section">
        <h3 class="section-title">Daftar Produk</h3>

        <table class="items-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $total_items = 0;
                while ($item = mysqli_fetch_assoc($query_items)): 
                    $total_items += $item['qty'];
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="product-name"><?= htmlspecialchars($item['nama_produk']) ?></td>
                    <td class="price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                    <td><?= $item['qty'] ?>x</td>
                    <td class="subtotal">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- ORDER SUMMARY -->
    <div class="order-summary">
        <h3 class="section-title">Ringkasan Pesanan</h3>

        <div class="summary-row">
            <span>Total Item</span>
            <span><?= $total_items ?> item</span>
        </div>

        <div class="summary-row total">
            <span>Total Bayar</span>
            <span>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="actions-bar">
        <a href="pesanan.php" class="btn btn-primary">
            <span>←</span>
            <span>Kembali ke Daftar Pesanan</span>
        </a>
        <a href="../menu.php" class="btn btn-secondary">
            <span>🛒</span>
            <span>Pesan Lagi</span>
        </a>
    </div>
</div>

<footer>
    <div class="footer-brand">Zarali's Catering</div>
    <div class="footer-text">
        Kelompok 5 | Melayani dengan Hati untuk Setiap Acara Anda<br>
        © 2024 Zarali's Catering. All Rights Reserved.
    </div>
</footer>

</body>
</html>