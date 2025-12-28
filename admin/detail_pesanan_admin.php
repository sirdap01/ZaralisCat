<?php
session_start();
include 'koneksi.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: pesanan_admin.php");
    exit;
}

$id = (int) $_GET['id'];

// Get order data
$pesanan_query = mysqli_query($koneksi, "
    SELECT 
        p.*,
        u.nama as user_nama,
        u.email as user_email
    FROM pesanan p
    LEFT JOIN users u ON p.id_pengguna = u.id
    WHERE p.id_pesanan = $id
");

if (!$pesanan_query || mysqli_num_rows($pesanan_query) == 0) {
    die("Pesanan tidak ditemukan!");
}

$pesanan = mysqli_fetch_assoc($pesanan_query);

// Get order details
$detail = mysqli_query($koneksi, "
    SELECT 
        pd.*,
        p.nama as nama_produk,
        p.gambar
    FROM pesanan_detail pd
    JOIN produk p ON pd.produk_id = p.id
    WHERE pd.pesanan_id = $id
");

if (!$detail) {
    die("Error: " . mysqli_error($koneksi));
}

$total_items = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pesanan #<?= $id ?> - Zarali's Catering</title>
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
    padding: 40px 20px;
}

/* ===== CONTAINER ===== */
.container {
    max-width: 1000px;
    margin: 0 auto;
}

/* ===== HEADER ===== */
.detail-header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 30px;
    border-radius: 20px 20px 0 0;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.2);
}

.detail-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--secondary-gold);
    margin-bottom: 10px;
}

.detail-header .order-id {
    font-size: 16px;
    opacity: 0.9;
}

/* ===== CONTENT ===== */
.detail-content {
    background: white;
    padding: 0;
    border-radius: 0 0 20px 20px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
}

.info-section {
    padding: 30px;
    border-bottom: 2px solid #F0F0F0;
}

