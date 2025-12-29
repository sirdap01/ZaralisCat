<?php
session_start();
include '../includes/config.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['id_pengguna']);
$user_id = $is_logged_in ? (int)$_SESSION['id_pengguna'] : 0;

// Get cart count (for floating cart badge)
$cart_count = 0;
if ($is_logged_in) {
    $cart_result = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM keranjang WHERE id_pengguna = $user_id");
    $cart_data = mysqli_fetch_assoc($cart_result);
    $cart_count = $cart_data['total'] ?? 0;
}

// Get filter parameters
$filter_rating = $_GET['rating'] ?? '';
$sort_by = $_GET['sort'] ?? 'terbaru';

// Build WHERE clause for filters
$where_clauses = ["1=1"];
if (!empty($filter_rating)) {
    $where_clauses[] = "u.rating = " . (int)$filter_rating;
}
$where_sql = implode(" AND ", $where_clauses);

// Determine ORDER BY
$order_by = "u.created_at DESC"; // Default: terbaru
if ($sort_by == 'rating_tinggi') {
    $order_by = "u.rating DESC, u.created_at DESC";
} elseif ($sort_by == 'rating_rendah') {
    $order_by = "u.rating ASC, u.created_at DESC";
}

// Get all testimonials
$query_testimonials = mysqli_query($koneksi, "
    SELECT 
        u.id_ulasan,
        u.komentar,
        u.rating,
        u.created_at,
        us.nama as nama_pengguna,
        p.id_pesanan,
        p.tanggal_pesanan,
        p.total_harga,
        COUNT(pd.id) as jumlah_item
    FROM ulasan u
    JOIN users us ON u.id_pengguna = us.id
    JOIN pesanan p ON u.id_pesanan = p.id_pesanan
    LEFT JOIN pesanan_detail pd ON p.id_pesanan = pd.pesanan_id
    WHERE $where_sql
    GROUP BY u.id_ulasan
    ORDER BY $order_by
");

$total_testimonials = mysqli_num_rows($query_testimonials);

// Get statistics
$stats_query = mysqli_query($koneksi, "
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
    FROM ulasan
");
$stats = mysqli_fetch_assoc($stats_query);
$avg_rating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;

// Check user's transaction status
$user_has_transactions = false;
$completed_orders = [];
$already_reviewed_orders = [];

if ($is_logged_in) {
    // Check if user has completed transactions
    $check_transactions = mysqli_query($koneksi, "
        SELECT COUNT(*) as total 
        FROM pesanan 
        WHERE id_pengguna = $user_id 
        AND status IN ('Lunas', 'Selesai')
    ");
    $trans_data = mysqli_fetch_assoc($check_transactions);
    $user_has_transactions = $trans_data['total'] > 0;
    
    if ($user_has_transactions) {
        // Get completed orders
        $orders_query = mysqli_query($koneksi, "
            SELECT 
                p.id_pesanan,
                p.tanggal_pesanan,
                p.total_harga,
                p.status,
                COUNT(pd.id) as jumlah_item
            FROM pesanan p
            LEFT JOIN pesanan_detail pd ON p.id_pesanan = pd.pesanan_id
            WHERE p.id_pengguna = $user_id 
            AND p.status IN ('Lunas', 'Selesai')
            GROUP BY p.id_pesanan
            ORDER BY p.tanggal_pesanan DESC
        ");
        while ($row = mysqli_fetch_assoc($orders_query)) {
            $completed_orders[] = $row;
        }
        
        // Get orders user has already reviewed
        $reviewed_query = mysqli_query($koneksi, "
            SELECT id_pesanan 
            FROM ulasan 
            WHERE id_pengguna = $user_id
        ");
        while ($row = mysqli_fetch_assoc($reviewed_query)) {
            $already_reviewed_orders[] = $row['id_pesanan'];
        }
    }
}

// Handle review submission
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Testimoni – Zarali's Catering</title>
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
    }

    .banner p {
      font-size: 1.2rem;
      font-weight: 300;
      color: white;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
      margin-bottom: 30px;
    }

    /* ===== CONTENT WRAPPER ===== */
    .content-wrapper {
      padding: 60px 40px;
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
      flex: 1;
    }

    /* ===== STATISTICS BANNER ===== */
    .stats-banner {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
      margin-bottom: 40px;
    }

    .stats-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .stats-header h3 {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-purple);
      margin-bottom: 10px;
    }

    .avg-rating {
      font-size: 48px;
      font-weight: 700;
      color: var(--secondary-gold);
    }

    .stars-display {
      font-size: 32px;
      margin: 10px 0;
    }

    .total-reviews {
      font-size: 16px;
      color: #666;
    }

    .rating-breakdown {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 20px;
    }

    .rating-row {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .rating-label {
      min-width: 60px;
      font-size: 14px;
      font-weight: 600;
    }

    .rating-bar {
      flex: 1;
      height: 24px;
      background: #E0E0E0;
      border-radius: 12px;
      overflow: hidden;
      position: relative;
    }

    .rating-bar-fill {
      height: 100%;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      transition: width 0.5s ease;
    }

    .rating-count {
      min-width: 80px;
      text-align: right;
      font-size: 14px;
      color: #666;
    }

    /* ===== TAB NAVIGATION ===== */
    .tab-navigation {
      display: flex;
      gap: 10px;
      margin-bottom: 30px;
      border-bottom: 3px solid #E0E0E0;
    }

    .tab-button {
      padding: 15px 30px;
      background: transparent;
      border: none;
      font-size: 16px;
      font-weight: 600;
      color: #666;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      margin-bottom: -3px;
      transition: all 0.3s ease;
    }

    .tab-button:hover {
      color: var(--primary-purple);
    }

    .tab-button.active {
      color: var(--primary-purple);
      border-bottom-color: var(--primary-purple);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* ===== FILTER BAR ===== */
    .filter-bar {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-bottom: 30px;
      align-items: center;
    }

    .filter-select {
      padding: 12px 18px;
      border: 2px solid #E0E0E0;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      background: white;
    }

    .filter-select:focus {
      outline: none;
      border-color: var(--primary-purple);
    }

    .filter-reset {
      padding: 12px 24px;
      background: white;
      color: var(--primary-purple);
      border: 2px solid var(--primary-purple);
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .filter-reset:hover {
      background: var(--primary-purple);
      color: white;
    }

    /* ===== TESTIMONIAL CARD ===== */
    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
      gap: 25px;
    }

    .testimonial-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
      transition: all 0.3s ease;
    }

    .testimonial-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(123, 44, 191, 0.15);
    }

    .testimonial-header {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
    }

    .user-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: 700;
    }

    .testimonial-user-info {
      flex: 1;
    }

    .user-name {
      font-size: 16px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 4px;
    }

    .testimonial-date {
      font-size: 13px;
      color: #999;
    }

    .order-info {
      display: flex;
      justify-content: space-between;
      padding: 12px;
      background: #F9F9F9;
      border-radius: 10px;
      margin-bottom: 15px;
    }

    .order-detail {
      font-size: 13px;
      color: #666;
    }

    .order-detail strong {
      color: var(--primary-purple);
      font-weight: 700;
    }

    .rating-stars {
      font-size: 20px;
      margin-bottom: 10px;
    }

    .testimonial-text {
      font-size: 15px;
      line-height: 1.6;
      color: var(--text-dark);
    }

    .verified-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      background: linear-gradient(135deg, #00C853, #00E676);
      color: white;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-top: 10px;
    }

    /* ===== WRITE REVIEW SECTION ===== */
    .auth-required,
    .no-transactions,
    .orders-list {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    }

    .auth-required-icon,
    .no-transactions-icon {
      font-size: 80px;
      margin-bottom: 20px;
    }

    .auth-required h3,
    .no-transactions h3 {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-purple);
      margin-bottom: 15px;
    }

    .auth-required p,
    .no-transactions p {
      font-size: 16px;
      color: #666;
      margin-bottom: 25px;
    }

    .btn-primary {
      padding: 14px 30px;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(123, 44, 191, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(123, 44, 191, 0.4);
    }

    .orders-list {
      text-align: left;
      padding: 40px;
    }

    .orders-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .orders-header h3 {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-purple);
      margin-bottom: 10px;
    }

    .order-item {
      display: flex;
      align-items: center;
      gap: 20px;
      padding: 20px;
      background: #F9F9F9;
      border-radius: 12px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
    }

    .order-item:hover {
      background: #F0F0F0;
    }

    .order-item-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: 700;
    }

    .order-item-info {
      flex: 1;
    }

    .order-item-number {
      font-size: 18px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 5px;
    }

    .order-item-details {
      font-size: 14px;
      color: #666;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    .order-item-status {
      font-size: 14px;
      padding: 4px 12px;
      border-radius: 20px;
      display: inline-block;
    }

    .status-unreviewed {
      background: #FFF3CD;
      color: #856404;
    }

    .status-reviewed {
      background: #D4EDDA;
      color: #155724;
    }

    .btn-review {
      padding: 10px 20px;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
      white-space: nowrap;
    }

    .btn-review:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
    }

    .btn-review:disabled {
      background: #BDBDBD;
      cursor: not-allowed;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-icon {
      font-size: 80px;
      margin-bottom: 20px;
    }

    .empty-state h3 {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-purple);
      margin-bottom: 15px;
    }

    .empty-state p {
      font-size: 16px;
      color: #666;
    }

    /* ===== ALERT MESSAGES ===== */
    .alert {
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
    }

    .alert-success {
      background: #D4EDDA;
      color: #155724;
      border-left: 4px solid #28A745;
    }

    .alert-error {
      background: #F8D7DA;
      color: #721C24;
      border-left: 4px solid #DC3545;
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

      .content-wrapper {
        padding: 30px 16px;
      }

      .testimonials-grid {
        grid-template-columns: 1fr;
      }

      .tab-navigation {
        overflow-x: auto;
      }

      .filter-bar {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-select,
      .filter-reset {
        width: 100%;
      }

      .order-item {
        flex-direction: column;
        text-align: center;
      }

      .order-item-details {
        justify-content: center;
      }
    }

  </style>

  <script>
  // Tab switching
  function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
      tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-button').forEach(btn => {
      btn.classList.remove('active');
    });
    
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
  }

  // Filter form submission
  function applyFilters() {
    const rating = document.getElementById('filterRating').value;
    const sort = document.getElementById('filterSort').value;
    
    let url = 'testi.php?';
    if (rating) url += 'rating=' + rating + '&';
    if (sort) url += 'sort=' + sort;
    
    window.location.href = url;
  }
  </script>
