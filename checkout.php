<?php
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php");
    exit;
}
$old = $_SESSION['old_checkout'] ?? [];
$error = $_SESSION['error'] ?? null;

unset($_SESSION['old_checkout'], $_SESSION['error']);

$id_pengguna = (int) $_SESSION['id_pengguna'];

// Get user data
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $id_pengguna");
$user_data = mysqli_fetch_assoc($user_query);

// Get cart items
$query_cart = mysqli_query($koneksi, "
    SELECT 
        k.id_keranjang,
        k.id_produk,
        k.jumlah,
        p.nama,
        p.harga,
        p.gambar,
        (k.jumlah * p.harga) as subtotal
    FROM keranjang k
    JOIN produk p ON k.id_produk = p.id
    WHERE k.id_pengguna = $id_pengguna
");

// Check if cart is empty
if (mysqli_num_rows($query_cart) == 0) {
    header("Location: ../keranjang/keranjang.php");
    exit;
}

// Calculate totals
$cart_items = [];
$total_harga = 0;
$total_items = 0;

while ($item = mysqli_fetch_assoc($query_cart)) {
    $cart_items[] = $item;
    $total_harga += $item['subtotal'];
    $total_items += $item['jumlah'];
}

// Get cart count for badge
$cart_count = $total_items;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Zarali's Catering</title>
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
    align-items: center;
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

/* ===== BANNER ===== */
.banner {
    background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
    padding: 40px 40px;
    color: white;
    text-align: center;
}

.banner h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--secondary-gold);
    text-shadow: 3px 3px 8px rgba(0,0,0,0.4);
    margin-bottom: 10px;
}

.banner p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
}

/* ===== PROGRESS STEPS ===== */
.progress-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    padding: 30px 40px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.step {
    display: flex;
    align-items: center;
    gap: 10px;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    transition: all 0.3s ease;
}

.step.completed .step-number {
    background: linear-gradient(135deg, #4CAF50, #81C784);
    color: white;
}

.step.active .step-number {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.4);
}

.step.inactive .step-number {
    background: #E0E0E0;
    color: #999;
}

.step-label {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-dark);
}

.step.inactive .step-label {
    color: #999;
}

.step-arrow {
    color: #BDBDBD;
    font-size: 20px;
}

/* ===== MAIN CONTENT ===== */
.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 40px;
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

.checkout-content {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 30px;
    align-items: start;
}

/* ===== CHECKOUT FORM ===== */
.checkout-form {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F0F0F0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-section {
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.required {
    color: #F44336;
    margin-left: 4px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    font-size: 15px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    transition: all 0.3s ease;
    background-color: #FAFAFA;
    font-family: 'Poppins', sans-serif;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-purple);
    background-color: white;
    box-shadow: 0 0 0 3px rgba(123,44,191,0.1);
}

.form-group input:read-only {
    background-color: #F5F5F5;
    color: #666;
    cursor: not-allowed;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.input-hint {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
    display: block;
}

/* ===== FILE UPLOAD ===== */
.file-upload-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-upload-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #FAFAFA;
    border: 2px dashed #E0E0E0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload-label:hover {
    border-color: var(--primary-purple);
    background: rgba(123, 44, 191, 0.05);
}

.file-upload-input {
    position: absolute;
    left: -9999px;
}

.file-upload-icon {
    font-size: 24px;
}

.file-upload-text {
    flex: 1;
    font-size: 14px;
    color: #666;
}

.file-preview {
    margin-top: 10px;
    padding: 10px;
    background: #F0F0F0;
    border-radius: 8px;
    display: none;
    align-items: center;
    gap: 10px;
}

.file-preview.show {
    display: flex;
}

.file-preview-icon {
    font-size: 20px;
}

.file-preview-name {
    flex: 1;
    font-size: 13px;
    color: var(--text-dark);
    font-weight: 600;
}

.file-remove {
    background: #F44336;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
}

/* ===== PAYMENT OPTIONS ===== */
.payment-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.payment-option {
    position: relative;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    border: 2px solid #E0E0E0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #FAFAFA;
    text-align: center;
    min-height: 100px;
}

.payment-option input[type="radio"]:checked + .payment-label {
    border-color: var(--primary-purple);
    background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), rgba(157, 78, 221, 0.05));
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.2);
}

.payment-icon {
    font-size: 32px;
    margin-bottom: 8px;
}

.payment-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
}

/* ===== UPLOAD SECTION (HIDDEN BY DEFAULT) ===== */
.upload-section {
    display: none;
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), rgba(157, 78, 221, 0.05));
    border-radius: 12px;
    border-left: 4px solid var(--primary-purple);
}

