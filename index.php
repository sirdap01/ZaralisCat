<?php
session_start();
include 'includes/config.php';

// Ambil semua produk dari database
$query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC");

// Hitung jumlah item di keranjang (untuk badge notifikasi)
$cart_count = 0;
if (isset($_SESSION['id_pengguna'])) {
    $id_pengguna = (int) $_SESSION['id_pengguna'];
    $cart_result = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM keranjang WHERE id_pengguna = $id_pengguna");
    $cart_data = mysqli_fetch_assoc($cart_result);
    $cart_count = $cart_data['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home - Zarali's Catering</title>
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

    /* Floating effect on scroll */
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
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ===== PRODUK SECTION ===== */
    .produk-section {
      padding: 60px 40px;
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
      flex: 1;
    }

    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }

    .section-header h2 {
      font-size: 2.5rem;
      color: var(--primary-purple);
      font-weight: 700;
      margin-bottom: 12px;
      position: relative;
      display: inline-block;
    }

    .section-header h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-purple), var(--accent-purple));
      border-radius: 2px;
    }

    .section-header p {
      font-size: 1.1rem;
      color: #666;
      margin-top: 20px;
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 30px;
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .product-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(123,44,191,0.12);
      padding: 20px;
      text-align: center;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
    }

    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 50px rgba(123,44,191,0.2);
    }

    .product-image-container {
      position: relative;
      width: 100%;
      height: 200px;
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 16px;
    }

    .product-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .product-card:hover .product-image {
      transform: scale(1.05);
    }

    .product-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(123,44,191,0.4);
    }

    .product-card h3 {
      font-size: 20px;
      font-weight: 700;
      color: var(--primary-purple);
      margin-bottom: 10px;
      min-height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .product-description {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
      margin-bottom: 16px;
      flex-grow: 1;
      min-height: 60px;
    }

    .product-price {
      font-size: 22px;
      font-weight: 700;
      color: var(--accent-purple);
      margin-bottom: 18px;
      padding: 10px;
      background: linear-gradient(135deg, rgba(123,44,191,0.05), rgba(157,78,221,0.05));
      border-radius: 10px;
    }

    /* ===== QUANTITY CONTROLS ===== */
    .quantity-controls {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 15px;
      padding: 12px;
      background: #F5F5F5;
      border-radius: 12px;
    }

    .quantity-label {
      font-size: 13px;
      font-weight: 600;
      color: #666;
    }

    .quantity-input-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn-quantity {
      width: 36px;
      height: 36px;
      border: none;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      border-radius: 8px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(123, 44, 191, 0.3);
    }

    .btn-quantity:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(123, 44, 191, 0.4);
    }

    .btn-quantity:active {
      transform: translateY(0);
    }

    .btn-quantity:disabled {
      background: #BDBDBD;
      cursor: not-allowed;
      box-shadow: none;
    }

    .quantity-value {
      width: 50px;
      height: 36px;
      border: 2px solid #E0E0E0;
      border-radius: 8px;
      text-align: center;
      font-size: 16px;
      font-weight: 700;
      color: var(--primary-purple);
      background: white;
    }

    .quantity-value:focus {
      outline: none;
      border-color: var(--primary-purple);
      box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
    }

    /* ===== ADD TO CART BUTTON ===== */
    .btn-add-cart {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 30px;
      background: linear-gradient(135deg, #00C853, #00E676);
      color: white;
      text-decoration: none;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 200, 83, 0.3);
      width: 100%;
    }

    .btn-add-cart:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 200, 83, 0.4);
    }

    .btn-add-cart:active {
      transform: translateY(0);
    }

    /* ===== NOTIFICATION TOAST ===== */
    .toast {
      position: fixed;
      top: 100px;
      right: 30px;
      background: white;
      padding: 20px 25px;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
      display: none;
      align-items: center;
      gap: 15px;
      z-index: 10000;
      animation: slideInRight 0.4s ease;
      min-width: 320px;
    }

    .toast.show {
      display: flex;
    }

    .toast.success {
      border-left: 4px solid #00C853;
    }

    .toast.error {
      border-left: 4px solid #F44336;
    }

    @keyframes slideInRight {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .toast-icon {
      font-size: 28px;
      flex-shrink: 0;
    }

    .toast-content {
      flex: 1;
    }

    .toast-title {
      font-weight: 700;
      font-size: 15px;
      color: var(--text-dark);
      margin-bottom: 4px;
    }

    .toast-message {
      font-size: 13px;
      color: #666;
    }

    .toast-close {
      background: none;
      border: none;
      font-size: 24px;
      color: #999;
      cursor: pointer;
      padding: 0;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.3s ease;
    }

    .toast-close:hover {
      color: #333;
    }

    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #666;
    }

    .empty-state-icon {
      font-size: 80px;
      margin-bottom: 20px;
      opacity: 0.5;
    }

    .empty-state h3 {
      font-size: 24px;
      color: var(--primary-purple);
      margin-bottom: 12px;
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

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 24px;
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

      nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
      }

      .banner h2 {
        font-size: 1.8rem;
      }

      .produk-section {
        padding: 30px 16px;
      }

      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
      }

      .toast {
        right: 16px;
        min-width: 280px;
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
    }

    @media (max-width: 480px) {
      .products-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <script>
  // Add scroll effect to floating cart
  window.addEventListener('scroll', function() {
    const floatingCart = document.querySelector('.floating-cart');
    if (window.scrollY > 100) {
      floatingCart.classList.add('scrolled');
    } else {
      floatingCart.classList.remove('scrolled');
    }
  });

  // Quantity controls
  function updateQuantity(productId, action) {
    const input = document.getElementById('qty-' + productId);
    let currentValue = parseInt(input.value) || 1;
    
    if (action === 'minus' && currentValue > 1) {
      currentValue--;
    } else if (action === 'plus' && currentValue < 100) {
      currentValue++;
    }
    
    input.value = currentValue;
  }

  // Add to cart
  function addToCart(productId, productName) {
    const quantity = document.getElementById('qty-' + productId).value;
    
    // Check if user is logged in
    <?php if (!isset($_SESSION['id_pengguna'])): ?>
      showToast('error', 'Login Required', 'Silakan login terlebih dahulu untuk menambahkan ke keranjang');
      setTimeout(() => {
        window.location.href = 'login.php';
      }, 1500);
      return;
    <?php endif; ?>
    
    // Send AJAX request
    const formData = new FormData();
    formData.append('id_produk', productId);
    formData.append('jumlah', quantity);
    
    fetch('proses_tambah_keranjang.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('success', 'Berhasil!', `${productName} (${quantity}x) ditambahkan ke keranjang`);
        updateCartBadge(data.cart_count);
        // Reset quantity to 1
        document.getElementById('qty-' + productId).value = 1;
      } else {
        showToast('error', 'Gagal!', data.message || 'Terjadi kesalahan');
      }
    })
    .catch(error => {
      showToast('error', 'Error!', 'Terjadi kesalahan koneksi');
      console.error('Error:', error);
    });
  }

  // Show toast notification
  function showToast(type, title, message) {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    
    toast.className = `toast ${type}`;
    toastIcon.textContent = type === 'success' ? '✓' : '✕';
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    
    toast.classList.add('show');
    
    setTimeout(() => {
      toast.classList.remove('show');
    }, 3000);
  }

  // Update cart badge
  function updateCartBadge(count) {
    const badge = document.getElementById('cartBadge');
    if (badge) {
      badge.textContent = count;
      if (count > 0) {
        badge.classList.remove('empty');
      } else {
        badge.classList.add('empty');
      }
    }
  }

  // Close toast
  function closeToast() {
    document.getElementById('toast').classList.remove('show');
  }
  </script>
