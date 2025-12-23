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
    header("Location: ../index.php");
    exit;
}
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
    background-color: var(--background-light);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* =====================================
   OVERLAY
===================================== */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    z-index: 9998;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* =====================================
   MODAL
===================================== */
.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(123, 44, 191, 0.3);
    z-index: 9999;
    max-width: 450px;
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

/* =====================================
   MODAL CONTENT
===================================== */
.modal-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    box-shadow: 0 8px 20px rgba(123, 44, 191, 0.3);
    animation: bounce 0.6s ease;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.modal-title {
    font-size: 26px;
    font-weight: 700;
    color: var(--primary-purple);
    text-align: center;
    margin-bottom: 15px;
}

.modal-message {
    font-size: 15px;
    color: #666;
    text-align: center;
    line-height: 1.6;
    margin-bottom: 35px;
}

.modal-user {
    background: linear-gradient(135deg, #F5F5F5, #E8E8E8);
    padding: 12px 20px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 30px;
    border-left: 4px solid var(--secondary-gold);
}

.modal-user-label {
    font-size: 12px;
    color: #999;
    margin-bottom: 5px;
}

.modal-user-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary-purple);
}

/* =====================================
   MODAL ACTIONS
===================================== */
.modal-actions {
    display: flex;
    gap: 15px;
}

.btn {
    flex: 1;
    padding: 14px 30px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
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
    color: #666;
    border: 2px solid #E0E0E0;
}

.btn-cancel:hover {
    background: #F5F5F5;
    border-color: #BDBDBD;
    transform: translateY(-2px);
}

.btn-logout {
    background: linear-gradient(135deg, #F44336, #E57373);
    color: white;
    box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
}

.btn-logout:hover {
    background: linear-gradient(135deg, #E53935, #EF5350);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(244, 67, 54, 0.4);
}

/* =====================================
   LOGO
===================================== */
.modal-logo {
    width: 60px;
    height: 60px;
    margin: 0 auto 20px;
    border-radius: 50%;
    border: 3px solid var(--secondary-gold);
    display: block;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* =====================================
   RESPONSIVE
===================================== */
@media (max-width: 480px) {
    .modal {
        padding: 30px 25px;
    }

    .modal-title {
        font-size: 22px;
    }

    .modal-actions {
        flex-direction: column;
    }
}
</style>
</head>

<body>

<!-- OVERLAY -->
<div class="overlay"></div>

<!-- MODAL -->
<div class="modal">
    <img src="logo.png" alt="Logo" class="modal-logo">
    
    <div class="modal-icon">🚪</div>
    
    <h2 class="modal-title">Konfirmasi Logout</h2>
    
    <p class="modal-message">
        Apakah Anda yakin ingin keluar dari Admin Panel?
    </p>

    <?php if(isset($_SESSION['admin_username'])): ?>
    <div class="modal-user">
        <div class="modal-user-label">Logged in as</div>
        <div class="modal-user-name">👤 <?= htmlspecialchars($_SESSION['admin_username']) ?></div>
    </div>
    <?php endif; ?>

    <div class="modal-actions">
        <a href="dashboard_admin.php" class="btn btn-cancel">
            <span>↩️</span>
            <span>Batal</span>
        </a>
        <a href="logout_admin.php?confirm=yes" class="btn btn-logout">
            <span>✓</span>
            <span>Ya, Logout</span>
        </a>
    </div>
</div>

</body>
</html>