.upload-section.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.upload-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--primary-purple);
}

/* ===== ORDER SUMMARY ===== */
.order-summary {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
    position: sticky;
    top: 120px;
}

.summary-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F0F0F0;
}

.summary-items {
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #F5F5F5;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.item-qty {
    font-size: 13px;
    color: #666;
}

.item-price {
    font-size: 14px;
    font-weight: 700;
    color: var(--accent-purple);
    text-align: right;
}

.summary-totals {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #F0F0F0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 15px;
}

.summary-row.total {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #F0F0F0;
}

.btn-submit {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #00C853, #00E676);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 25px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 200, 83, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 200, 83, 0.4);
}

.btn-submit:disabled {
    background: #BDBDBD;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-back {
    width: 100%;
    padding: 14px;
    background: white;
    color: var(--primary-purple);
    border: 2px solid var(--primary-purple);
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 15px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-back:hover {
    background: var(--primary-purple);
    color: white;
    transform: translateY(-2px);
}

/* ===== FOOTER ===== */
footer {
    margin-top: auto;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    padding: 30px 40px;
    text-align: center;
    color: white;
    box-shadow: 0 -4px 12px rgba(123, 44, 191, 0.3);
}

.footer-brand {
    font-size: 18px;
    font-weight: 700;
    color: var(--secondary-gold);
    margin-bottom: 12px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.footer-text {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .checkout-content {
        grid-template-columns: 1fr;
    }

    .order-summary {
        position: static;
    }

    .payment-options {
        grid-template-columns: 1fr;
    }
}

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

    .checkout-container {
        padding: 30px 16px;
    }

    .progress-steps {
        flex-direction: column;
        gap: 15px;
    }

    .step-arrow {
        transform: rotate(90deg);
    }
}
</style>

</head>

<body>

<header>
    <div class="logo-container">
        <img src="../../gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
        <h1>Zarali's Catering</h1>
    </div>

    <nav>
        <a href="../../index.php">Home</a>
        <a href="../../menu.php">Menu</a>
        <a href="../testi.php">Testimoni</a>
        <a href="../pesanan.php">Pesanan saya</a>
        <a href="../../users/contact.php">Hubungi kami</a>
        <a href="../../about.php">Tentang kami</a>
        <a href="../../logout.php">Logout</a>
    </nav>
</header>

<div class="banner">
    <h2>Checkout Pesanan</h2>
    <p>Lengkapi data pesanan Anda</p>
</div>

<!-- PROGRESS STEPS -->
<div class="progress-steps">
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Keranjang</div>
    </div>
    <div class="step-arrow">→</div>
    <div class="step active">
        <div class="step-number">2</div>
        <div class="step-label">Checkout</div>
    </div>
    <div class="step-arrow">→</div>
    <div class="step inactive">
        <div class="step-number">3</div>
        <div class="step-label">Konfirmasi</div>
    </div>
</div>

<div class="checkout-container">
    <div class="breadcrumb">
        <a href="../../index.php">🏠 Home</a> › 
        <a href="../keranjang/keranjang.php">Keranjang</a> › 
        <span>Checkout</span>
    </div>

<?php if ($error): ?>
    <div style="background:#ffebee;color:#c62828;padding:12px;border-radius:8px;margin-bottom:20px;">
        ⚠️ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

    <form action="proses_checkout.php" method="POST" enctype="multipart/form-data" id="checkoutForm">
        <div class="checkout-content">
            <!-- CHECKOUT FORM -->
            <div class="checkout-form">
                <!-- DATA PELANGGAN -->
                <div class="form-section">
                    <h3 class="section-title">
                        <span>👤</span>
                        <span>Data Pelanggan</span>
                    </h3>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($user_data['nama']) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="no_telepon">Nomor Telepon <span class="required">*</span></label>
                        <input type="tel" id="no_telepon" name="no_telepon" required>
                        <span class="input-hint">Contoh: 08123456789</span>
                    </div>
                </div>

                <!-- DETAIL PENGIRIMAN -->
                <div class="form-section">
                    <h3 class="section-title">
                        <span>📍</span>
                        <span>Detail Pengiriman</span>
                    </h3>

                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap <span class="required">*</span></label>
                        <textarea name="alamat" required><?= htmlspecialchars($old['alamat'] ?? '') ?></textarea>
                        <span class="input-hint">Sertakan nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan</span>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_acara">Tanggal Acara <span class="required">*</span></label>
                        <input type="date" 
                            id="tanggal_acara" 
                            name="tanggal_acara"
                            value="<?= htmlspecialchars($old['tanggal_acara'] ?? '') ?>"
                            min="<?= date('Y-m-d', strtotime('+3 days')) ?>" 
                            required>
                        <span class="input-hint">Minimal H+3 dari hari ini</span>
                    </div>

                    <div class="form-group">
                        <label for="waktu_acara">Waktu Acara (Opsional)</label>
                        <input type="time" id="waktu_acara" name="waktu_acara">
                    </div>
                </div>

                <!-- METODE PEMBAYARAN -->
                <div class="form-section">
                    <h3 class="section-title">
                        <span>💳</span>
                        <span>Metode Pembayaran</span>
                    </h3>

                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" id="tunai" name="metode_pembayaran" value="Tunai" required>
                            <label for="tunai" class="payment-label">
                                <div class="payment-icon">💵</div>
                                <div class="payment-name">Tunai</div>
                            </label>
                        </div>

                        <div class="payment-option">
                            <input type="radio" id="qris" name="metode_pembayaran" value="QRIS">
                            <label for="qris" class="payment-label">
                                <div class="payment-icon">📱</div>
                                <div class="payment-name">Scan QRIS</div>
                            </label>
                        </div>

                        <div class="payment-option">
                            <input type="radio" id="transfer" name="metode_pembayaran" value="Transfer">
                            <label for="transfer" class="payment-label">
                                <div class="payment-icon">🏦</div>
                                <div class="payment-name">Transfer Bank</div>
                            </label>
                        </div>
                    </div>

                    <!-- UPLOAD BUKTI TRANSFER (CONDITIONAL) -->
                    <div class="upload-section" id="uploadSection">
                        <h4 class="upload-title">📤 Upload Bukti Transfer</h4>
                        
                        <!-- TAMPILKAN QRIS IMAGE JIKA PILIH QRIS -->
                        <div id="qrisImage" style="display: none; text-align: center; margin-bottom: 25px; padding: 20px; background: white; border-radius: 15px;">
                            <p style="font-size: 15px; font-weight: 600; color: var(--primary-purple); margin-bottom: 15px;">📱 Scan QRIS untuk Pembayaran</p>
                            <img src="gambar/qris.jpeg" 
                                 alt="QRIS Code" 
                                 style="max-width: 300px; width: 100%; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border: 3px solid var(--secondary-gold);"
                                 onerror="this.src='../../gambar/placeholder.jpg'; this.style.border='3px solid #F44336';">
                            <p style="margin-top: 12px; font-size: 13px; color: #666; line-height: 1.6;">
                                Scan kode QRIS di atas menggunakan aplikasi mobile banking atau e-wallet Anda.<br>
                                Setelah pembayaran berhasil, screenshot bukti pembayaran dan upload di bawah ini.
                            </p>
                        </div>
                        
                        <!-- TAMPILKAN INFO REKENING JIKA PILIH TRANSFER -->
                        <div id="bankInfo" style="display: none; padding: 20px; background: white; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid var(--secondary-gold); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                            <h5 style="margin-bottom: 15px; color: var(--primary-purple); font-size: 16px; font-weight: 700;">🏦 Informasi Rekening Transfer</h5>
                            <div style="display: grid; gap: 10px;">
                                <div style="display: flex; justify-content: space-between; padding: 10px; background: #FAFAFA; border-radius: 8px;">
                                    <span style="font-weight: 600; color: #666;">Bank:</span>
                                    <span style="font-weight: 700; color: var(--text-dark);">BCA</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 10px; background: #FAFAFA; border-radius: 8px;">
                                    <span style="font-weight: 600; color: #666;">No. Rekening:</span>
                                    <span style="font-weight: 700; color: var(--text-dark); font-family: monospace;">1234567890</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 10px; background: #FAFAFA; border-radius: 8px;">
                                    <span style="font-weight: 600; color: #666;">Atas Nama:</span>
                                    <span style="font-weight: 700; color: var(--text-dark);">Zarali's Catering</span>
                                </div>
                            </div>
                            <p style="margin-top: 12px; font-size: 13px; color: #666; line-height: 1.6;">
                                ⚠️ <strong>Penting:</strong> Transfer sesuai dengan total pembayaran dan upload bukti transfer di bawah ini.
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label for="bukti_transfer">Upload Bukti Pembayaran <span class="required">*</span></label>
                            <div class="file-upload-wrapper">
                                <label for="bukti_transfer" class="file-upload-label">
                                    <span class="file-upload-icon">📎</span>
                                    <span class="file-upload-text">Pilih file (JPG, PNG, PDF - Max 2MB)</span>
                                </label>
                                <input type="file" 
                                       id="bukti_transfer" 
                                       name="bukti_transfer" 
                                       class="file-upload-input"
                                       accept="image/jpeg,image/png,image/jpg,application/pdf">
                            </div>
                            <div class="file-preview" id="filePreview">
                                <span class="file-preview-icon">📄</span>
                                <span class="file-preview-name" id="fileName"></span>
                                <button type="button" class="file-remove" onclick="removeFile()">Hapus</button>
                            </div>
                            <span class="input-hint">*Wajib upload bukti transfer untuk metode QRIS/Transfer Bank</span>
                        </div>
                    </div>
                </div>

                <!-- CATATAN -->
                <div class="form-section">
                    <h3 class="section-title">
                        <span>📝</span>
                        <span>Catatan Tambahan</span>
                    </h3>

                    <div class="form-group">
                        <label for="catatan">Catatan untuk Pesanan (Opsional)</label>
                        <textarea id="catatan" name="catatan" placeholder="Contoh: Tolong sediakan piring plastik tambahan, atau request khusus lainnya..."></textarea>
                    </div>
                </div>
            </div>

            <!-- ORDER SUMMARY -->
            <div class="order-summary">
                <h3 class="summary-title">Ringkasan Pesanan</h3>

                <div class="summary-items">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <img src="../../uploads/produk/<?= htmlspecialchars($item['gambar']) ?>" 
                             alt="<?= htmlspecialchars($item['nama']) ?>" 
                             class="item-image"
                             onerror="this.src='../../gambar/placeholder.jpg';">
                        <div class="item-details">
                            <div class="item-name"><?= htmlspecialchars($item['nama']) ?></div>
                            <div class="item-qty"><?= $item['jumlah'] ?>x @ Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                        </div>
                        <div class="item-price">
                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Total Item</span>
                        <span><?= $total_items ?> item</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total Bayar</span>
                        <span>Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>✓</span>
                    <span>Konfirmasi Pesanan</span>
                </button>

                <a href="../keranjang.php" class="btn-back">
                    <span>←</span>
                    <span>Kembali ke Keranjang</span>
                </a>
            </div>
        </div>
    </form>
</div>

<footer>
    <div class="footer-brand">Zarali's Catering</div>
    <div class="footer-text">
        Kelompok 5 | Melayani dengan Hati untuk Setiap Acara Anda<br>
        © 2024 Zarali's Catering. All Rights Reserved.
    </div>
</footer>

<script>
// Show/Hide upload section based on payment method
document.querySelectorAll('input[name="metode_pembayaran"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const uploadSection = document.getElementById('uploadSection');
        const qrisImage = document.getElementById('qrisImage');
        const bankInfo = document.getElementById('bankInfo');
        const buktiTransfer = document.getElementById('bukti_transfer');
        
        if (this.value === 'QRIS') {
            uploadSection.classList.add('show');
            qrisImage.style.display = 'block';
            bankInfo.style.display = 'none';
            buktiTransfer.required = true;
        } else if (this.value === 'Transfer') {
            uploadSection.classList.add('show');
            qrisImage.style.display = 'none';
            bankInfo.style.display = 'block';
            buktiTransfer.required = true;
        } else {
            uploadSection.classList.remove('show');
            qrisImage.style.display = 'none';
            bankInfo.style.display = 'none';
            buktiTransfer.required = false;
            buktiTransfer.value = '';
            document.getElementById('filePreview').classList.remove('show');
        }
    });
});

