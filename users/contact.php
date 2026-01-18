<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hubungi Kami - Zarali's Catering</title>
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
      font-size: 1.9rem;
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
      min-height: 280px;
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
       FORM CONTACT (ENHANCED)
    ===================================== */
    .form-wrapper {
      max-width: 800px;
      width: 90%;
      margin: 60px auto;
      background: white;
      padding: 50px 40px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(123, 44, 191, 0.15);
      border: 2px solid rgba(123, 44, 191, 0.1);
      animation: fadeInScale 0.8s ease;
    }

    @keyframes fadeInScale {
      from {
        opacity: 0;
        transform: scale(0.95);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .form-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .form-header h3 {
      font-size: 28px;
      color: var(--primary-purple);
      margin-bottom: 10px;
      font-weight: 700;
    }

    .form-header p {
      color: #666;
      font-size: 15px;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
      color: var(--text-dark);
      font-size: 15px;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 14px 16px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--accent-purple);
      box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
      color: #999;
    }

    .form-group textarea {
      height: 140px;
      resize: vertical;
      min-height: 100px;
      max-height: 300px;
    }

    button {
      background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
      color: white;
      padding: 16px 50px;
      border: none;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      display: block;
      margin: 30px auto 0;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(123, 44, 191, 0.3);
      letter-spacing: 0.5px;
    }

    button:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(123, 44, 191, 0.4);
    }

    button:active {
      transform: translateY(-1px);
    }

    /* =====================================
       CONTACT INFO (ENHANCED WITH WHATSAPP)
    ===================================== */
    .contact-info {
      max-width: 1000px;
      width: 90%;
      margin: 60px auto;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
    }

    .info-card {
      background: white;
      padding: 35px 25px;
      border-radius: 20px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      text-align: center;
      transition: all 0.4s ease;
      border: 2px solid transparent;
      position: relative;
      overflow: hidden;
      text-decoration: none;
      color: inherit;
      display: block;
    }

    .info-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(123, 44, 191, 0.05), transparent);
      opacity: 0;
      transition: opacity 0.4s ease;
    }

    .info-card:hover::before {
      opacity: 1;
    }

    .info-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 35px rgba(123, 44, 191, 0.2);
      border-color: var(--accent-purple);
    }

    .info-icon {
      font-size: 48px;
      margin-bottom: 18px;
      transition: transform 0.3s ease;
    }

    .info-card:hover .info-icon {
      transform: scale(1.15);
    }

    .info-card h4 {
      font-size: 20px;
      color: var(--primary-purple);
      margin-bottom: 12px;
      font-weight: 700;
      position: relative;
      z-index: 2;
    }

    .info-card p {
      color: #666;
      font-size: 14px;
      line-height: 1.8;
      position: relative;
      z-index: 2;
    }

    /* WhatsApp Card Special Styling - Full Width */
    .whatsapp-card {
      grid-column: 1 / -1;
      background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
      border: 3px solid #25D366;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 40px;
      padding: 40px 50px;
    }

    .whatsapp-card::before {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
    }

    .whatsapp-card:hover {
      transform: translateY(-10px) scale(1.01);
      box-shadow: 0 15px 45px rgba(37, 211, 102, 0.4);
      border-color: #128C7E;
    }

    .whatsapp-content {
      display: flex;
      align-items: center;
      gap: 30px;
    }

    .whatsapp-card .info-icon {
      font-size: 64px;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
      margin-bottom: 0;
    }

    .whatsapp-text {
      text-align: left;
    }

    .whatsapp-card h4 {
      color: white;
      font-size: 28px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
      margin-bottom: 8px;
    }

    .whatsapp-card p {
      color: rgba(255, 255, 255, 0.95);
      font-weight: 500;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
      font-size: 15px;
      margin-bottom: 0;
    }

    .whatsapp-badge {
      padding: 12px 30px;
      background: rgba(255, 255, 255, 0.25);
      border-radius: 25px;
      color: white;
      font-size: 15px;
      font-weight: 700;
      display: inline-block;
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s ease;
      position: relative;
      z-index: 2;
      white-space: nowrap;
    }

    .whatsapp-card:hover .whatsapp-badge {
      background: white;
      color: #25D366;
      transform: scale(1.05);
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

      .form-wrapper {
        padding: 40px 30px;
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
        min-height: 240px;
        padding: 30px 16px;
      }

      .banner h2 {
        font-size: 1.8rem;
      }

      .banner p {
        font-size: 1rem;
      }

      .form-wrapper {
        width: 95%;
        padding: 30px 20px;
        margin: 40px auto;
      }

      .form-header h3 {
        font-size: 24px;
      }

      .contact-info {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .info-card {
        padding: 30px 20px;
      }

      .whatsapp-card {
        flex-direction: column;
        gap: 20px;
        padding: 35px 25px;
      }

      .whatsapp-content {
        flex-direction: column;
        gap: 15px;
        text-align: center;
      }

      .whatsapp-text {
        text-align: center;
      }

      .whatsapp-card h4 {
        font-size: 22px;
      }

      .whatsapp-card p {
        font-size: 14px;
      }

      .whatsapp-badge {
        width: 100%;
        text-align: center;
      }

      button {
        padding: 14px 40px;
        font-size: 15px;
      }

      footer {
        padding: 24px 16px;
      }
    }

    @media (max-width: 480px) {
      .form-wrapper {
        padding: 25px 16px;
      }

      .form-header h3 {
        font-size: 22px;
      }

      .form-group label {
        font-size: 14px;
      }

      .form-group input,
      .form-group textarea {
        padding: 12px 14px;
        font-size: 14px;
      }

      button {
        width: 100%;
        padding: 14px 20px;
      }
    }

  </style>

</head>
<body>

<header>
  <div class="logo-container">
    <img src="../gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
    <h1>Zarali's Catering</h1>
  </div>

  <nav>
    <a href="../index.php">Home</a>
    <a href="../menu.php">Menu</a>
    <a href="testi.php">Testimoni</a>
    <a href="pesanan.php">Pesanan saya</a>
    <a href="contact.php" class="active">Hubungi kami</a>
    <a href="../about.php">Tentang kami</a>
    <a href="../login.php">Login</a>
  </nav>
</header>

<div class="banner">
  <div class="banner-content">
    <h2>Hubungi Kami</h2>
    <p>Kami siap membantu mewujudkan acara istimewa Anda</p>
  </div>
</div>

<div class="contact-info">
  <div class="info-card">
    <div class="info-icon">📞</div>
    <h4>Telepon</h4>
    <p>+62 813-9883-8812<br>Senin - Sabtu: 08:00 - 20:00</p>
  </div>
  
  <div class="info-card">
    <div class="info-icon">📧</div>
    <h4>Email</h4>
    <p>info@zaraliscatering.com<br>Respons dalam 24 jam</p>
  </div>
  
  <div class="info-card">
    <div class="info-icon">📍</div>
    <h4>Lokasi</h4>
    <p>Depok, Kelapa 2<br>Melayani wilayah Depok dan sekitarnya</p>
  </div>

  <a href="https://wa.me/6281398838812?text=Halo%20Zarali's%20Catering,%20saya%20ingin%20bertanya%20tentang%20layanan%20catering%20Anda" target="_blank" class="info-card whatsapp-card">
    <div class="whatsapp-content">
      <div class="info-icon">💬</div>
      <div class="whatsapp-text">
        <h4>Hubungi Kami via WhatsApp</h4>
        <p>Chat langsung dengan kami untuk konsultasi dan pemesanan • Respon cepat & mudah</p>
      </div>
    </div>
    <div class="whatsapp-badge">Klik untuk chat →</div>
  </a>
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