</head>

<body>

<!-- FLOATING CART -->
<?php if ($is_logged_in): ?>
<div class="floating-cart">
  <a href="keranjang/keranjang.php" class="cart-button" title="Lihat Keranjang">
    <div class="cart-icon">🛒</div>
    <span class="cart-badge <?= $cart_count == 0 ? 'empty' : '' ?>">
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
    <a href="testi.php" class="active">Testimoni</a>
    <a href="pesanan.php">Pesanan saya</a>
    <a href="contact.php">Hubungi kami</a>
    <a href="../about.php">Tentang kami</a>
    <a href="<?= $is_logged_in ? '../logout.php' : '../login.php' ?>">
      <?= $is_logged_in ? 'Logout' : 'Login' ?>
    </a>
  </nav>
</header>

<div class="banner">
  <div class="banner-content">
    <h2>Testimoni Pelanggan</h2>
    <p>Dengarkan cerita kepuasan pelanggan kami</p>
  </div>
</div>

<div class="content-wrapper">
  
  <?php if ($success_message): ?>
  <div class="alert alert-success">
    <span>✅</span>
    <span><?= htmlspecialchars($success_message) ?></span>
  </div>
  <?php endif; ?>

  <?php if ($error_message): ?>
  <div class="alert alert-error">
    <span>⚠️</span>
    <span><?= htmlspecialchars($error_message) ?></span>
  </div>
  <?php endif; ?>

  <!-- STATISTICS BANNER -->
  <?php if ($stats['total'] > 0): ?>
  <div class="stats-banner">
    <div class="stats-header">
      <h3>📊 Rating Keseluruhan</h3>
      <div class="avg-rating"><?= $avg_rating ?> / 5.0</div>
      <div class="stars-display">
        <?php 
        for ($i = 1; $i <= 5; $i++) {
          echo $i <= round($avg_rating) ? '⭐' : '☆';
        }
        ?>
      </div>
      <div class="total-reviews">Dari <?= $stats['total'] ?> ulasan</div>
    </div>

    <div class="rating-breakdown">
      <?php 
      for ($i = 5; $i >= 1; $i--):
        $count = $stats['rating_' . $i];
        $percentage = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
      ?>
      <div class="rating-row">
        <div class="rating-label"><?= $i ?>⭐</div>
        <div class="rating-bar">
          <div class="rating-bar-fill" style="width: <?= $percentage ?>%;"></div>
        </div>
        <div class="rating-count"><?= $count ?> (<?= round($percentage) ?>%)</div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- TAB NAVIGATION -->
  <div class="tab-navigation">
    <button class="tab-button active" onclick="switchTab('allTestimonials')">
      📋 Semua Testimoni
    </button>
    <button class="tab-button" onclick="switchTab('writeTestimonial')">
      ✍️ Tulis Testimoni
    </button>
  </div>

  <!-- TAB 1: ALL TESTIMONIALS -->
  <div id="allTestimonials" class="tab-content active">
    
    <?php if ($total_testimonials > 0): ?>
    <!-- FILTER BAR -->
    <div class="filter-bar">
      <select id="filterRating" class="filter-select" onchange="applyFilters()">
        <option value="">⭐ Semua Rating</option>
        <option value="5" <?= $filter_rating == '5' ? 'selected' : '' ?>>5 Bintang</option>
        <option value="4" <?= $filter_rating == '4' ? 'selected' : '' ?>>4 Bintang</option>
        <option value="3" <?= $filter_rating == '3' ? 'selected' : '' ?>>3 Bintang</option>
        <option value="2" <?= $filter_rating == '2' ? 'selected' : '' ?>>2 Bintang</option>
        <option value="1" <?= $filter_rating == '1' ? 'selected' : '' ?>>1 Bintang</option>
      </select>

      <select id="filterSort" class="filter-select" onchange="applyFilters()">
        <option value="terbaru" <?= $sort_by == 'terbaru' ? 'selected' : '' ?>>📅 Terbaru</option>
        <option value="rating_tinggi" <?= $sort_by == 'rating_tinggi' ? 'selected' : '' ?>>⭐ Rating Tertinggi</option>
        <option value="rating_rendah" <?= $sort_by == 'rating_rendah' ? 'selected' : '' ?>>⭐ Rating Terendah</option>
      </select>

      <?php if (!empty($filter_rating) || $sort_by != 'terbaru'): ?>
      <a href="testi.php" class="filter-reset">🔄 Reset</a>
      <?php endif; ?>
    </div>

    <!-- TESTIMONIALS GRID -->
    <div class="testimonials-grid">
      <?php 
      mysqli_data_seek($query_testimonials, 0);
      while ($testi = mysqli_fetch_assoc($query_testimonials)): 
      ?>
      <div class="testimonial-card">
        <div class="testimonial-header">
          <div class="user-avatar">
            <?= strtoupper(substr($testi['nama_pengguna'], 0, 1)) ?>
          </div>
          <div class="testimonial-user-info">
            <div class="user-name"><?= htmlspecialchars($testi['nama_pengguna']) ?></div>
            <div class="testimonial-date">
              📅 <?= date('d M Y', strtotime($testi['created_at'])) ?>
            </div>
          </div>
        </div>

        <div class="order-info">
          <div class="order-detail">
            <strong>Pesanan #<?= $testi['id_pesanan'] ?></strong>
          </div>
          <div class="order-detail">
            📦 <?= $testi['jumlah_item'] ?> item
          </div>
          <div class="order-detail">
            💰 Rp <?= number_format($testi['total_harga'], 0, ',', '.') ?>
          </div>
        </div>

        <div class="rating-stars">
          <?php 
          for ($i = 1; $i <= 5; $i++) {
            echo $i <= $testi['rating'] ? '⭐' : '☆';
          }
          ?>
        </div>

        <div class="testimonial-text">
          "<?= htmlspecialchars($testi['komentar']) ?>"
        </div>

        <div class="verified-badge">
          ✓ Pembelian Terverifikasi
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">💬</div>
      <h3>Belum Ada Testimoni</h3>
      <p>Jadilah yang pertama memberikan ulasan untuk layanan kami!</p>
    </div>
    <?php endif; ?>
  </div>

  <!-- TAB 2: WRITE TESTIMONIAL -->
  <div id="writeTestimonial" class="tab-content">
    
    <?php if (!$is_logged_in): ?>
    <!-- NOT LOGGED IN -->
    <div class="auth-required">
      <div class="auth-required-icon">🔒</div>
      <h3>Login Required</h3>
      <p>Silakan login terlebih dahulu untuk menulis testimoni</p>
      <a href="../login.php" class="btn-primary">🔐 Login</a>
    </div>

    <?php elseif (!$user_has_transactions): ?>
    <!-- NO TRANSACTIONS -->
    <div class="no-transactions">
      <div class="no-transactions-icon">⚠️</div>
      <h3>Belum Ada Transaksi</h3>
      <p>Anda belum pernah memesan dari kami. Silakan order terlebih dahulu untuk memberikan testimoni.</p>
      <a href="../menu.php" class="btn-primary">🛒 Lihat Menu</a>
    </div>

    <?php else: ?>
    <!-- ORDERS LIST -->
    <div class="orders-list">
      <div class="orders-header">
        <h3>✅ Pesanan yang Sudah Selesai</h3>
        <p>Pilih pesanan untuk memberikan ulasan</p>
      </div>

      <?php foreach ($completed_orders as $order): ?>
      <div class="order-item">
        <div class="order-item-icon">
          📦
        </div>
        
        <div class="order-item-info">
          <div class="order-item-number">Pesanan #<?= $order['id_pesanan'] ?></div>
          <div class="order-item-details">
            <span>📅 <?= date('d M Y', strtotime($order['tanggal_pesanan'])) ?></span>
            <span>📦 <?= $order['jumlah_item'] ?> item</span>
            <span>💰 Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
          </div>
          <?php if (in_array($order['id_pesanan'], $already_reviewed_orders)): ?>
            <span class="order-item-status status-reviewed">✓ Sudah diulas</span>
          <?php else: ?>
            <span class="order-item-status status-unreviewed">⏳ Belum diulas</span>
          <?php endif; ?>
        </div>

        <?php if (in_array($order['id_pesanan'], $already_reviewed_orders)): ?>
          <button class="btn-review" disabled>✓ Sudah Diulas</button>
        <?php else: ?>
          <a href="tulis_testimoni.php?pesanan=<?= $order['id_pesanan'] ?>" class="btn-review">
            ✍️ Tulis Ulasan
          </a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

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