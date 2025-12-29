<?php
session_start();
include 'koneksi.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: produk_admin.php");
    exit;
}

$id = (int) $_GET['id'];

// Get product data
$data = mysqli_query($conn, "SELECT * FROM produk WHERE id=$id");

if (!$data || mysqli_num_rows($data) == 0) {
    $_SESSION['error'] = "Produk tidak ditemukan!";
    header("Location: produk_admin.php");
    exit;
}

$p = mysqli_fetch_assoc($data);

/* ======================
   UPDATE PRODUK
====================== */
if (isset($_POST['update'])) {

    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $harga     = (int) $_POST['harga'];
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);

    // Validate input
    if (empty($nama) || empty($deskripsi) || $harga <= 0) {
        $_SESSION['error'] = "Semua field harus diisi dengan benar!";
        header("Location: edit_produk_admin.php?id=$id");
        exit;
    }

    // Handle image upload
    $gambar = $p['gambar']; // Keep old image by default
    
    if (!empty($_FILES['gambar']['name'])) {
        $file = $_FILES['gambar'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Format file harus JPG atau PNG!";
            header("Location: edit_produk_admin.php?id=$id");
            exit;
        }
        
        // Validate file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = "Ukuran file maksimal 2MB!";
            header("Location: edit_produk_admin.php?id=$id");
            exit;
        }
        
        // Generate unique filename
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = 'produk_' . time() . '_' . uniqid() . '.' . $file_ext;
        
        // Upload directory
        $upload_dir = '../uploads/produk/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
            // Delete old image if exists and different
            if ($p['gambar'] && file_exists($upload_dir . $p['gambar'])) {
                unlink($upload_dir . $p['gambar']);
            }
            $gambar = $new_filename;
        } else {
            $_SESSION['error'] = "Gagal upload gambar!";
            header("Location: edit_produk_admin.php?id=$id");
            exit;
        }
    }

    // Update database
    $update = mysqli_query($conn, "
        UPDATE produk SET
            nama='$nama',
            deskripsi='$deskripsi',
            harga=$harga,
            kategori='$kategori',
            gambar='$gambar'
        WHERE id=$id
    ");

    if ($update) {
        $_SESSION['success'] = "Produk berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal update produk: " . mysqli_error($conn);
    }

    header("Location: produk_admin.php");
    exit;
}

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Produk - Zarali's Catering</title>
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
    max-width: 700px;
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

.card-header .product-id {
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
    margin-left: 4px;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 15px;
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

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.form-group select {
    cursor: pointer;
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
    padding: 14px 18px;
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

/* ===== CURRENT IMAGE ===== */
.current-image-box {
    margin-top: 15px;
    padding: 15px;
    background: #F9F9F9;
    border-radius: 10px;
    border: 2px solid #E0E0E0;
}

.current-image-label {
    font-size: 13px;
    font-weight: 600;
    color: #666;
    margin-bottom: 10px;
    display: block;
}

.current-image {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid var(--secondary-gold);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* ===== FORM ACTIONS ===== */
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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
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

/* ===== RESPONSIVE ===== */
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

    .btn {
        width: 100%;
    }

    .current-image {
        width: 100%;
        max-width: 300px;
    }
}
</style>
</head>

<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Edit Produk</h2>
            <div class="product-id">ID Produk: #<?= $id ?></div>
        </div>

        <div class="card-body">
            <?php if ($error): ?>
            <div class="error-message">
                <span>⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="editForm">
                <div class="form-group">
                    <label for="nama">Nama Produk <span class="required">*</span></label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           value="<?= htmlspecialchars($p['nama']) ?>" 
                           placeholder="Contoh: Nasi Kotak Premium"
                           required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span class="required">*</span></label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              placeholder="Jelaskan detail produk..."
                              required><?= htmlspecialchars($p['deskripsi']) ?></textarea>
                    <span class="input-hint">Minimal 20 karakter</span>
                </div>

                <div class="form-group">
                    <label for="harga">Harga <span class="required">*</span></label>
                    <input type="number" 
                           id="harga" 
                           name="harga" 
                           value="<?= $p['harga'] ?>" 
                           min="1000"
                           step="1000"
                           placeholder="50000"
                           required>
                    <span class="input-hint">Dalam Rupiah (minimal Rp 1.000)</span>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori <span class="required">*</span></label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Paket Besar" <?= $p['kategori']=='Paket Besar'?'selected':'' ?>>Paket Besar</option>
                        <option value="Kue Satuan" <?= $p['kategori']=='Kue Satuan'?'selected':'' ?>>Kue Satuan</option>
                        <option value="Minuman" <?= $p['kategori']=='Minuman'?'selected':'' ?>>Minuman</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gambar">Gambar Produk (Opsional)</label>
                    <div class="file-upload-wrapper">
                        <label for="gambar" class="file-upload-label">
                            <span class="file-upload-icon">📷</span>
                            <span class="file-upload-text">Pilih gambar baru (JPG/PNG - Max 2MB)</span>
                        </label>
                        <input type="file" 
                               id="gambar" 
                               name="gambar" 
                               class="file-upload-input"
                               accept="image/jpeg,image/png,image/jpg">
                    </div>
                    <div class="file-preview" id="filePreview">
                        <span>📄</span>
                        <span class="file-preview-name" id="fileName"></span>
                        <button type="button" class="file-remove" onclick="removeFile()">Hapus</button>
                    </div>
                    
                    <div class="current-image-box">
                        <span class="current-image-label">📷 Gambar Saat Ini:</span>
                        <img src="../uploads/produk/<?= htmlspecialchars($p['gambar']) ?>" 
                             alt="<?= htmlspecialchars($p['nama']) ?>" 
                             class="current-image"
                             onerror="this.src='../gambar/placeholder.jpg';">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="produk_admin.php" class="btn btn-secondary">
                        <span>←</span>
                        <span>Kembali</span>
                    </a>
                    <button type="submit" name="update" class="btn btn-primary" id="submitBtn">
                        <span>💾</span>
                        <span>Update Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// File preview
document.getElementById('gambar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB!');
            this.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file harus JPG atau PNG!');
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
    document.getElementById('gambar').value = '';
    document.getElementById('filePreview').classList.remove('show');
}

// Form validation
document.getElementById('editForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const deskripsi = document.getElementById('deskripsi').value;
    
    // Validate description length
    if (deskripsi.length < 20) {
        e.preventDefault();
        alert('Deskripsi minimal 20 karakter!');
        return;
    }
    
    // Disable button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>⏳</span><span>Memproses...</span>';
});
</script>

</body>
</html>