</head>

<body>

<!-- TOAST NOTIFICATION -->
<div id="toast" class="toast">
  <div id="toastIcon" class="toast-icon"></div>
  <div class="toast-content">
    <div id="toastTitle" class="toast-title"></div>
    <div id="toastMessage" class="toast-message"></div>
  </div>
  <button class="toast-close" onclick="closeToast()">×</button>
</div>

<!-- FLOATING CART BUTTON -->
<?php if (isset($_SESSION['id_pengguna'])): ?>
<div class="floating-cart">
  <a href="keranjang.php" class="cart-button" title="Lihat Keranjang">
    <div class="cart-icon">🛒</div>
    <span id="cartBadge" class="cart-badge <?= $cart_count == 0 ? 'empty' : '' ?>">
      <?= $cart_count ?>
    </span>
  </a>
</div>
<?php endif; ?>

<header>
  <div class="logo-container">
    <img src="gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
    <h1>Zarali's Catering</h1>
  </div>

  <nav>
    <a href="index.php" class="active">Home</a>
    <a href="menu.php">Menu</a>
    <a href="users/testi.php">Testimoni</a>
    <a href="users/pesanan.php">Pesanan saya</a>
    <a href="users/contact.php">Hubungi kami</a>
    <a href="about.html">Tentang kami</a>
    <a href="<?= isset($_SESSION['id_pengguna']) ? 'logout.php' : 'login.php' ?>">
      <?= isset($_SESSION['id_pengguna']) ? 'Logout' : 'Login' ?>
    </a>
  </nav>
