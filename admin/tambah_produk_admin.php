<?php
include 'koneksi.php';

$success = false;
$error_message = "";

if (isset($_POST['tambah'])) {

    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga     = (int) $_POST['harga'];
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);

    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];

    if ($gambar != "") {
        $upload_dir = "../uploads/produk/";
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $ext = pathinfo($gambar, PATHINFO_EXTENSION);
        $new_name = time() . '_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($tmp, $upload_dir . $new_name)) {
            
            $query = "INSERT INTO produk 
                      (nama, deskripsi, harga, kategori, gambar) 
                      VALUES 
                      ('$nama','$deskripsi','$harga','$kategori','$new_name')";

            if (mysqli_query($conn, $query)) {
                $success = true;
            } else {
                $error_message = "Gagal menyimpan ke database: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Gagal upload gambar!";
        }
    } else {
        $error_message = "Gambar wajib diupload!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Produk - Zarali's Catering</title>
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
   NOTIFICATION POPUP
===================================== */
.notification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.notification-overlay.show {
    display: flex;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.notification-popup {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(123, 44, 191, 0.3);
    animation: slideUp 0.4s ease;
    text-align: center;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.notification-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    animation: bounceIn 0.6s ease;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.notification-icon.success {
    background: linear-gradient(135deg, #00C853, #00E676);
    box-shadow: 0 8px 20px rgba(0, 200, 83, 0.3);
}

.notification-icon.error {
    background: linear-gradient(135deg, #F44336, #E57373);
    box-shadow: 0 8px 20px rgba(244, 67, 54, 0.3);
}

.notification-title {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 15px;
}

.notification-title.success {
    color: #00C853;
}

.notification-title.error {
    color: #F44336;
}

.notification-message {
    font-size: 15px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 30px;
}

.notification-actions {
    display: flex;
    gap: 15px;
}

.btn-notification {
    flex: 1;
    padding: 14px 30px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-success {
    background: linear-gradient(135deg, #00C853, #00E676);
    color: white;
    box-shadow: 0 4px 12px rgba(0, 200, 83, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 200, 83, 0.4);
}

.btn-secondary {
    background: white;
    color: #666;
    border: 2px solid #E0E0E0;
}

.btn-secondary:hover {
    background: #F5F5F5;
    transform: translateY(-2px);
}

/* =====================================
   SIDEBAR (sama seperti sebelumnya)
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

.content-body {
    padding: 40px;
    display: flex;
    justify-content: center;
}

.form-container {
    width: 100%;
    max-width: 800px;
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
.form-textarea,
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
.form-textarea:focus,
.form-select:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 40px;
}

.file-input-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-input-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #F5F5F5, #E8E8E8);
    border: 2px dashed #BDBDBD;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    color: #666;
}

.file-input-label:hover {
    background: linear-gradient(135deg, #E8E8E8, #DADADA);
    border-color: var(--primary-purple);
    color: var(--primary-purple);
}

.file-input-wrapper input[type=file] {
    position: absolute;
    left: -9999px;
}

.file-name {
    font-size: 13px;
    color: #00C853;
    margin-top: 8px;
    font-weight: 500;
}

.file-info {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.image-preview {
    margin-top: 15px;
    display: none;
}

.preview-img {
    width: 100%;
    max-width: 300px;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #E0E0E0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

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

    .form-actions,
    .notification-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Preview gambar sebelum upload
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
        fileName.textContent = '📁 File terpilih: ' + input.files[0].name;
    }
}

// Show notification popup
function showNotification(type) {
    const overlay = document.getElementById('notificationOverlay');
    overlay.classList.add('show');
    
    // Auto redirect after 3 seconds for success
    if (type === 'success') {
        setTimeout(function() {
            window.location.href = 'produk_admin.php';
        }, 3000);
    }
}

// Close notification
function closeNotification() {
    const overlay = document.getElementById('notificationOverlay');
    overlay.classList.remove('show');
}
</script>

</head>

<body>

<!-- NOTIFICATION POPUP -->
<?php if ($success): ?>
<div id="notificationOverlay" class="notification-overlay">
    <div class="notification-popup">
        <div class="notification-icon success">✓</div>
        <h2 class="notification-title success">Berhasil!</h2>
        <p class="notification-message">
            Produk berhasil ditambahkan ke database.<br>
            Anda akan diarahkan ke halaman produk...
        </p>
        <div class="notification-actions">
            <a href="produk_admin.php" class="btn-notification btn-success">
                <span>✓</span>
                <span>Lihat Produk</span>
            </a>
            <a href="tambah_produk_admin.php" class="btn-notification btn-secondary">
                <span>+</span>
                <span>Tambah Lagi</span>
            </a>
        </div>
    </div>
</div>
<script>showNotification('success');</script>
<?php endif; ?>

<?php if ($error_message): ?>
<div id="notificationOverlay" class="notification-overlay">
    <div class="notification-popup">
        <div class="notification-icon error">✕</div>
        <h2 class="notification-title error">Gagal!</h2>
        <p class="notification-message"><?= $error_message ?></p>
        <div class="notification-actions">
            <button onclick="closeNotification()" class="btn-notification btn-success" style="flex: 1;">
                <span>↩️</span>
                <span>Coba Lagi</span>
            </button>
        </div>
    </div>
</div>
<script>showNotification('error');</script>
<?php endif; ?>

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
        <h2>Tambah Produk Baru</h2>
        <div class="breadcrumb">
            <a href="produk_admin.php">🍽️ Produk</a>
            <span>›</span>
            <span>Tambah Produk</span>
        </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content-body">
        
        <div class="form-container">
            <div class="form-title">
                <span>➕</span>
                <span>Form Tambah Produk</span>
            </div>
            <p class="form-subtitle">Lengkapi informasi produk catering yang akan ditambahkan</p>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="form-label">
                        Nama Produk
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nama" 
                        class="form-input"
                        placeholder="Contoh: Nasi Box Premium"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Deskripsi Produk
                        <span class="required">*</span>
                    </label>
                    <textarea 
                        name="deskripsi" 
                        class="form-textarea"
                        placeholder="Jelaskan detail produk, komposisi menu, dan informasi lainnya..."
                        required
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Harga (Rp)
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="harga" 
                        class="form-input"
                        placeholder="Contoh: 50000"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Kategori
                        <span class="required">*</span>
                    </label>
                    <select name="kategori" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Paket Besar">🍱 Paket Besar</option>
                        <option value="Kue Satuan">🍰 Kue Satuan</option>
                        <option value="Minuman">🥤 Minuman</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Gambar Produk
                        <span class="required">*</span>
                    </label>
                    <div class="file-input-wrapper">
                        <label for="gambar" class="file-input-label">
                            <span>📷</span>
                            <span>Pilih Gambar Produk</span>
                        </label>
                        <input 
                            type="file" 
                            id="gambar"
                            name="gambar" 
                            accept="image/*" 
                            onchange="previewImage(this)"
                            required
                        >
                    </div>
                    <div id="fileName" class="file-name"></div>
                    <div class="file-info">Format: JPG, PNG, JPEG (Maks. 2MB)</div>
                    
                    <div id="imagePreview" class="image-preview">
                        <img id="previewImg" class="preview-img" src="" alt="Preview">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="produk_admin.php" class="btn-cancel">
                        <span>↩️</span>
                        <span>Batal</span>
                    </a>
                    <button type="submit" name="tambah" class="btn-submit">
                        <span>✅</span>
                        <span>Simpan Produk</span>
                    </button>
                </div>

            </form>
        </div>

    </div>

</div>

</body>
</html>