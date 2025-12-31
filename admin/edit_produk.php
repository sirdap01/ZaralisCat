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
   HANDLE UPDATE
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $harga     = (int) $_POST['harga'];
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);

    // Validate
    if (empty($nama) || empty($deskripsi) || $harga <= 0 || empty($kategori)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header("Location: edit_produk.php?id=$id");
        exit;
    }

    if (strlen($deskripsi) < 20) {
        $_SESSION['error'] = "Deskripsi minimal 20 karakter!";
        header("Location: edit_produk.php?id=$id");
        exit;
    }

    // Handle image
    $gambar = $p['gambar'];
    
    if (!empty($_FILES['gambar']['name'])) {
        $file = $_FILES['gambar'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
            $_SESSION['error'] = "Format file harus JPG atau PNG!";
            header("Location: edit_produk.php?id=$id");
            exit;
        }
        
        if ($file['size'] > 2097152) { // 2MB
            $_SESSION['error'] = "Ukuran file maksimal 2MB!";
            header("Location: edit_produk.php?id=$id");
            exit;
        }
        
        $new_filename = 'produk_' . time() . '_' . uniqid() . '.' . $file_ext;
        $upload_dir = '../uploads/produk/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
            if ($p['gambar'] && file_exists($upload_dir . $p['gambar'])) {
                @unlink($upload_dir . $p['gambar']);
            }
            $gambar = $new_filename;
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
        header("Location: produk_admin.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal update: " . mysqli_error($conn);
        header("Location: edit_produk.php?id=$id");
        exit;
    }
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
    background-color: white;
    font-family: 'Poppins', sans-serif;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123,44,191,0.1);
}
.form-group textarea {
    resize: vertical;
    min-height: 120px;
}
.input-hint {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}
.current-image-box {
    margin-top: 15px;
    padding: 15px;
    background: #F9F9F9;
    border-radius: 10px;
}
.current-image {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid var(--secondary-gold);
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
@media (max-width: 768px) {
    .card-body {
        padding: 30px 20px;
    }
    .form-actions {
        flex-direction: column;
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

            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="nama">Nama Produk <span class="required">*</span></label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           value="<?= htmlspecialchars($p['nama']) ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span class="required">*</span></label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              required><?= htmlspecialchars($p['deskripsi']) ?></textarea>
                    <div class="input-hint">Minimal 20 karakter</div>
                </div>

                <div class="form-group">
                    <label for="harga">Harga <span class="required">*</span></label>
                    <input type="number" 
                           id="harga" 
                           name="harga" 
                           value="<?= $p['harga'] ?>" 
                           min="1000"
                           required>
                    <div class="input-hint">Dalam Rupiah</div>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori <span class="required">*</span></label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih --</option>
                        <option value="Paket Besar" <?= $p['kategori']=='Paket Besar'?'selected':'' ?>>Paket Besar</option>
                        <option value="Kue Satuan" <?= $p['kategori']=='Kue Satuan'?'selected':'' ?>>Kue Satuan</option>
                        <option value="Minuman" <?= $p['kategori']=='Minuman'?'selected':'' ?>>Minuman</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gambar">Gambar (Opsional)</label>
                    <input type="file" 
                           id="gambar" 
                           name="gambar" 
                           accept="image/jpeg,image/png,image/jpg"
                           style="padding:10px;border:2px solid #E0E0E0;border-radius:10px;width:100%;">
                    
                    <div class="current-image-box">
                        <div style="font-size:13px;font-weight:600;color:#666;margin-bottom:10px;">Gambar Saat Ini:</div>
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
                    <button type="submit" name="update" value="1" class="btn btn-primary">
                        <span>💾</span>
                        <span>Update Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Simple logging
console.log('Form loaded');

document.querySelector('form').addEventListener('submit', function(e) {
    console.log('FORM SUBMITTED!');
    console.log('Nama:', document.getElementById('nama').value);
    console.log('Harga:', document.getElementById('harga').value);
    
    // Check deskripsi length
    const desk = document.getElementById('deskripsi').value;
    if (desk.length < 20) {
        alert('Deskripsi minimal 20 karakter! (Saat ini: ' + desk.length + ' karakter)');
        e.preventDefault();
        return false;
    }
    
    console.log('Validation passed, submitting...');
});
</script>

</body>
</html>