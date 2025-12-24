<?php
session_start();
include 'includes/config.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $nama     = htmlspecialchars($_POST['nama']);
    $email    = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Validasi dasar
    if ($password !== $confirm) {
        $error = "Password dan konfirmasi tidak sama!";
    } else {
        // cek email sudah ada atau belum
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = mysqli_query($koneksi, "
                INSERT INTO users (nama, email, password, role)
                VALUES ('$nama', '$email', '$hash', 'user')
            ");

            if ($insert) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Registrasi gagal!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register – Zarali's Catering</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    /* ===== SEMUA STYLE SAMA PERSIS ===== */
    :root {
      --primary-purple: #7B2CBF;
      --secondary-gold: #FFD700;
      --accent-purple: #9D4EDD;
      --text-dark: #222;
      --background-light: #FFFDF8;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
    body {
      background-color: var(--background-light);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      padding: 16px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-height: 85px;
      box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
      flex-shrink: 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .logo-container { display: flex; align-items: center; gap: 14px; min-width: 280px; }
    .logo {
      max-height: 55px; border-radius: 50%; border: 3px solid var(--secondary-gold);
      box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: transform 0.3s ease;
    }
    .logo:hover { transform: scale(1.05); }
    header h1 { font-weight: 700; font-size: 1.9rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); letter-spacing: 0.5px; }
    nav { display: flex; align-items: center; gap: 30px; }
    nav a {
      color: white; text-decoration: none; font-weight: 600; font-size: 15px;
      transition: all 0.3s ease; padding: 8px 12px; border-radius: 6px; position: relative;
    }
    nav a:hover { color: var(--secondary-gold); background-color: rgba(255,255,255,0.1); transform: translateY(-2px); }
    nav a.active { background-color: rgba(255,215,0,0.2); color: var(--secondary-gold); }
    .banner {
      background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
      min-height: 280px; display: flex; flex-direction: column; align-items: center; justify-content: center;
      color: white; padding: 40px 20px; position: relative; overflow: hidden;
    }
    .banner::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      background: radial-gradient(circle at 30% 50%, rgba(255,215,0,0.1), transparent 50%);
      pointer-events: none;
    }
    .banner-content { position: relative; z-index: 2; text-align: center; max-width: 900px; }
    .banner h2 { font-size: 2.8rem; font-weight: 700; color: var(--secondary-gold); text-shadow: 3px 3px 8px rgba(0,0,0,0.4); margin-bottom: 16px; letter-spacing: 1px; animation: fadeInDown 0.8s ease; }
    .banner p { font-size: 1.2rem; font-weight: 300; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); animation: fadeInUp 0.8s ease 0.2s both; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .content-wrapper { padding: 60px 40px; max-width: 1400px; margin: 0 auto; width: 100%; flex: 1; display: flex; align-items: center; justify-content: center; }
    .login-container {
      background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(123,44,191,0.15);
      padding: 50px 45px; max-width: 480px; width: 100%; animation: slideUp 0.6s ease;
    }
    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .login-header { text-align: center; margin-bottom: 35px; }
    .login-icon { font-size: 60px; margin-bottom: 20px; animation: bounce 2s infinite; }
    @keyframes bounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    .login-header h2 { font-size: 28px; font-weight: 700; color: var(--primary-purple); margin-bottom: 8px; }
    .login-header p { font-size: 15px; color: #666; font-weight: 400; }
    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-size: 15px; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
    .form-group input {
      width: 100%; padding: 14px 18px; font-size: 15px; border: 2px solid #E0E0E0; border-radius: 10px;
      transition: all 0.3s ease; background-color: #FAFAFA;
    }
    .form-group input:focus { outline: none; border-color: var(--primary-purple); background-color: white; box-shadow: 0 0 0 3px rgba(123,44,191,0.1); }
    .form-group input::placeholder { color: #AAAAAA; }
    .btn-login {
      width: 100%; padding: 15px; background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer;
      transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(123,44,191,0.3); margin-bottom: 20px;
    }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(123,44,191,0.4); }
    .btn-login:active { transform: translateY(0); }
    .divider { text-align: center; margin: 25px 0; position: relative; }
    .divider::before { content: ''; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background-color: #E0E0E0; }
    .divider span { background-color: white; padding: 0 15px; color: #999; font-size: 14px; position: relative; z-index: 1; }
    .signup-link { text-align: center; font-size: 15px; color: #666; }
    .signup-link a { color: var(--primary-purple); text-decoration: none; font-weight: 600; transition: color 0.3s ease; }
    .signup-link a:hover { color: var(--accent-purple); text-decoration: underline; }
    .alert-message {
      padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; font-size: 14px;
    }
    .alert-error { background-color: #FEE; color: #d93025; border: 1px solid #FCC; }
    .alert-success { background-color: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
    footer {
      margin-top: auto; background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      padding: 30px 40px; text-align: center; color: white; box-shadow: 0 -4px 12px rgba(123,44,191,0.3);
    }
    .footer-content { max-width: 1400px; margin: 0 auto; }
    .footer-brand { font-size: 18px; font-weight: 700; color: var(--secondary-gold); margin-bottom: 12px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
    .footer-text { font-size: 14px; font-weight: 400; color: rgba(255,255,255,0.9); line-height: 1.6; }
    @media (max-width: 1024px) { header { padding: 16px 24px; } nav { gap: 20px; } nav a { font-size: 14px; } .banner h2 { font-size: 2.2rem; } .content-wrapper { padding: 40px 24px; } .login-container { padding: 40px 35px; } }
    @media (max-width: 768px) { header { flex-direction: column; min-height: auto; padding: 16px; gap: 16px; } .logo-container { min-width: auto; } header h1 { font-size: 1.5rem; } nav { flex-wrap: wrap; justify-content: center; gap: 12px; } nav a { font-size: 13px; padding: 6px 10px; } .banner { min-height: 240px; padding: 30px 16px; } .banner h2 { font-size: 1.8rem; } .banner p { font-size: 1rem; } .content-wrapper { padding: 30px 16px; } .login-container { padding: 35px 25px; } .login-header h2 { font-size: 24px; } .login-icon { font-size: 50px; } footer { padding: 24px 16px; } }
    @media (max-width: 480px) { .login-container { padding: 30px 20px; } .form-group input { padding: 12px 15px; } .btn-login { padding: 13px; } }
  </style>
</head>
<body>

<header>
  <div class="logo-container">
    <img src="gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
    <h1>Zarali's Catering</h1>
  </div>
  <nav>
    <a href="index.html">Home</a>
    <a href="menu.php">Menu</a>
    <a href="users/testi.php">Testimoni</a>
    <a href="users/pesanan.php">Pesanan saya</a>
    <a href="users/contact.html">Hubungi kami</a>
    <a href="about.html">Tentang kami</a>
    <a href="login.php">Login</a>
  </nav>
</header>

<div class="banner">
  <div class="banner-content">
    <h2>Daftar Akun Baru</h2>
    <p>Buat akun untuk mulai memesan layanan catering kami</p>
  </div>
</div>

<div class="content-wrapper">
  <div class="login-container">
    <div class="login-header">
      <div class="login-icon">📝</div>
      <h2>Daftar Akun</h2>
      <p>Isi formulir di bawah untuk membuat akun</p>
    </div>

    <!-- menampilkan pesan error jika ada -->
    <?php if ($error): ?>
      <div class="alert-message alert-error">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- menampilkan pesan success jika ada -->
    <?php if ($success): ?>
      <div class="alert-message alert-success">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password Anda" required>
      </div>

      <button type="submit" name="register" class="btn-login">Daftar Sekarang</button>
    </form>

    <div class="divider">
      <span>atau</span>
    </div>

    <div class="signup-link">
      Sudah punya akun? <a href="login.php">Login di sini</a>
    </div>
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