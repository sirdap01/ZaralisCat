<?php
session_start();
include '../includes/config.php';

// Cek apakah user sudah login
$is_logged_in = isset($_SESSION['id_pengguna']);
$id_pengguna = $is_logged_in ? (int) $_SESSION['id_pengguna'] : 0;

// Jika sudah login, ambil data pesanan
$query_pesanan = null;
if ($is_logged_in) {
    $query_pesanan = mysqli_query($koneksi, "
        SELECT * FROM pesanan 
        WHERE id_pengguna = $id_pengguna 
        ORDER BY tanggal_pesanan DESC
    ");
}

// Hitung jumlah item di keranjang (untuk badge notifikasi)
$cart_count = 0;
if ($is_logged_in) {
    $cart_result = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM keranjang WHERE id_pengguna = $id_pengguna");
    $cart_data = mysqli_fetch_assoc($cart_result);
    $cart_count = $cart_data['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya – Zarali's Catering</title>
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
      flex-shrink: 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .logo-container {
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 280px;
    }

    .logo {
      max-height: 55px;
      border-radius: 50%;
      border: 3px solid var(--secondary-gold);
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    header h1 {
      font-weight: 700;
      font-size: 1.2rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
      letter-spacing: 0.5px;
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
      position: relative;
    }

    nav a:hover {
      color: var(--secondary-gold);
      background-color: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px);
    }

    nav a.active {
      background-color: rgba(255, 215, 0, 0.2);
      color: var(--secondary-gold);
    }

    /* ===== FLOATING CART BUTTON ===== */
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

    .cart-button:active {
      transform: translateY(-2px) scale(1.02);
    }

    .cart-icon {
      font-size: 32px;
      animation: swing 2s infinite;
    }

    @keyframes swing {
      0%, 100% { transform: rotate(0deg); }
      25% { transform: rotate(-15deg); }
      75% { transform: rotate(15deg); }
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
      animation: pulse 2s infinite;
      border: 2px solid white;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.15); }
    }

    .cart-badge.empty {
      display: none;
    }

    .floating-cart.scrolled .cart-button {
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    .banner {
      background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
      min-height: 280px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
      padding: 40px 20px;
      position: relative;
      overflow: hidden;
    }

    .banner::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 50%, rgba(255, 215, 0, 0.1), transparent 50%);
      pointer-events: none;
    }

    .banner-content {
      position: relative;
      z-index: 2;
      text-align: center;
      max-width: 900px;
    }

    .banner h2 {
      font-size: 2.8rem;
      font-weight: 700;
      color: var(--secondary-gold);
      text-shadow: 3px 3px 8px rgba(0,0,0,0.4);
      margin-bottom: 16px;
      letter-spacing: 1px;
      animation: fadeInDown 0.8s ease;
    }

    .banner p {
      font-size: 1.2rem;
      font-weight: 300;
      color: white;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
      animation: fadeInUp 0.8s ease 0.2s both;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .content-wrapper {
      padding: 60px 40px;
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
      flex: 1;
    }

    .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 400px;
      text-align: center;
      padding: 40px 20px;
    }

    .empty-icon {
      font-size: 80px;
      margin-bottom: 24px;
      animation: bounce 2s infinite;
    }

    .empty-state h3 {
      font-size: 28px;
      font-weight: 700;
      color: var(--primary-purple);
      margin-bottom: 12px;
    }

    .empty-state p {
      font-size: 16px;
      color: #666;
      max-width: 500px;
      line-height: 1.6;
      margin-bottom: 30px;
    }

    .btn-menu, .btn-login {
      display: inline-block;
      padding: 14px 35px;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      font-weight: 700;
      font-size: 16px;
      border-radius: 12px;
      text-decoration: none;
      box-shadow: 0 6px 20px rgba(123, 44, 191, 0.3);
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      margin: 0 10px;
    }

    .btn-menu:hover, .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(123, 44, 191, 0.4);
    }

    @keyframes bounce {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-10px);
      }
    }

    /* ===== LOGIN REQUIRED BOX ===== */
    .login-required-box {
      background: white;
      border-radius: 20px;
      padding: 50px 40px;
      text-align: center;
      max-width: 600px;
      margin: 0 auto;
      box-shadow: 0 10px 40px rgba(123,44,191,0.15);
    }

    .login-required-box .icon {
      font-size: 80px;
      margin-bottom: 20px;
      animation: bounce 2s infinite;
    }

    .login-required-box h3 {
      font-size: 28px;
      color: var(--primary-purple);
      font-weight: 700;
      margin-bottom: 15px;
    }

    .login-required-box p {
      font-size: 16px;
      color: #666;
      line-height: 1.6;
      margin-bottom: 30px;
    }

    /* ===== ORDER LIST ===== */
    .orders-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 25px;
    }

    .order-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 8px 25px rgba(123, 44, 191, 0.12);
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .order-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 35px rgba(123, 44, 191, 0.2);
      border-color: var(--accent-purple);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #F0F0F0;
    }

    .order-id {
      font-size: 16px;
      font-weight: 700;
      color: var(--primary-purple);
    }

    .order-status {
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 12px;
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

    .order-info {
      margin-bottom: 15px;
    }

    .order-info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .order-info-label {
      color: #666;
    }

    .order-info-value {
      font-weight: 700;
      color: var(--text-dark);
    }

    .order-total {
      font-size: 20px;
      font-weight: 700;
      color: var(--accent-purple);
      text-align: center;
      padding: 15px;
      background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), rgba(157, 78, 221, 0.05));
      border-radius: 10px;
      margin-top: 15px;
    }

    .btn-detail {
      display: block;
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      text-align: center;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 700;
      margin-top: 15px;
      transition: all 0.3s ease;
    }

    .btn-detail:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(123, 44, 191, 0.4);
    }

    footer {
      margin-top: auto;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      padding: 30px 40px;
      text-align: center;
      color: white;
      box-shadow: 0 -4px 12px rgba(123, 44, 191, 0.3);
    }

    .footer-content {
      max-width: 1400px;
      margin: 0 auto;
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
      font-weight: 400;
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.6;
    }

    @media (max-width: 1024px) {
      header {
        padding: 16px 24px;
      }

      nav {
        gap: 20px;
      }

      nav a {
        font-size: 14px;
      }

      .banner h2 {
        font-size: 2.2rem;
      }

      .content-wrapper {
        padding: 40px 24px;
      }

      .orders-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
      }

      .floating-cart {
        bottom: 20px;
        right: 20px;
      }

      .cart-button {
        width: 60px;
        height: 60px;
      }

      .cart-icon {
        font-size: 28px;
      }
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        min-height: auto;
        padding: 16px;
        gap: 16px;
      }

      .logo-container {
        min-width: auto;
      }

      header h1 {
        font-size: 1.5rem;
      }

      nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
      }

      nav a {
        font-size: 13px;
        padding: 6px 10px;
      }

      .banner {
        min-height: 240px;
        padding: 30px 16px;
      }

      .banner h2 {
        font-size: 1.8rem;
      }

      .banner p {
        font-size: 1rem;
      }

      .content-wrapper {
        padding: 30px 16px;
      }

      .orders-grid {
        grid-template-columns: 1fr;
      }

      footer {
        padding: 24px 16px;
      }

      .floating-cart {
        bottom: 15px;
        right: 15px;
      }

      .cart-button {
        width: 55px;
        height: 55px;
      }

      .cart-icon {
        font-size: 24px;
      }

      .cart-badge {
        width: 24px;
        height: 24px;
        font-size: 11px;
      }

      .login-required-box {
        padding: 40px 25px;
      }
    }

  </style>

  <script>
  window.addEventListener('scroll', function() {
    const floatingCart = document.querySelector('.floating-cart');
    if (floatingCart && window.scrollY > 100) {
      floatingCart.classList.add('scrolled');
    } else if (floatingCart) {
      floatingCart.classList.remove('scrolled');
    }
  });
  </script>
