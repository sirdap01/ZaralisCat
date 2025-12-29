<?php
session_start();
include '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['id_pengguna'];

// Check if order ID is provided
if (!isset($_GET['pesanan'])) {
    header("Location: testi.php");
    exit;
}

$pesanan_id = (int)$_GET['pesanan'];

// Verify user owns this order and it's completed
$verify_query = mysqli_query($koneksi, "
    SELECT p.*, COUNT(pd.id) as jumlah_item
    FROM pesanan p
    LEFT JOIN pesanan_detail pd ON p.id_pesanan = pd.pesanan_id
    WHERE p.id_pesanan = $pesanan_id
    AND p.id_pengguna = $user_id
    AND p.status IN ('Lunas', 'Selesai')
    GROUP BY p.id_pesanan
");

if (!$verify_query || mysqli_num_rows($verify_query) == 0) {
    $_SESSION['error'] = "Pesanan tidak ditemukan atau belum selesai!";
    header("Location: testi.php");
    exit;
}

$order = mysqli_fetch_assoc($verify_query);

// Check if user has already reviewed this order
$check_review = mysqli_query($koneksi, "
    SELECT id_ulasan 
    FROM ulasan 
    WHERE id_pengguna = $user_id 
    AND id_pesanan = $pesanan_id
");

if (mysqli_num_rows($check_review) > 0) {
    $_SESSION['error'] = "Anda sudah memberikan ulasan untuk pesanan ini!";
    header("Location: testi.php");
    exit;
}

// Get order items
$items_query = mysqli_query($koneksi, "
    SELECT pd.*, p.nama, p.gambar
    FROM pesanan_detail pd
    JOIN produk p ON pd.produk_id = p.id
    WHERE pd.pesanan_id = $pesanan_id
    ORDER BY pd.id ASC
");

$order_items = [];
while ($item = mysqli_fetch_assoc($items_query)) {
    $order_items[] = $item;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)$_POST['rating'];
    $komentar = trim($_POST['komentar']);
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        $error = "Rating harus antara 1-5 bintang!";
    } elseif (strlen($komentar) < 20) {
        $error = "Komentar minimal 20 karakter!";
    } elseif (strlen($komentar) > 500) {
        $error = "Komentar maksimal 500 karakter!";
    } else {
        // Insert review
        $komentar_escaped = mysqli_real_escape_string($koneksi, $komentar);
        
        $insert = mysqli_query($koneksi, "
            INSERT INTO ulasan (id_pengguna, id_pesanan, komentar, rating, created_at)
            VALUES ($user_id, $pesanan_id, '$komentar_escaped', $rating, NOW())
        ");
        
        if ($insert) {
            $_SESSION['success'] = "Terima kasih! Ulasan Anda berhasil dikirim.";
            header("Location: testi.php");
            exit;
        } else {
            $error = "Gagal menyimpan ulasan: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tulis Testimoni – Zarali's Catering</title>
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
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .container {
      max-width: 800px;
      width: 100%;
    }

    .card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(123, 44, 191, 0.15);
      overflow: hidden;
    }

    .card-header {
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      padding: 30px;
      text-align: center;
    }

    .card-header h2 {
      font-size: 28px;
      font-weight: 700;
      color: var(--secondary-gold);
      margin-bottom: 5px;
    }

    .card-header p {
      font-size: 14px;
      opacity: 0.9;
    }

    .card-body {
      padding: 40px;
    }

    .error-message {
      background: linear-gradient(135deg, #FFEBEE, #FFCDD2);
      color: #C62828;
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 25px;
      border-left: 4px solid #F44336;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      font-weight: 600;
    }

    .order-summary {
      padding: 20px;
      background: #F9F9F9;
      border-radius: 12px;
      margin-bottom: 30px;
      border-left: 4px solid var(--primary-purple);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .order-number {
      font-size: 20px;
      font-weight: 700;
      color: var(--primary-purple);
    }

    .order-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .order-info-item {
      text-align: center;
      padding: 10px;
      background: white;
      border-radius: 8px;
    }

    .order-info-label {
      font-size: 12px;
      color: #666;
      margin-bottom: 5px;
    }

    .order-info-value {
      font-size: 16px;
      font-weight: 700;
      color: var(--text-dark);
    }

    .order-items-title {
      font-size: 16px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 10px;
    }

    .order-items-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .order-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px;
      background: white;
      border-radius: 8px;
    }

    .item-image {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      object-fit: cover;
      border: 2px solid var(--secondary-gold);
    }

    .item-info {
      flex: 1;
    }

    .item-name {
      font-size: 14px;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 2px;
    }

    .item-qty {
      font-size: 12px;
      color: #666;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      font-size: 15px;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 10px;
    }

    .required {
      color: #F44336;
    }

    /* STAR RATING */
    .star-rating {
      display: flex;
      gap: 10px;
      font-size: 40px;
      margin: 15px 0;
      justify-content: center;
    }

    .star-rating input {
      display: none;
    }

    .star-rating label {
      cursor: pointer;
      color: #E0E0E0;
      transition: all 0.2s ease;
      margin: 0;
    }

    .star-rating label:hover,
    .star-rating label:hover ~ label {
      color: var(--secondary-gold);
      transform: scale(1.1);
    }

    .star-rating input:checked ~ label {
      color: var(--secondary-gold);
    }

    .rating-text {
      font-size: 16px;
      font-weight: 600;
      color: var(--primary-purple);
      margin-top: 10px;
      text-align: center;
    }

    textarea {
      width: 100%;
      padding: 14px 18px;
      border: 2px solid #E0E0E0;
      border-radius: 10px;
      font-size: 15px;
      font-family: 'Poppins', sans-serif;
      resize: vertical;
      min-height: 150px;
      transition: all 0.3s ease;
    }

    textarea:focus {
      outline: none;
      border-color: var(--primary-purple);
      box-shadow: 0 0 0 3px rgba(123,44,191,0.1);
    }

    .char-counter {
      font-size: 12px;
      color: #999;
      text-align: right;
      margin-top: 5px;
    }

    .char-counter.warning {
      color: #F44336;
    }

    .input-hint {
      font-size: 12px;
      color: #999;
      margin-top: 5px;
    }

    .form-actions {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }

    .btn {
      flex: 1;
      padding: 14px 20px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
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

    .btn-primary:disabled {
      background: #BDBDBD;
      cursor: not-allowed;
      box-shadow: none;
    }

    .btn-secondary {
      background: white;
      color: var(--primary-purple);
      border: 2px solid var(--primary-purple);
    }

    .btn-secondary:hover {
      background: var(--primary-purple);
      color: white;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      body {
        padding: 20px 10px;
      }

      .card-body {
        padding: 30px 20px;
      }

      .form-actions {
        flex-direction: column;
      }

      .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .star-rating {
        font-size: 36px;
      }

      .order-info-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>✍️ Tulis Ulasan</h2>
            <p>Bagikan pengalaman Anda dengan layanan kami</p>
        </div>

        <div class="card-body">
            <?php if (isset($error)): ?>
            <div class="error-message">
                <span>⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <!-- ORDER SUMMARY -->
            <div class="order-summary">
                <div class="order-header">
                    <div class="order-number">📦 Pesanan #<?= $order['id_pesanan'] ?></div>
                </div>

                <div class="order-info-grid">
                    <div class="order-info-item">
                        <div class="order-info-label">Tanggal Pesanan</div>
                        <div class="order-info-value">
                            <?= date('d M Y', strtotime($order['tanggal_pesanan'])) ?>
                        </div>
                    </div>
                    <div class="order-info-item">
                        <div class="order-info-label">Total Item</div>
                        <div class="order-info-value"><?= $order['jumlah_item'] ?> item</div>
                    </div>
                    <div class="order-info-item">
                        <div class="order-info-label">Total Bayar</div>
                        <div class="order-info-value">
                            Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="order-info-item">
                        <div class="order-info-label">Status</div>
                        <div class="order-info-value"><?= $order['status'] ?></div>
                    </div>
                </div>

                <div class="order-items-title">📋 Item Pesanan:</div>
                <div class="order-items-list">
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <img src="../uploads/produk/<?= htmlspecialchars($item['gambar']) ?>" 
                             alt="<?= htmlspecialchars($item['nama']) ?>" 
                             class="item-image"
                             onerror="this.src='../gambar/placeholder.jpg';">
                        <div class="item-info">
                            <div class="item-name"><?= htmlspecialchars($item['nama']) ?></div>
                            <div class="item-qty"><?= $item['qty'] ?>x @ Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="POST" id="reviewForm">
                <!-- RATING -->
                <div class="form-group">
                    <label>Rating <span class="required">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5">⭐</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">⭐</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">⭐</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">⭐</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1">⭐</label>
                    </div>
                    <div class="rating-text" id="ratingText">Pilih rating Anda</div>
                </div>

                <!-- COMMENT -->
                <div class="form-group">
                    <label for="komentar">Komentar <span class="required">*</span></label>
                    <textarea id="komentar" 
                              name="komentar" 
                              placeholder="Ceritakan pengalaman Anda... Contoh: Makanannya enak, pelayanan ramah, pengiriman tepat waktu, dll."
                              required
                              minlength="20"
                              maxlength="500"
                              oninput="updateCharCount()"></textarea>
                    <div class="char-counter" id="charCounter">0 / 500 karakter</div>
                    <span class="input-hint">Minimal 20 karakter, maksimal 500 karakter</span>
                </div>

                <div class="form-actions">
                    <a href="testi.php" class="btn btn-secondary">
                        <span>←</span>
                        <span>Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span>📤</span>
                        <span>Kirim Ulasan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Rating text update
document.querySelectorAll('input[name="rating"]').forEach(input => {
    input.addEventListener('change', function() {
        const ratingTexts = {
            '5': '⭐⭐⭐⭐⭐ Sangat Puas!',
            '4': '⭐⭐⭐⭐ Puas',
            '3': '⭐⭐⭐ Cukup',
            '2': '⭐⭐ Kurang Puas',
            '1': '⭐ Tidak Puas'
        };
        document.getElementById('ratingText').textContent = ratingTexts[this.value];
    });
});

// Character counter
function updateCharCount() {
    const textarea = document.getElementById('komentar');
    const counter = document.getElementById('charCounter');
    const length = textarea.value.length;
    
    counter.textContent = length + ' / 500 karakter';
    
    if (length < 20) {
        counter.classList.add('warning');
    } else {
        counter.classList.remove('warning');
    }
}

// Form validation
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    const rating = document.querySelector('input[name="rating"]:checked');
    const komentar = document.getElementById('komentar').value;
    const submitBtn = document.getElementById('submitBtn');
    
    if (!rating) {
        e.preventDefault();
        alert('Silakan pilih rating!');
        return;
    }
    
    if (komentar.length < 20) {
        e.preventDefault();
        alert('Komentar minimal 20 karakter!');
        return;
    }
    
    if (komentar.length > 500) {
        e.preventDefault();
        alert('Komentar maksimal 500 karakter!');
        return;
    }
    
    // Disable button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳</span><span>Mengirim...</span>';
});
</script>

</body>
</html>