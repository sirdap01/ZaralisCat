<?php
session_start();

// Jika sudah konfirmasi logout
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Hapus semua session variables
    $_SESSION = array();

    // Hapus session cookie jika ada
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // Hancurkan session
    session_destroy();

    // Redirect ke halaman index
    header("Location: index.php");
    exit;
}

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['nama'] ?? 'User';
$user_email = $_SESSION['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logout - Zarali's Catering</title>
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
    background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 50%, rgba(255, 215, 0, 0.1), transparent 50%);
    pointer-events: none;
}

/* ===== OVERLAY ===== */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px);
    z-index: 9998;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* ===== MODAL (COMPACT) ===== */
.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    padding: 35px 30px;
    box-shadow: 0 20px 60px rgba(123, 44, 191, 0.4);
    z-index: 9999;
    max-width: 420px;
    width: 90%;
    animation: slideUp 0.4s ease;
}

@keyframes slideUp {
    from {
        transform: translate(-50%, -40%);
        opacity: 0;
    }
    to {
        transform: translate(-50%, -50%);
        opacity: 1;
    }
}

/* ===== MODAL CONTENT ===== */
.modal-logo {
    width: 55px;
    height: 55px;
    margin: 0 auto 20px;
    border-radius: 50%;
    border: 3px solid var(--secondary-gold);
    display: block;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.modal-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    box-shadow: 0 8px 25px rgba(123, 44, 191, 0.3);
    animation: bounce 0.6s ease;
    border: 3px solid rgba(255, 215, 0, 0.3);
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.modal-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-purple);
    text-align: center;
    margin-bottom: 10px;
}

.modal-message {
    font-size: 14px;
    color: #666;
    text-align: center;
    line-height: 1.6;
    margin-bottom: 20px;
}

.modal-user {
    background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), rgba(157, 78, 221, 0.05));
    padding: 14px 16px;
    border-radius: 12px;
    margin-bottom: 25px;
    border-left: 4px solid var(--secondary-gold);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
}

.modal-user-label {
    font-size: 11px;
    color: #999;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modal-user-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-user-email {
    font-size: 13px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ===== MODAL ACTIONS ===== */
.modal-actions {
    display: flex;
    gap: 12px;
}

.btn {
    flex: 1;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.btn-cancel {
    background: white;
    color: var(--primary-purple);
    border: 2px solid var(--primary-purple);
}

.btn-cancel:hover {
    background: var(--primary-purple);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.3);
}

.btn-logout {
    background: linear-gradient(135deg, #F44336, #E57373);
    color: white;
    box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
}

.btn-logout:hover {
    background: linear-gradient(135deg, #E53935, #EF5350);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
}

.btn-icon {
    font-size: 16px;
}

/* ===== FOOTER NOTE ===== */
.footer-note {
    text-align: center;
    font-size: 12px;
    color: #999;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #F0F0F0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .modal {
        padding: 30px 25px;
    }

    .modal-title {
        font-size: 20px;
    }

    .modal-icon {
        width: 60px;
        height: 60px;
        font-size: 32px;
    }
}

@media (max-width: 480px) {
    .modal {
        padding: 25px 20px;
        border-radius: 16px;
        max-width: 340px;
    }

    .modal-title {
        font-size: 18px;
    }

    .modal-message {
        font-size: 13px;
    }

    .modal-actions {
        flex-direction: column;
        gap: 10px;
    }

    .btn {
        padding: 11px 20px;
        font-size: 13px;
    }

    .modal-logo {
        width: 50px;
        height: 50px;
    }

    .modal-icon {
        width: 55px;
        height: 55px;
        font-size: 28px;
    }

    .modal-user {
        padding: 12px 14px;
    }

    .modal-user-name {
        font-size: 15px;
    }

    .modal-user-email {
        font-size: 12px;
    }

    .footer-note {
        font-size: 11px;
        margin-top: 16px;
        padding-top: 14px;
    }
}
</style>
</head>

<body>

<!-- OVERLAY -->
<div class="overlay"></div>

<!-- MODAL -->
<div class="modal">
    <img src="gambar/logo.png" alt="Logo Zarali's Catering" class="modal-logo">
    
    <div class="modal-icon">👋</div>
    
    <h2 class="modal-title">Konfirmasi Logout</h2>
    
    <p class="modal-message">
        Apakah Anda yakin ingin keluar dari akun Anda?<br>
        Anda dapat login kembali kapan saja.
    </p>

    <div class="modal-user">
        <div class="modal-user-label">Akun Aktif</div>
        <div class="modal-user-name">
            <span>👤</span>
            <span><?= htmlspecialchars($user_name) ?></span>
        </div>
        <?php if ($user_email): ?>
        <div class="modal-user-email">
            <span>📧</span>
            <span><?= htmlspecialchars($user_email) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="modal-actions">
        <a href="index.php" class="btn btn-cancel">
            <span class="btn-icon">←</span>
            <span>Batal</span>
        </a>
        <a href="logout.php?confirm=yes" class="btn btn-logout">
            <span class="btn-icon">✓</span>
            <span>Ya, Logout</span>
        </a>
    </div>

    <div class="footer-note">
        Terima kasih telah menggunakan Zarali's Catering 💜
    </div>
</div>

</body>
</html>