</head>

<body>

<!-- FLOATING CART BUTTON -->
<?php if ($is_logged_in): ?>
<div class="floating-cart">
  <a href="../keranjang.php" class="cart-button" title="Lihat Keranjang">
    <div class="cart-icon">🛒</div>
    <span id="cartBadge" class="cart-badge <?= $cart_count == 0 ? 'empty' : '' ?>">
      <?= $cart_count ?>
    </span>
  </a>
</div>
<?php endif; ?>

<header>
  <div class="logo-container">
    <img src="../gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
    <h1>Zarali's Catering</h1>
  </div>

  <nav>
    <a href="../index.php">Home</a>
    <a href="../menu.php">Menu</a>
    <a href="testi.php">Testimoni</a>
    <a href="pesanan.php" class="active">Pesanan saya</a>
    <a href="contact.php">Hubungi kami</a>
    <a href="../about.php">Tentang kami</a>
    <?php if ($is_logged_in): ?>
      <a href="../logout.php">Logout</a>
    <?php else: ?>
      <a href="../login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>

<div class="banner">
  <div class="banner-content">
    <h2>Riwayat Pesanan</h2>
    <p>Lihat dan kelola semua pesanan Anda di sini</p>
  </div>
</div>

<div class="content-wrapper">
  <?php if (!$is_logged_in): ?>
    <!-- BELUM LOGIN -->
    <div class="login-required-box">
      <div class="icon">🔐</div>
      <h3>Login Diperlukan</h3>
      <p>Untuk melihat riwayat pesanan Anda, silakan login terlebih dahulu. Belum punya akun? Daftar sekarang dan nikmati kemudahan berbelanja dengan kami!</p>
      <div>
        <a href="../login.php" class="btn-login">Login</a>
        <a href="../register.php" class="btn-menu">Daftar</a>
      </div>
    </div>
  <?php elseif (mysqli_num_rows($query_pesanan) == 0): ?>
    <!-- SUDAH LOGIN TAPI BELUM ADA PESANAN -->
    <div class="empty-state">
      <div class="empty-icon">📦</div>
      <h3>Belum Ada Pesanan</h3>
      <p>Anda belum membuat pesanan apa pun. Jelajahi menu kami dan pesan hidangan favorit Anda sekarang!</p>
      <a href="../menu.php" class="btn-menu">Lihat Menu Kami</a>
    </div>
  <?php else: ?>
    <!-- SUDAH LOGIN DAN ADA PESANAN -->
    <div class="orders-grid">
      <?php while ($pesanan = mysqli_fetch_assoc($query_pesanan)): ?>
        <div class="order-card">
          <div class="order-header">
            <div class="order-id">#<?= htmlspecialchars($pesanan['id_pesanan']) ?></div>
            <div class="order-status status-<?= strtolower($pesanan['status']) ?>">
              <?= htmlspecialchars($pesanan['status']) ?>
            </div>
          </div>

          <div class="order-info">
            <div class="order-info-row">
              <span class="order-info-label">🎉 Tanggal Acara:</span>
              <span class="order-info-value"><?= date('d M Y', strtotime($pesanan['tanggal_pesanan'])) ?></span>
            </div>
            <div class="order-info-row">
              <span class="order-info-label">💳 Pembayaran:</span>
              <span class="order-info-value"><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></span>
            </div>
          </div>

          <div class="order-total">
            Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?>
          </div>

          <a href="detail_pesanan.php?id=<?= $pesanan['id_pesanan'] ?>" class="btn-detail">
            Lihat Detail
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<footer>
  <div class="footer-content">
    <div class="footer-brand">Zarali's Catering</div>
    <div class="footer-text">
      Kelompok 5 | Melayani dengan Hati untuk Setiap Acara Anda<br>
      © 2024 Zarali's Catering. All Rights Reserved.
    </div>
  </div>
</footer>

</body>
</html>