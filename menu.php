<?php
include 'includes/config.php';

// Ambil kategori dari URL (jika ada)
$kategori_filter = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

// Query produk berdasarkan kategori
if ($kategori_filter) {
    $query_produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE kategori = '$kategori_filter' ORDER BY id DESC");
} else {
    $query_produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC");
}

// Hitung jumlah produk per kategori
$count_paket_besar = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk WHERE kategori='Paket Besar'"))['total'];
$count_kue_satuan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk WHERE kategori='Kue Satuan'"))['total'];
$count_minuman = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk WHERE kategori='Minuman'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Menu - Zarali's Catering</title>
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

    /* =====================================
       HEADER (KONSISTEN)
    ===================================== */
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

    /* =====================================
       BANNER (KONSISTEN)
    ===================================== */
    .banner {
      background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
      min-height: 320px;
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

    /* =====================================
       KATEGORI SECTION
    ===================================== */
    .kategori {
      display: flex;
      flex-direction: column;
      gap: 35px;
      padding: 60px 40px;
      max-width: 1200px;
      margin: 0 auto;
      width: 100%;
    }

    .card {
      display: flex;
      align-items: center;
      background-color: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
      overflow: hidden;
      transition: all 0.4s ease;
      cursor: pointer;
      height: 240px;
      text-decoration: none;
      border: 3px solid transparent;
      position: relative;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), transparent);
      opacity: 0;
      transition: opacity 0.4s ease;
      pointer-events: none;
    }

    .card:hover::before {
      opacity: 1;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 40px rgba(123, 44, 191, 0.25);
      border-color: var(--accent-purple);
    }

    .card-image-container {
      width: 340px;
      height: 240px;
      overflow: hidden;
      flex-shrink: 0;
      position: relative;
    }

    .card-image-container::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(to right, transparent, rgba(255,255,255,0.1));
      pointer-events: none;
    }

    .card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .card:hover img {
      transform: scale(1.1);
    }

    .card-content {
      flex: 1;
      padding: 30px 40px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }

    .card h3 {
      font-size: 32px;
      color: var(--primary-purple);
      font-weight: 700;
      text-align: center;
      transition: all 0.3s ease;
      position: relative;
      display: inline-block;
    }

    .card h3::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 50%;
      transform: translateX(-50%) scaleX(0);
      width: 80%;
      height: 3px;
      background-color: var(--secondary-gold);
      transition: transform 0.3s ease;
    }

    .card:hover h3 {
      color: var(--accent-purple);
    }

    .card:hover h3::after {
      transform: translateX(-50%) scaleX(1);
    }

    .card-count {
      background: linear-gradient(135deg, var(--secondary-gold), #FFC700);
      color: var(--primary-purple);
      padding: 6px 18px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 700;
      box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
    }

    /* =====================================
       PRODUK SECTION
    ===================================== */
    .produk-section {
      padding: 60px 40px;
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
      display: <?= $kategori_filter ? 'block' : 'none' ?>;
    }

    .section-header {
      text-align: center;
      margin-bottom: 40px;
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

    .filter-info {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
    }

    .filter-badge {
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
    }

    .btn-reset {
      padding: 8px 20px;
      background: white;
      color: var(--primary-purple);
      border: 2px solid var(--primary-purple);
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-reset:hover {
      background: var(--primary-purple);
      color: white;
      transform: translateY(-2px);
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 30px;
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
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

    .btn-detail {
      display: inline-block;
      padding: 12px 30px;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 15px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(123,44,191,0.3);
    }

    .btn-detail:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(123,44,191,0.4);
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

    /* =====================================
       FOOTER (KONSISTEN)
    ===================================== */
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

    /* =====================================
       RESPONSIVE DESIGN
    ===================================== */
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

      .kategori {
        padding: 40px 24px;
      }

      .card {
        height: 200px;
      }

      .card-image-container {
        width: 280px;
        height: 200px;
      }

      .card h3 {
        font-size: 26px;
      }

      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 24px;
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
        min-height: 280px;
        padding: 30px 16px;
      }

      .banner h2 {
        font-size: 1.8rem;
      }

      .banner p {
        font-size: 1rem;
      }

      .kategori,
      .produk-section {
        padding: 30px 16px;
      }

      .kategori {
        gap: 25px;
      }

      .card {
        flex-direction: column;
        height: auto;
      }

      .card-image-container {
        width: 100%;
        height: 200px;
      }

      .card-content {
        padding: 25px 20px;
      }

      .card h3 {
        font-size: 24px;
      }

      .section-header h2 {
        font-size: 2rem;
      }

      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
      }

      footer {
        padding: 24px 16px;
      }
    }

    @media (max-width: 480px) {
      .products-grid {
        grid-template-columns: 1fr;
      }

      .filter-info {
        flex-direction: column;
      }
    }

  </style>
</head>

<body>

<header>
  <div class="logo-container">
    <img src="gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
    <h1>Zarali's Catering</h1>
  </div>

  <nav>
    <a href="index.php">Home</a>
    <a href="menu.php" class="active">Menu</a>
    <a href="users/testi.html">Testimoni</a>
    <a href="users/pesanan.html">Pesanan saya</a>
    <a href="users/contact.html">Hubungi kami</a>
    <a href="about.html">Tentang kami</a>
    <a href="login.php">Login</a>
  </nav>
</header>

<div class="banner">
  <div class="banner-content">
    <h2><?= $kategori_filter ? 'Menu ' . $kategori_filter : 'Pilih Menu Favorit Anda' ?></h2>
    <p><?= $kategori_filter ? 'Produk berkualitas pilihan terbaik' : '👇 Pilih menu berdasarkan kategori di bawah 👇' ?></p>
  </div>
</div>

<!-- KATEGORI SECTION (Tampil jika tidak ada filter) -->
<?php if (!$kategori_filter): ?>
<section class="kategori">
  <a href="menu.php?kategori=Paket Besar" class="card">
    <div class="card-image-container">
      <img src="gambar/paket_besar.jpg" alt="Paket Besar">
    </div>
    <div class="card-content">
      <h3>Paket Besar</h3>
      <span class="card-count"><?= $count_paket_besar ?> Produk</span>
    </div>
  </a>

  <a href="menu.php?kategori=Kue Satuan" class="card">
    <div class="card-image-container">
      <img src="gambar/kue_satuan.jpg" alt="Kue Satuan">
    </div>
    <div class="card-content">
      <h3>Kue Satuan</h3>
      <span class="card-count"><?= $count_kue_satuan ?> Produk</span>
    </div>
  </a>

  <a href="menu.php?kategori=Minuman" class="card">
    <div class="card-image-container">
      <img src="gambar/minuman.jpg" alt="Minuman">
    </div>
    <div class="card-content">
      <h3>Minuman</h3>
      <span class="card-count"><?= $count_minuman ?> Produk</span>
    </div>
  </a>
</section>
<?php endif; ?>

<!-- PRODUK SECTION (Tampil jika ada filter) -->
<?php if ($kategori_filter): ?>
<section class="produk-section">
  <div class="section-header">
    <h2><?= htmlspecialchars($kategori_filter) ?></h2>
    <div class="filter-info">
      <span class="filter-badge">
        <?= mysqli_num_rows($query_produk) ?> Produk Ditemukan
      </span>
      <a href="menu.php" class="btn-reset">
        ← Kembali ke Kategori
      </a>
    </div>
  </div>

  <?php if (mysqli_num_rows($query_produk) == 0): ?>
    <div class="empty-state">
      <div class="empty-state-icon">🍽️</div>
      <h3>Produk Belum Tersedia</h3>
      <p>Produk untuk kategori ini akan segera hadir. Silakan cek kategori lainnya.</p>
      <br>
      <a href="menu.php" class="btn-reset">← Kembali ke Kategori</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php while ($produk = mysqli_fetch_assoc($query_produk)): ?>
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
          <a href="#" class="btn-detail">Pesan</a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>

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