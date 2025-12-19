<?php
include 'koneksi.php';

/* =========================
   HAPUS PRODUK
========================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id=$id");
    header("Location: produk_admin.php");
    exit;
}

/* =========================
   FILTER PENCARIAN
========================= */
$where = [];
$query = "SELECT * FROM produk";

if (!empty($_GET['cari'])) {
    $key = mysqli_real_escape_string($conn, $_GET['cari']);
    $where[] = "nama LIKE '%$key%'";
}

if (!empty($_GET['kategori'])) {
    $kat = mysqli_real_escape_string($conn, $_GET['kategori']);
    $where[] = "kategori='$kat'";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY id DESC";
$produk = mysqli_query($conn, $query);

/* =========================
   HITUNG TOTAL PRODUK
========================= */
$total_produk = mysqli_num_rows($produk);

/* =========================
   GET KATEGORI UNIK
========================= */
$kategori_query = mysqli_query($conn, "SELECT DISTINCT kategori FROM produk ORDER BY kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Produk - Zarali's Catering</title>
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

.header-date {
    font-size: 15px;
    font-weight: 500;
    background-color: rgba(255, 255, 255, 0.2);
    padding: 10px 20px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* =====================================
   CONTENT BODY
===================================== */
.content-body {
    padding: 40px;
}

/* =====================================
   TOOLBAR
===================================== */
.toolbar {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.toolbar-left {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.product-count {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-purple);
    display: flex;
    align-items: center;
    gap: 8px;
}

.count-badge {
    background: linear-gradient(135deg, var(--secondary-gold), #FFC700);
    color: var(--primary-purple);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 16px;
    font-weight: 700;
}

.search-filter-box {
    display: flex;
    gap: 10px;
    flex: 1;
    max-width: 600px;
}

.search-input {
    flex: 1;
    padding: 12px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
}

.filter-select {
    padding: 12px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary-purple);
}

.btn-search {
    padding: 12px 25px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(123, 44, 191, 0.4);
}

.btn-add {
    padding: 12px 25px;
    background: linear-gradient(135deg, #00C853, #00E676);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 200, 83, 0.3);
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 200, 83, 0.4);
}

/* =====================================
   PRODUCT GRID
===================================== */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
    transition: all 0.3s ease;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(123, 44, 191, 0.2);
}

.product-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: #F5F5F5;
}

.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, var(--secondary-gold), #FFC700);
    color: var(--primary-purple);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.product-info {
    padding: 20px;
}

.product-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-category {
    display: inline-block;
    padding: 5px 12px;
    background-color: #F0F0F0;
    color: #666;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 12px;
}

.product-price {
    font-size: 22px;
    font-weight: 700;
    color: #00C853;
    margin-bottom: 15px;
}

.product-actions {
    display: flex;
    gap: 10px;
    padding: 0 20px 20px;
}

.btn-edit {
    flex: 1;
    padding: 10px;
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
}

.btn-delete {
    flex: 1;
    padding: 10px;
    background: linear-gradient(135deg, #F44336, #E57373);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
}

/* =====================================
   EMPTY STATE
===================================== */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.empty-state h3 {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 10px;
}

.empty-state p {
    font-size: 16px;
    color: #666;
    margin-bottom: 25px;
}

/* =====================================
   RESPONSIVE DESIGN
===================================== */
@media (max-width: 1200px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

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

    .toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .toolbar-left {
        flex-direction: column;
    }

    .search-filter-box {
        flex-direction: column;
        max-width: 100%;
    }

    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr;
    }

    .product-actions {
        flex-direction: column;
    }
}
</style>
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
        <a href="produk_admin.php" class="active">
            <span class="menu-icon">🍽️</span>
            <span>Produk</span>
        </a>
        <a href="pesanan_admin.php">
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
        <h2>Manajemen Produk</h2>
        <div class="header-date">
            <span>📅</span>
            <span><?= date('d M Y') ?></span>
        </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content-body">

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="product-count">
                    <span>Total Produk:</span>
                    <span class="count-badge"><?= $total_produk ?></span>
                </div>

                <form method="GET" class="search-filter-box">
                    <input 
                        type="text" 
                        name="cari" 
                        class="search-input"
                        placeholder="🔍 Cari nama produk..." 
                        value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>"
                    >
                    
                    <select name="kategori" class="filter-select">
                        <option value="">Semua Kategori</option>
                        <?php 
                        mysqli_data_seek($kategori_query, 0);
                        while($k = mysqli_fetch_assoc($kategori_query)): 
                        ?>
                            <option value="<?= $k['kategori'] ?>" 
                                <?= (isset($_GET['kategori']) && $_GET['kategori'] == $k['kategori']) ? 'selected' : '' ?>>
                                <?= $k['kategori'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <button type="submit" class="btn-search">Cari</button>
                </form>
            </div>

            <a href="tambah_produk_admin.php" class="btn-add">
                <span>➕</span>
                <span>Tambah Produk</span>
            </a>
        </div>

        <!-- PRODUCT GRID -->
        <?php if($total_produk > 0): ?>
            <div class="product-grid">
                <?php 
                mysqli_data_seek($produk, 0);
                while ($p = mysqli_fetch_assoc($produk)): 
                ?>
                <div class="product-card">
                    <img src="uploads/<?= $p['gambar'] ?>" alt="<?= $p['nama'] ?>" class="product-image">
                    <div class="product-badge"><?= $p['kategori'] ?></div>
                    
                    <div class="product-info">
                        <h3 class="product-name"><?= $p['nama'] ?></h3>
                        <div class="product-price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                    </div>

                    <div class="product-actions">
                        <a class="btn-edit" href="edit_produk.php?id=<?= $p['id'] ?>">
                            ✏️ Edit
                        </a>
                        <a class="btn-delete" href="?hapus=<?= $p['id'] ?>"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                            🗑️ Hapus
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🍽️</div>
                <h3>Belum Ada Produk</h3>
                <p>Mulai tambahkan produk catering Anda untuk ditampilkan di sini</p>
                <a href="tambah_produk_admin.php" class="btn-add">
                    <span>➕</span>
                    <span>Tambah Produk Pertama</span>
                </a>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>