</header>

<div class="banner">
  <div class="banner-content">
    <h2>Selamat Datang di Zarali's Catering</h2>
    <p>Hidangan lezat untuk setiap momen spesial Anda</p>
  </div>
</div>

<!-- SECTION SEMUA PRODUK -->
<section class="produk-section">
  <div class="section-header">
    <h2>Menu Kami</h2>
    <p>Pilihan hidangan terbaik untuk acara Anda</p>
  </div>

  <?php if (mysqli_num_rows($query_produk) == 0): ?>
    <div class="empty-state">
      <div class="empty-state-icon">🍽️</div>
      <h3>Belum Ada Produk</h3>
      <p>Produk akan segera tersedia. Silakan cek kembali nanti.</p>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php 
      mysqli_data_seek($query_produk, 0);
      while ($produk = mysqli_fetch_assoc($query_produk)): 
      ?>
        <div class="product-card">
          <div class="product-image-container">
            <img src="uploads/produk/<?= htmlspecialchars($produk['gambar']); ?>"
                 alt="<?= htmlspecialchars($produk['nama']); ?>"
                 class="product-image"
                 onerror="this.src='gambar/placeholder.jpg';">
            <span class="product-badge"><?= htmlspecialchars($produk['kategori']); ?></span>
          </div>
          <h3><?= htmlspecialchars($produk['nama']); ?></h3>
          <p class="product-description"><?= htmlspecialchars($produk['deskripsi']); ?></p>
          <div class="product-price">Rp <?= number_format($produk['harga'], 0, ',', '.'); ?></div>
          
          <!-- QUANTITY CONTROLS -->
          <div class="quantity-controls">
            <span class="quantity-label">Jumlah:</span>
            <div class="quantity-input-group">
              <button type="button" class="btn-quantity" onclick="updateQuantity(<?= $produk['id'] ?>, 'minus')">−</button>
              <input type="number" 
                     id="qty-<?= $produk['id'] ?>" 
                     class="quantity-value" 
                     value="1" 
                     min="1" 
                     max="100" 
                     readonly>
              <button type="button" class="btn-quantity" onclick="updateQuantity(<?= $produk['id'] ?>, 'plus')">+</button>
            </div>
          </div>

          <!-- ADD TO CART BUTTON -->
          <button class="btn-add-cart" onclick="addToCart(<?= $produk['id'] ?>, '<?= htmlspecialchars($produk['nama']) ?>')">
            <span>🛒</span>
            <span>Tambah ke Keranjang</span>
          </button>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

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