.info-section:last-of-type {
    border-bottom: none;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-grid {
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
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
}

.status-pending {
    background: linear-gradient(135deg, #FFF3CD, #FFE69C);
    color: #856404;
}

.status-proses {
    background: linear-gradient(135deg, #D1ECF1, #BEE5EB);
    color: #0C5460;
}

.status-selesai {
    background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
    color: #155724;
}

.status-lunas {
    background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
    color: #155724;
}

.status-batal {
    background: linear-gradient(135deg, #F8D7DA, #F5C2C7);
    color: #721C24;
}

/* ===== PRODUCTS TABLE ===== */
.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table thead {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
}

.products-table th {
    padding: 15px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
}

.products-table td {
    padding: 15px;
    border-bottom: 1px solid #E0E0E0;
    font-size: 14px;
}

.products-table tbody tr:last-child td {
    border-bottom: none;
}

.product-img {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
}

/* ===== SUMMARY ===== */
.order-summary {
    padding: 30px;
    background: #F9F9F9;
    border-radius: 0 0 20px 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    font-size: 16px;
}

.summary-row.total {
    border-top: 2px solid #E0E0E0;
    margin-top: 10px;
    padding-top: 20px;
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-purple);
}

/* ===== ACTIONS ===== */
.actions {
    padding: 30px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    justify-content: center;
}

.btn {
    padding: 14px 30px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-back {
    background: linear-gradient(135deg, #757575, #9E9E9E);
    color: white;
    box-shadow: 0 4px 12px rgba(117, 117, 117, 0.3);
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(117, 117, 117, 0.4);
}

.btn-print {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
}

.btn-print:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(123, 44, 191, 0.4);
}

.btn-edit {
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    color: white;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(33, 150, 243, 0.4);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    body {
        padding: 20px 10px;
    }

    .detail-header {
        padding: 20px;
    }

    .detail-header h1 {
        font-size: 22px;
    }

    .info-section {
        padding: 20px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .products-table {
        font-size: 12px;
    }

    .products-table th,
    .products-table td {
        padding: 10px;
    }

    .actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media print {
    .actions {
        display: none;
    }
}
</style>
</head>

<body>

<div class="container">
    <!-- HEADER -->
    <div class="detail-header">
        <h1>📋 Detail Pesanan</h1>
        <div class="order-id">ID Pesanan: #<?= $id ?></div>
    </div>

    <!-- CONTENT -->
    <div class="detail-content">
        
        <!-- INFORMASI PESANAN -->
        <div class="info-section">
            <h3 class="section-title">
                <span>📦</span>
                <span>Informasi Pesanan</span>
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nomor Pesanan</span>
                    <span class="info-value">#<?= $pesanan['id_pesanan'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Pesanan</span>
                    <span class="info-value"><?= date('d F Y', strtotime($pesanan['tanggal_pesanan'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status Pesanan</span>
                    <span class="status-badge status-<?= strtolower($pesanan['status']) ?>">
                        <?= $pesanan['status'] ?>
                    </span>
                </div>
                <?php if (!empty($pesanan['tanggal_acara'])): ?>
                <div class="info-item">
                    <span class="info-label">Tanggal Acara</span>
                    <span class="info-value"><?= date('d F Y', strtotime($pesanan['tanggal_acara'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- INFORMASI PELANGGAN -->
        <div class="info-section">
            <h3 class="section-title">
                <span>👤</span>
                <span>Informasi Pelanggan</span>
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nama Pelanggan</span>
                    <span class="info-value"><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></span>
                </div>
                <?php if ($pesanan['user_email']): ?>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= htmlspecialchars($pesanan['user_email']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- INFORMASI PEMBAYARAN -->
        <div class="info-section">
            <h3 class="section-title">
                <span>💳</span>
                <span>Informasi Pembayaran</span>
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Metode Pembayaran</span>
                    <span class="info-value"><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></span>
                </div>
                <?php if (!empty($pesanan['bukti_transfer'])): ?>
                <div class="info-item">
                    <span class="info-label">Bukti Transfer</span>
                    <span class="info-value">
                        <a href="../uploads/bukti_transfer/<?= htmlspecialchars($pesanan['bukti_transfer']) ?>" 
                           target="_blank" 
                           style="color: var(--primary-purple); text-decoration: underline;">
                            📎 Lihat Bukti Transfer
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($pesanan['catatan'])): ?>
            <div class="info-item" style="margin-top: 15px;">
                <span class="info-label">Catatan</span>
                <span class="info-value"><?= nl2br(htmlspecialchars($pesanan['catatan'])) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- DAFTAR PRODUK -->
        <div class="info-section">
            <h3 class="section-title">
                <span>🍽️</span>
                <span>Daftar Produk</span>
            </h3>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    while($d = mysqli_fetch_assoc($detail)): 
                        $subtotal = $d['qty'] * $d['harga'];
                        $grand_total += $subtotal;
                        $total_items += $d['qty'];
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($d['nama_produk']) ?></strong>
                        </td>
                        <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                        <td><?= $d['qty'] ?></td>
                        <td><strong>Rp <?= number_format($subtotal, 0, ',', '.') ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- SUMMARY -->
        <div class="order-summary">
            <div class="summary-row">
                <span>Total Item</span>
                <span><strong><?= $total_items ?> item</strong></span>
            </div>
            <div class="summary-row total">
                <span>Total Bayar</span>
                <span>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="actions">
            <a href="pesanan_admin.php" class="btn btn-back">
                <span>←</span>
                <span>Kembali</span>
            </a>
            <a href="edit_status_pesanan.php?id=<?= $id ?>" class="btn btn-edit">
                <span>✏️</span>
                <span>Edit Status</span>
            </a>
            <a href="cetak_invoice.php?id=<?= $id ?>" class="btn btn-print" target="_blank">
                <span>🖨️</span>
                <span>Cetak Invoice</span>
            </a>
        </div>
    </div>
</div>

</body>
</html>