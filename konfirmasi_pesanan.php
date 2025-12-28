<?php
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php");
    exit;
}

// Check if order success data exists
if (!isset($_SESSION['order_success'])) {
    header("Location: ../../index.php");
    exit;
}

$order = $_SESSION['order_success'];
$id_pengguna = (int) $_SESSION['id_pengguna'];

// Get cart count (should be 0 now)
$cart_count = 0;

// Clear the order success data after displaying
unset($_SESSION['order_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan Berhasil - Zarali's Catering</title>
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

header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 16px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 85px;
    box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
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
}

header h1 {
    font-weight: 700;
    font-size: 1.2rem;
}

nav {
    display: flex;
    gap: 30px;
}

nav a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

nav a:hover {
    color: var(--secondary-gold);
    background-color: rgba(255, 255, 255, 0.1);
}

/* PROGRESS STEPS */
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
    background: linear-gradient(135deg, #4CAF50, #81C784);
    color: white;
}

.step-label {
    font-weight: 600;
    font-size: 14px;
}

.step-arrow {
    color: #BDBDBD;
    font-size: 20px;
}

/* SUCCESS CONTAINER */
.success-container {
    max-width: 800px;
    margin: 60px auto;
    padding: 0 40px;
    flex: 1;
}

.success-card {
    background: white;
    border-radius: 25px;
    padding: 50px;
    box-shadow: 0 10px 40px rgba(123, 44, 191, 0.15);
    text-align: center;
    animation: slideUp 0.6s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 30px;
    background: linear-gradient(135deg, #4CAF50, #81C784);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
    animation: bounce 0.8s ease;
    box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
}

@keyframes bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.success-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 15px;
}

.success-message {
    font-size: 16px;
    color: #666;
    line-height: 1.8;
    margin-bottom: 35px;
}

.order-info-box {
    background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), rgba(157, 78, 221, 0.05));
    border-radius: 15px;
    padding: 30px;
    margin: 30px 0;
    border-left: 5px solid var(--secondary-gold);
}

.order-detail {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #E0E0E0;
    font-size: 15px;
}

.order-detail:last-child {
    border-bottom: none;
}

.order-detail-label {
    color: #666;
    font-weight: 500;
}

.order-detail-value {
    font-weight: 700;
    color: var(--text-dark);
}

.order-detail.total {
    font-size: 24px;
    color: var(--primary-purple);
    padding-top: 20px;
    margin-top: 15px;
    border-top: 2px solid #E0E0E0;
    border-bottom: none;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 35px;
}

.btn {
    padding: 16px 30px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
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

.info-note {
    background: #FFF9E6;
    border-left: 4px solid #FFC107;
    padding: 20px;
    border-radius: 10px;
    margin-top: 30px;
    text-align: left;
}

.info-note-title {
    font-weight: 700;
    color: #F57F17;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-note-text {
    font-size: 14px;
    color: #666;
    line-height: 1.7;
}

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

    .success-container {
        padding: 0 16px;
        margin: 30px auto;
    }

    .success-card {
        padding: 30px 20px;
    }

    .action-buttons {
        grid-template-columns: 1fr;
    }

    .progress-steps {
        flex-direction: column;
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
        <a href="../../users/contact.html">Hubungi kami</a>
        <a href="../../about.php">Tentang kami</a>
        <a href="../../logout.php">Logout</a>
    </nav>
</header>

<!-- PROGRESS STEPS -->
<div class="progress-steps">
    <div class="step">
        <div class="step-number">✓</div>
        <div class="step-label">Keranjang</div>
    </div>
    <div class="step-arrow">→</div>
    <div class="step">
        <div class="step-number">✓</div>
        <div class="step-label">Checkout</div>
    </div>
    <div class="step-arrow">→</div>
    <div class="step">
        <div class="step-number">✓</div>
        <div class="step-label">Konfirmasi</div>
    </div>
</div>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">✓</div>
        
        <h1 class="success-title">Pesanan Berhasil Dibuat!</h1>
        
        <p class="success-message">
            Terima kasih atas pesanan Anda. Pesanan Anda telah berhasil diterima dan sedang menunggu konfirmasi dari tim kami. 
            Kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut.
        </p>

        <div class="order-info-box">
            <div class="order-detail">
                <span class="order-detail-label">📋 Nomor Pesanan:</span>
                <span class="order-detail-value">#<?= str_pad($order['id_pesanan'], 4, '0', STR_PAD_LEFT) ?></span>
            </div>

            <div class="order-detail">
                <span class="order-detail-label">📅 Tanggal Acara:</span>
                <span class="order-detail-value"><?= date('d F Y', strtotime($order['tanggal_acara'])) ?></span>
            </div>

            <?php if (!empty($order['waktu_acara'])): ?>
            <div class="order-detail">
                <span class="order-detail-label">🕐 Waktu Acara:</span>
                <span class="order-detail-value"><?= date('H:i', strtotime($order['waktu_acara'])) ?> WIB</span>
            </div>
            <?php endif; ?>

            <div class="order-detail">
                <span class="order-detail-label">📍 Alamat Pengiriman:</span>
                <span class="order-detail-value"><?= htmlspecialchars($order['alamat']) ?></span>
            </div>

            <div class="order-detail">
                <span class="order-detail-label">💳 Metode Pembayaran:</span>
                <span class="order-detail-value"><?= htmlspecialchars($order['metode_pembayaran']) ?></span>
            </div>

            <div class="order-detail">
                <span class="order-detail-label">📦 Total Item:</span>
                <span class="order-detail-value"><?= $order['total_items'] ?> item</span>
            </div>

            <div class="order-detail total">
                <span class="order-detail-label">Total Bayar:</span>
                <span class="order-detail-value">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="info-note">
            <div class="info-note-title">
                <span>💡</span>
                <span>Informasi Penting</span>
            </div>
            <div class="info-note-text">
                • Status pesanan Anda saat ini: <strong>Pending</strong><br>
                • Tim kami akan menghubungi Anda melalui telepon/WhatsApp untuk konfirmasi<br>
                • Anda dapat memantau status pesanan di halaman "Pesanan Saya"<br>
                • Simpan nomor pesanan Anda untuk referensi
            </div>
        </div>

        <div class="action-buttons">
            <a href="users/pesanan.php" class="btn btn-primary">
                <span>📋</span>
                <span>Lihat Pesanan Saya</span>
            </a>
            <a href="../../menu.php" class="btn btn-secondary">
                <span>🛒</span>
                <span>Pesan Lagi</span>
            </a>
        </div>
    </div>
</div>

<footer>
    <div class="footer-brand">Zarali's Catering</div>
    <div class="footer-text">
        Kelompok 5 | Melayani dengan Hati untuk Setiap Acara Anda<br>
        © 2024 Zarali's Catering. All Rights Reserved.
    </div>
</footer>

<script>
// Prevent back button after successful order
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};
</script>

</body>
</html>