// File preview
document.getElementById('bukti_transfer').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB!');
            this.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file harus JPG, PNG, atau PDF!');
            this.value = '';
            return;
        }
        
        // Show preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').classList.add('show');
    }
});

// Remove file
function removeFile() {
    document.getElementById('bukti_transfer').value = '';
    document.getElementById('filePreview').classList.remove('show');
}

// Form validation
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const paymentSelected = document.querySelector('input[name="metode_pembayaran"]:checked');
    
    if (!paymentSelected) {
        e.preventDefault();
        alert('Silakan pilih metode pembayaran!');
        return;
    }
    
    // Check if bukti transfer is required
    if ((paymentSelected.value === 'QRIS' || paymentSelected.value === 'Transfer') && 
        !document.getElementById('bukti_transfer').files[0]) {
        e.preventDefault();
        alert('Silakan upload bukti pembayaran!');
        return;
    }
    
    // Disable button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳</span><span>Memproses...</span>';
});

// Set minimum date for tanggal_acara (H+3)
document.addEventListener('DOMContentLoaded', function() {
    const tanggalInput = document.getElementById('tanggal_acara');
    const today = new Date();
    today.setDate(today.getDate() + 3);
    const minDate = today.toISOString().split('T')[0];
    tanggalInput.setAttribute('min', minDate);
});
</script>

</body>
</html>