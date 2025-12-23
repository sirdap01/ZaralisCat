<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home - Zarali's Catering</title>
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
      flex-direction: column;
    }

    /* =====================================
       HEADER (KONSISTEN)
    ===================================== */
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

    .logo-container {
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 280px;
    }

    .logo {
      max-height: 55px;
      border-radius: 50%;
      border: 3px solid var(--secondary-gold);
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    header h1 {
      font-weight: 700;
      font-size: 1.2rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
      letter-spacing: 0.5px;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 30px;
    }

    nav a {
      color: white;
      text-decoration: none;
      font-weight: 600;
      font-size: 15px;
      transition: all 0.3s ease;
      padding: 8px 12px;
      border-radius: 6px;
      position: relative;
    }

    nav a:hover {
      color: var(--secondary-gold);
      background-color: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px);
    }

    nav a.active {
      background-color: rgba(255, 215, 0, 0.2);
      color: var(--secondary-gold);
    }

    /* =====================================
       BANNER (KONSISTEN)
    ===================================== */
    .banner {
      background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
      min-height: 320px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
      padding: 40px 20px;
      position: relative;
      overflow: hidden;
    }

    .banner::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 50%, rgba(255, 215, 0, 0.1), transparent 50%);
      pointer-events: none;
    }

    .banner-content {
      position: relative;
      z-index: 2;
      text-align: center;
      max-width: 900px;
    }

    .banner h2 {
      font-size: 2.8rem;
      font-weight: 700;
      color: var(--secondary-gold);
      text-shadow: 3px 3px 8px rgba(0,0,0,0.4);
      margin-bottom: 16px;
      letter-spacing: 1px;
      animation: fadeInDown 0.8s ease;
    }

    .banner p {
      font-size: 1.2rem;
      font-weight: 300;
      color: white;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
      animation: fadeInUp 0.8s ease 0.2s both;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* =====================================
       KATEGORI SECTION
    ===================================== */
    .kategori {
      display: flex;
      flex-direction: column;
      gap: 35px;
      padding: 60px 40px;
      max-width: 1200px;
      margin: 0 auto;
      width: 100%;
    }

    .card {
      display: flex;
      align-items: center;
      background-color: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
      overflow: hidden;
      transition: all 0.4s ease;
      cursor: pointer;
      height: 240px;
      text-decoration: none;
      border: 3px solid transparent;
      position: relative;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), transparent);
      opacity: 0;
      transition: opacity 0.4s ease;
      pointer-events: none;
    }

    .card:hover::before {
      opacity: 1;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 40px rgba(123, 44, 191, 0.25);
      border-color: var(--accent-purple);
    }

    .card-image-container {
      width: 340px;
      height: 240px;
      overflow: hidden;
      flex-shrink: 0;
      position: relative;
    }

    .card-image-container::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(to right, transparent, rgba(255,255,255,0.1));
      pointer-events: none;
    }

    .card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .card:hover img {
      transform: scale(1.1);
    }

    .card-content {
      flex: 1;
      padding: 30px 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card h3 {
      font-size: 32px;
      color: var(--primary-purple);
      font-weight: 700;
      text-align: center;
      transition: all 0.3s ease;
      position: relative;
      display: inline-block;
    }

    .card h3::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 50%;
      transform: translateX(-50%) scaleX(0);
      width: 80%;
      height: 3px;
      background-color: var(--secondary-gold);
      transition: transform 0.3s ease;
    }

    .card:hover h3 {
      color: var(--accent-purple);
    }

    .card:hover h3::after {
      transform: translateX(-50%) scaleX(1);
    }

    /* =====================================
       FOOTER (KONSISTEN)
    ===================================== */
    footer {
      margin-top: auto;
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      padding: 30px 40px;
      text-align: center;
      color: white;
      box-shadow: 0 -4px 12px rgba(123, 44, 191, 0.3);
    }

    .footer-content {
      max-width: 1400px;
      margin: 0 auto;
    }

    .footer-brand {
      font-size: 18px;
      font-weight: 700;
      color: var(--secondary-gold);
      margin-bottom: 12px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .footer-text {
      font-size: 14px;
      font-weight: 400;
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.6;
    }

    /* =====================================
       RESPONSIVE DESIGN
    ===================================== */
    @media (max-width: 1024px) {
      header {
        padding: 16px 24px;
      }

      nav {
        gap: 20px;
      }

      nav a {
        font-size: 14px;
      }

      .banner h2 {
        font-size: 2.2rem;
      }

      .kategori {
        padding: 40px 24px;
      }

      .card {
        height: 200px;
      }

      .card-image-container {
        width: 280px;
        height: 200px;
      }

      .card h3 {
        font-size: 26px;
      }
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        min-height: auto;
        padding: 16px;
        gap: 16px;
      }

      .logo-container {
        min-width: auto;
      }

      header h1 {
        font-size: 1.5rem;
      }

      nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
      }

      nav a {
        font-size: 13px;
        padding: 6px 10px;
      }

      .banner {
        min-height: 280px;
        padding: 30px 16px;
      }

      .banner h2 {
        font-size: 1.8rem;
      }

      .banner p {
        font-size: 1rem;
      }

      .kategori {
        padding: 30px 16px;
        gap: 25px;
      }

      .card {
        flex-direction: column;
        height: auto;
      }

      .card-image-container {
        width: 100%;
        height: 200px;
      }

      .card-content {
        padding: 25px 20px;
      }

      .card h3 {
        font-size: 24px;
      }

      footer {
        padding: 24px 16px;
      }
    }

  </style>
</head>

<body>

<header>
  <div class="logo-container">
    <img src="gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
    <h1>Zarali's Catering</h1>
  </div>

  <nav>
    <a href="index.php" class="active">Home</a>
    <a href="menu.html">Menu</a>
    <a href="users/testi.html">Testimoni</a>
    <a href="users/pesanan.html">Pesanan saya</a>
    <a href="users/contact.html">Hubungi kami</a>
    <a href="about.html">Tentang kami</a>
    <a href="login.php">Login</a>
  </nav>
</header>

<div class="banner">
  <div class="banner-content">
    <h2>Selamat Datang di Zarali's Catering</h2>
    <p>Pilih menu favorit Anda berdasarkan kategori di bawah</p>
  </div>
</div>

<section class="kategori">
  <a href="menu.php?kategori=paket_besar" class="card">
    <div class="card-image-container">
      <img src="gambar/paket_besar.jpg" alt="Paket Besar">
    </div>
    <div class="card-content">
      <h3>Paket Besar</h3>
    </div>
  </a>

  <a href="menu.php?kategori=kue_satuan" class="card">
    <div class="card-image-container">
      <img src="gambar/kue_satuan.jpg" alt="Kue Satuan">
    </div>
    <div class="card-content">
      <h3>Kue Satuan</h3>
    </div>
  </a>

  <a href="menu.php?kategori=minuman" class="card">
    <div class="card-image-container">
      <img src="gambar/minuman.jpg" alt="Minuman">
    </div>
    <div class="card-content">
      <h3>Minuman</h3>
    </div>
  </a>
</section>

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