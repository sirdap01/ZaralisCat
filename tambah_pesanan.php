<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'koneksi.php';

$produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Pesanan - Zarali's Catering</title>
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
}

/* =====================================
   SIDEBAR
===================================== */
.sidebar {
    width: 280px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    height: 100vh;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 30px 0;
    box-shadow: 4px 0 20px rgba(123, 44, 191, 0.2);
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    text-align: center;
    padding: 0 20px 30px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 20px;
}

.sidebar-logo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid var(--secondary-gold);
    background: white;
    margin: 0 auto 15px;
    display: block;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.sidebar-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--secondary-gold);
    margin-bottom: 5px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.sidebar-subtitle {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 400;
}

.sidebar-menu {
    padding: 10px 0;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    padding: 15px 25px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.sidebar-menu a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: var(--secondary-gold);
    padding-left: 30px;
}

.sidebar-menu a.active {
    background-color: var(--secondary-gold);
    color: var(--primary-purple);
    font-weight: 700;
    border-left-color: var(--primary-purple);
}

.menu-icon {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

/* =====================================
   MAIN CONTENT
===================================== */
.main-content {
    margin-left: 280px;
    padding: 0;
    min-height: 100vh;
}

/* =====================================
   HEADER
===================================== */
.content-header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 30px 40px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.content-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: var(--secondary-gold);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.breadcrumb {
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.breadcrumb a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
}

.breadcrumb a:hover {
    color: var(--secondary-gold);
}

.breadcrumb span {
    color: var(--secondary-gold);
}

/* =====================================
   CONTENT BODY
===================================== */
.content-body {
    padding: 40px;
    display: flex;
    justify-content: center;
}

/* =====================================
   FORM CONTAINER
===================================== */
.form-container {
    width: 100%;
    max-width: 900px;
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(123, 44, 191, 0.15);
}

.form-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.form-subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F0F0F0;
}

/* =====================================
   FORM SECTIONS
===================================== */
.form-section {
    margin-bottom: 30px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 20px;
    padding-left: 15px;
    border-left: 4px solid var(--secondary-gold);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* =====================================
   FORM ELEMENTS
===================================== */
.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.form-label .required {
    color: #F44336;
    margin-left: 3px;
}

.form-input,
.form-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    background: white;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
}

.form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 40px;
}

/* =====================================
   ITEM PRODUK DYNAMIC
===================================== */
.items-container {
    background: #F9F9F9;
    border-radius: 10px;
    padding: 20px;
}

.item-row {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    border: 2px solid #E0E0E0;
    position: relative;
    transition: all 0.3s ease;
}

.item-row:hover {
    border-color: var(--accent-purple);
    box-shadow: 0 2px 8px rgba(123, 44, 191, 0.1);
}

.item-number {
    position: absolute;
    top: -12px;
    left: 15px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(123, 44, 191, 0.3);
}

.item-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 15px;
    margin-top: 10px;
}

.btn-remove-item {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #F44336;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
}

.btn-remove-item:hover {
    background: #D32F2F;
    transform: rotate(90deg) scale(1.1);
}

.btn-add-item {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    border: 2px dashed rgba(255, 255, 255, 0.5);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
}

.btn-add-item:hover {
    background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
}

/* =====================================
   PAYMENT INFO BOX
===================================== */
.info-box {
    background: linear-gradient(135deg, #E3F2FD, #BBDEFB);
    border-left: 4px solid #2196F3;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    display: flex;
    gap: 12px;
    align-items: start;
}

.info-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.info-content {
    flex: 1;
}

.info-title {
    font-weight: 700;
    color: #1976D2;
    margin-bottom: 5px;
    font-size: 14px;
}

.info-text {
    font-size: 13px;
    color: #0D47A1;
    line-height: 1.5;
}

/* =====================================
   FORM ACTIONS
===================================== */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 35px;
    padding-top: 25px;
    border-top: 2px solid #F0F0F0;
}

