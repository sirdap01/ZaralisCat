<?php
session_start();
include 'koneksi.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: pesanan_admin.php");
    exit;
}

$id = (int) $_GET['id'];

// Handle form submission
if (isset($_POST['update'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update = mysqli_query($conn, "
        UPDATE pesanan 
        SET status='$status' 
        WHERE id_pesanan=$id
    ");
    
    if ($update) {
        $_SESSION['success'] = "Status pesanan berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal update status: " . mysqli_error($conn);
    }
    
    header("Location: pesanan_admin.php");
    exit;
}

// Get order data
$query = mysqli_query($conn, "SELECT * FROM pesanan WHERE id_pesanan=$id");

if (!$query || mysqli_num_rows($query) == 0) {
    die("Pesanan tidak ditemukan!");
}

$p = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Status Pesanan #<?= $id ?> - Zarali's Catering</title>
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
    padding: 20px;
}

.container {
    max-width: 600px;
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

.card-header .order-id {
    font-size: 14px;
    opacity: 0.9;
}

.card-body {
    padding: 40px;
}

.info-box {
    background: #F9F9F9;
    border-left: 4px solid var(--primary-purple);
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
}

.info-label {
    font-weight: 600;
    color: #666;
}

.info-value {
    font-weight: 700;
    color: var(--text-dark);
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

.form-group select {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #E0E0E0;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.form-group select:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
}

.form-group select option {
    padding: 10px;
}

.status-info {
    background: #FFF3CD;
    border: 1px solid #FFE69C;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 25px;
    font-size: 13px;
    color: #856404;
}

.status-info strong {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
}

.status-info ul {
    margin-left: 20px;
    margin-top: 8px;
}

.status-info li {
    margin-bottom: 5px;
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
    box-shadow: 0 6px 20px rgba(123, 44, 191, 0.4);
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

.current-status {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

.status-pending {
    background: linear-gradient(135deg, #FFF3CD, #FFE69C);
    color: #856404;
}

.status-proses {
    background: linear-gradient(135deg, #D1ECF1, #BEE5EB);
    color: #0C5460;
}

.status-selesai {
    background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
    color: #155724;
}

.status-lunas {
    background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
    color: #155724;
}

.status-batal {
    background: linear-gradient(135deg, #F8D7DA, #F5C2C7);
    color: #721C24;
}

@media (max-width: 768px) {
    .card-body {
        padding: 30px 20px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>
</head>

<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Edit Status Pesanan</h2>
            <div class="order-id">ID Pesanan: #<?= $id ?></div>
        </div>

        <div class="card-body">
            <!-- ORDER INFO -->
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Nama Pelanggan:</span>
                    <span class="info-value"><?= htmlspecialchars($p['nama_pelanggan']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Pesanan:</span>
                    <span class="info-value"><?= date('d M Y', strtotime($p['tanggal_pesanan'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Harga:</span>
                    <span class="info-value">Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status Saat Ini:</span>
                    <span class="current-status status-<?= strtolower($p['status']) ?>">
                        <?= htmlspecialchars($p['status']) ?>
                    </span>
                </div>
            </div>

            <!-- STATUS INFO -->
            <div class="status-info">
                <strong>ℹ️ Keterangan Status:</strong>
                <ul>
                    <li><strong>Pending:</strong> Pesanan baru masuk, menunggu konfirmasi</li>
                    <li><strong>Proses:</strong> Pesanan sedang diproses/disiapkan</li>
                    <li><strong>Selesai:</strong> Pesanan sudah selesai/dikirim</li>
                    <li><strong>Lunas:</strong> Pesanan sudah dibayar lunas</li>
                    <li><strong>Batal:</strong> Pesanan dibatalkan</li>
                </ul>
            </div>

            <!-- FORM -->
            <form method="POST">
                <div class="form-group">
                    <label for="status">🔄 Ubah Status Pesanan</label>
                    <select name="status" id="status" required>
                        <option value="Pending" <?= $p['status']=='Pending'?'selected':'' ?>>⏳ Pending</option>
                        <option value="Proses" <?= $p['status']=='Proses'?'selected':'' ?>>⚙️ Proses</option>
                        <option value="Selesai" <?= $p['status']=='Selesai'?'selected':'' ?>>✅ Selesai</option>
                        <option value="Lunas" <?= $p['status']=='Lunas'?'selected':'' ?>>💰 Lunas</option>
                        <option value="Batal" <?= $p['status']=='Batal'?'selected':'' ?>>❌ Batal</option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="pesanan_admin.php" class="btn btn-secondary">
                        <span>←</span>
                        <span>Kembali</span>
                    </a>
                    <button type="submit" name="update" class="btn btn-primary">
                        <span>💾</span>
                        <span>Update Status</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>