.btn-submit {
    flex: 1;
    padding: 14px 30px;
    background: linear-gradient(135deg, #00C853, #00E676);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 200, 83, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 200, 83, 0.4);
}

.btn-cancel {
    flex: 1;
    padding: 14px 30px;
    background: white;
    color: #666;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-cancel:hover {
    background: #F5F5F5;
    border-color: #BDBDBD;
    transform: translateY(-2px);
}

/* =====================================
   RESPONSIVE DESIGN
===================================== */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main-content {
        margin-left: 0;
    }

    .content-header {
        flex-direction: column;
        gap: 15px;
        padding: 25px 20px;
        text-align: center;
    }

    .content-body {
        padding: 20px;
    }

    .form-container {
        padding: 25px 20px;
    }

    .form-title {
        font-size: 24px;
    }

    .item-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
let itemCount = 1;

function addItem() {
    itemCount++;
    const container = document.getElementById('itemsContainer');
    
    const newItem = document.createElement('div');
    newItem.className = 'item-row';
    newItem.innerHTML = `
        <div class="item-number">${itemCount}</div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">×</button>
        
        <div class="item-grid">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Pilih Produk <span class="required">*</span></label>
                <select name="produk[]" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php 
                    mysqli_data_seek($produk, 0);
                    while($p = mysqli_fetch_assoc($produk)): 
                    ?>
                    <option value="<?= $p['id'] ?>">
                        <?= $p['nama'] ?> - Rp <?= number_format($p['harga'],0,',','.') ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Jumlah <span class="required">*</span></label>
                <input type="number" name="qty[]" class="form-input" value="1" min="1" required>
            </div>
        </div>
    `;
    
    container.appendChild(newItem);
    updateItemNumbers();
}

function removeItem(btn) {
    if (document.querySelectorAll('.item-row').length > 1) {
        btn.closest('.item-row').remove();
        updateItemNumbers();
    } else {
        alert('Minimal harus ada 1 produk!');
    }
}

function updateItemNumbers() {
    const items = document.querySelectorAll('.item-row');
    items.forEach((item, index) => {
        item.querySelector('.item-number').textContent = index + 1;
    });
    itemCount = items.length;
}
</script>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="logo.png" alt="Logo Zarali's Catering" class="sidebar-logo">
        <div class="sidebar-title">Zarali's Catering</div>
        <div class="sidebar-subtitle">Admin Panel</div>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard_admin.php">
            <span class="menu-icon">📊</span>
            <span>Dashboard</span>
        </a>
        <a href="produk_admin.php">
            <span class="menu-icon">🍽️</span>
            <span>Produk</span>
        </a>
        <a href="pesanan_admin.php" class="active">
            <span class="menu-icon">📦</span>
            <span>Pesanan</span>
        </a>
        <a href="riwayat_transaksi_admin.php">
            <span class="menu-icon">💳</span>
            <span>Transaksi</span>
        </a>
        <a href="laporan_penjualan_admin.php">
            <span class="menu-icon">📈</span>
            <span>Laporan</span>
        </a>
        <a href="logout_admin.php">
            <span class="menu-icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    
    <!-- HEADER -->
    <div class="content-header">
        <h2>Tambah Pesanan Baru</h2>
        <div class="breadcrumb">
            <a href="pesanan_admin.php">📦 Pesanan</a>
            <span>›</span>
            <span>Tambah Pesanan</span>
        </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content-body">
        
        <div class="form-container">
            <div class="form-title">
                <span>🛒</span>
                <span>Form Tambah Pesanan</span>
            </div>
            <p class="form-subtitle">Masukkan informasi pelanggan dan produk yang dipesan</p>

            <form method="POST" action="simpan_pesanan.php">

                <!-- DATA PELANGGAN -->
                <div class="form-section">
                    <div class="section-title">
                        <span>👤</span>
                        <span>Data Pelanggan</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Nama Pelanggan
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="nama" 
                            class="form-input"
                            placeholder="Masukkan nama lengkap pelanggan"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Metode Pembayaran
                            <span class="required">*</span>
                        </label>
                        <select name="metode" class="form-select" required>
                            <option value="">-- Pilih Metode --</option>
                            <option value="Tunai">💵 Tunai</option>
                            <option value="Debit">💳 Debit</option>
                            <option value="Transfer">🏦 Transfer Bank</option>
                            <option value="E-Wallet">📱 E-Wallet</option>
                        </select>
                    </div>

                    <div class="info-box">
                        <div class="info-icon">ℹ️</div>
                        <div class="info-content">
                            <div class="info-title">Info Pembayaran</div>
                            <div class="info-text">
                                Pastikan metode pembayaran sesuai dengan kesepakatan dengan pelanggan. 
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DAFTAR PRODUK -->
                <div class="form-section">
                    <div class="section-title">
                        <span>🛍️</span>
                        <span>Daftar Produk</span>
                    </div>

                    <div class="items-container" id="itemsContainer">
                        <div class="item-row">
                            <div class="item-number">1</div>
                            
                            <div class="item-grid">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">
                                        Pilih Produk
                                        <span class="required">*</span>
                                    </label>
                                    <select name="produk[]" class="form-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        <?php 
                                        mysqli_data_seek($produk, 0);
                                        while($p = mysqli_fetch_assoc($produk)): 
                                        ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= $p['nama'] ?> - Rp <?= number_format($p['harga'],0,',','.') ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">
                                        Jumlah
                                        <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        name="qty[]" 
                                        class="form-input" 
                                        value="1" 
                                        min="1" 
                                        required
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-add-item" onclick="addItem()">
                        <span>➕</span>
                        <span>Tambah Produk Lain</span>
                    </button>
                </div>

                <!-- FORM ACTIONS -->
                <div class="form-actions">
                    <a href="pesanan_admin.php" class="btn-cancel">
                        <span>↩️</span>
                        <span>Batal</span>
                    </a>
                    <button type="submit" class="btn-submit">
                        <span>✅</span>
                        <span>Simpan Pesanan</span>
                    </button>
                </div>

            </form>
        </div>

    </div>

</div>

</body>
</html>