<?php
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php");
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];

// Get cart items with product details
$query_cart = mysqli_query($koneksi, "
    SELECT 
        k.id_keranjang,
        k.id_produk,
        k.jumlah,
        p.nama,
        p.deskripsi,
        p.harga,
        p.kategori,
        p.gambar,
        (k.jumlah * p.harga) as subtotal
    FROM keranjang k
    JOIN produk p ON k.id_produk = p.id
    WHERE k.id_pengguna = $id_pengguna
    ORDER BY k.created_at DESC
");

// Calculate total
$total_harga = 0;
$total_items = 0;
$cart_items = [];

while ($item = mysqli_fetch_assoc($query_cart)) {
    $cart_items[] = $item;
    $total_harga += $item['subtotal'];
    $total_items += $item['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keranjang Belanja - Zarali's Catering</title>
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

/* ===== HEADER ===== */
header {
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    padding: 16px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 85px;
    box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
    position: sticky;
    top: 0;
    z-index: 100;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 14px;
}

.logo {
    max-height: 55px;
    border-radius: 50%;
    border: 3px solid var(--secondary-gold);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

header h1 {
    font-weight: 700;
    font-size: 1.2rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
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

/* ===== BANNER ===== */
.banner {
    background: linear-gradient(135deg, var(--accent-purple), var(--primary-purple));
    padding: 40px 40px;
    color: white;
    text-align: center;
}

.banner h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--secondary-gold);
    text-shadow: 3px 3px 8px rgba(0,0,0,0.4);
    margin-bottom: 10px;
}

.banner p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
}

/* ===== MAIN CONTENT ===== */
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 40px;
    flex: 1;
}

.breadcrumb {
    margin-bottom: 30px;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 600;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
    align-items: start;
}

/* ===== CART ITEMS ===== */
.cart-items {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F0F0F0;
}

.cart-header h3 {
    font-size: 24px;
    color: var(--primary-purple);
    display: flex;
    align-items: center;
    gap: 10px;
}

.item-count {
    background: linear-gradient(135deg, var(--secondary-gold), #FFC700);
    color: var(--primary-purple);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
}

.btn-clear-all {
    background: #F44336;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-clear-all:hover {
    background: #D32F2F;
    transform: translateY(-2px);
}

/* ===== CART ITEM CARD ===== */
.cart-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: #F9F9F9;
    border-radius: 15px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.cart-item:hover {
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.1);
}

.item-image {
    width: 120px;
    height: 120px;
    border-radius: 12px;
    object-fit: cover;
    flex-shrink: 0;
}

.item-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.item-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 4px;
}

.item-category {
    display: inline-block;
    padding: 4px 12px;
    background: white;
    color: #666;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    width: fit-content;
}

.item-price {
    font-size: 20px;
    font-weight: 700;
    color: var(--accent-purple);
    margin-top: auto;
}

.item-actions {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-end;
}

.btn-remove {
    background: none;
    border: none;
    color: #F44336;
    font-size: 24px;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    background: #FFEBEE;
    transform: rotate(90deg);
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 8px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.btn-qty {
    width: 32px;
    height: 32px;
    border: none;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-qty:hover {
    transform: scale(1.1);
}

.btn-qty:disabled {
    background: #BDBDBD;
    cursor: not-allowed;
}

.qty-display {
    width: 40px;
    text-align: center;
    font-weight: 700;
    color: var(--primary-purple);
    font-size: 16px;
}

.item-subtotal {
    font-size: 16px;
    font-weight: 600;
    color: #666;
    margin-top: 8px;
}

/* ===== CART SUMMARY ===== */
.cart-summary {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
    position: sticky;
    top: 120px;
}

.summary-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F0F0F0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 15px;
}

.summary-row.total {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-purple);
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #F0F0F0;
}

.summary-row .label {
    color: #666;
}

.summary-row .value {
    font-weight: 700;
    color: var(--text-dark);
}

.btn-checkout {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #00C853, #00E676);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 25px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 200, 83, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 200, 83, 0.4);
}

.btn-continue {
    width: 100%;
    padding: 14px;
    background: white;
    color: var(--primary-purple);
    border: 2px solid var(--primary-purple);
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 15px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-continue:hover {
    background: var(--primary-purple);
    color: white;
    transform: translateY(-2px);
}

/* ===== EMPTY CART ===== */
.empty-cart {
    text-align: center;
    padding: 80px 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(123, 44, 191, 0.12);
}

.empty-icon {
    font-size: 100px;
    margin-bottom: 25px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

.empty-cart h3 {
    font-size: 28px;
    color: var(--primary-purple);
    margin-bottom: 15px;
}

.empty-cart p {
    font-size: 16px;
    color: #666;
    margin-bottom: 30px;
}

.btn-shop {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 35px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(123, 44, 191, 0.3);
}

.btn-shop:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(123, 44, 191, 0.4);
}

/* ===== TOAST NOTIFICATION ===== */
.toast {
    position: fixed;
    top: 100px;
    right: 30px;
    background: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    display: none;
    align-items: center;
    gap: 15px;
    z-index: 10000;
    animation: slideInRight 0.4s ease;
    min-width: 320px;
}

.toast.show {
    display: flex;
}

.toast.success {
    border-left: 4px solid #00C853;
}

.toast.error {
    border-left: 4px solid #F44336;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-icon {
    font-size: 28px;
    flex-shrink: 0;
}

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 700;
    font-size: 15px;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.toast-message {
    font-size: 13px;
    color: #666;
}

.toast-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
}

.toast-close:hover {
    color: #333;
}

/* ===== FOOTER ===== */
footer {
    margin-top: auto;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    padding: 30px 40px;
    text-align: center;
    color: white;
    box-shadow: 0 -4px 12px rgba(123, 44, 191, 0.3);
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
    color: rgba(255, 255, 255, 0.9);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .cart-content {
        grid-template-columns: 1fr;
    }

    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    header {
        flex-direction: column;
        padding: 16px;
        gap: 16px;
    }

    nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
    }

    .cart-container {
        padding: 30px 16px;
    }

    .banner h2 {
        font-size: 1.8rem;
    }

    .cart-item {
        flex-direction: column;
    }

    .item-image {
        width: 100%;
        height: 200px;
    }

    .item-actions {
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
    }

    .toast {
        right: 16px;
        min-width: 280px;
    }
}

@media (max-width: 480px) {
    .cart-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

/* ===== FLOATING CART - BACK BUTTON STATE ===== */
.floating-cart.back-mode .cart-button {
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    border-color: white;
}

.floating-cart.back-mode .cart-button:hover {
    background: linear-gradient(135deg, #1976D2, #2196F3);
    transform: translateY(-5px) scale(1.05) rotate(-10deg);
}

.floating-cart.back-mode .cart-icon {
    animation: backArrow 1.5s infinite;
}

@keyframes backArrow {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(-5px); }
}

.back-tooltip {
    position: absolute;
    bottom: 80px;
    right: 0;
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4);
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s ease;
}

.back-tooltip::after {
    content: '';
    position: absolute;
    bottom: -8px;
    right: 25px;
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid #42A5F5;
}

.floating-cart.back-mode:hover .back-tooltip {
    opacity: 1;
    bottom: 85px;
}
/* ===== FLOATING CART - BACK MODE ===== */
.floating-cart {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 999;
}

.floating-cart.back-mode .cart-button {
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    border-color: white;
}

.floating-cart.back-mode .cart-button:hover {
    background: linear-gradient(135deg, #1976D2, #2196F3);
    transform: translateY(-5px) scale(1.05) rotate(-10deg);
    box-shadow: 0 12px 35px rgba(33, 150, 243, 0.5);
}

.cart-button {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(123, 44, 191, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid var(--secondary-gold);
    text-decoration: none;
    position: relative;
}

.cart-button:active {
    transform: translateY(-2px) scale(1.02);
}

.cart-icon {
    font-size: 36px;
    font-weight: bold;
    color: white;
    transition: all 0.3s ease;
}

.floating-cart.back-mode .cart-icon {
    animation: backArrow 1.5s infinite;
}

@keyframes backArrow {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(-5px); }
}

.cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: linear-gradient(135deg, #F44336, #E57373);
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    animation: pulse 2s infinite;
    border: 2px solid white;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

.cart-badge.empty {
    display: none;
}

.back-tooltip {
    position: absolute;
    bottom: 80px;
    right: 0;
    background: linear-gradient(135deg, #2196F3, #42A5F5);
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4);
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s ease;
}

.back-tooltip::after {
    content: '';
    position: absolute;
    bottom: -8px;
    right: 25px;
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid #42A5F5;
}

.floating-cart.back-mode:hover .back-tooltip {
    opacity: 1;
    bottom: 85px;
}

/* Floating effect on scroll */
.floating-cart.scrolled .cart-button {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* ===== RESPONSIVE FLOATING CART ===== */
@media (max-width: 1024px) {
    .floating-cart {
        bottom: 20px;
        right: 20px;
    }

    .cart-button {
        width: 60px;
        height: 60px;
    }

    .cart-icon {
        font-size: 30px;
    }
}

@media (max-width: 768px) {
    .floating-cart {
        bottom: 15px;
        right: 15px;
    }

    .cart-button {
        width: 55px;
        height: 55px;
    }

    .cart-icon {
        font-size: 26px;
    }

    .cart-badge {
        width: 24px;
        height: 24px;
        font-size: 11px;
    }

    .back-tooltip {
        font-size: 11px;
        padding: 8px 15px;
        bottom: 70px;
    }
}

</style>

<script>
// Update quantity
function updateQuantity(id_keranjang, action) {
    const formData = new FormData();
    formData.append('id_keranjang', id_keranjang);
    formData.append('action', action);
    
    fetch('proses_update_keranjang.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast('error', 'Gagal!', data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        showToast('error', 'Error!', 'Terjadi kesalahan koneksi');
        console.error('Error:', error);
    });
}

// Remove item from cart
function removeItem(id_keranjang, productName) {
    if (!confirm(`Hapus "${productName}" dari keranjang?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id_keranjang', id_keranjang);
    
    fetch('proses_hapus_keranjang.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Berhasil!', `${productName} dihapus dari keranjang`);
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', 'Gagal!', data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        showToast('error', 'Error!', 'Terjadi kesalahan koneksi');
        console.error('Error:', error);
    });
}

// Clear all cart items
function clearCart() {
    if (!confirm('Hapus semua item dari keranjang?')) {
        return;
    }
    
    fetch('proses_clear_keranjang.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Berhasil!', 'Keranjang telah dikosongkan');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', 'Gagal!', data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        showToast('error', 'Error!', 'Terjadi kesalahan koneksi');
        console.error('Error:', error);
    });
}

// Show toast notification
function showToast(type, title, message) {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    
    toast.className = `toast ${type}`;
    toastIcon.textContent = type === 'success' ? '✓' : '✕';
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Close toast
function closeToast() {
    document.getElementById('toast').classList.remove('show');
}
</script>

</head>

<body>

<!-- TOAST NOTIFICATION -->
<div id="toast" class="toast">
    <div id="toastIcon" class="toast-icon"></div>
    <div class="toast-content">
        <div id="toastTitle" class="toast-title"></div>
        <div id="toastMessage" class="toast-message"></div>
    </div>
    <button class="toast-close" onclick="closeToast()">×</button>
</div>

<header>
    <div class="logo-container">
        <img src="gambar/logo.png" alt="Logo Zarali's Catering" class="logo">
        <h1>Zarali's Catering</h1>
    </div>

    <nav>
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="users/testi.php">Testimoni</a>
        <a href="users/pesanan.php">Pesanan saya</a>
        <a href="users/contact.html">Hubungi kami</a>
        <a href="about.html">Tentang kami</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="banner">
    <h2>Keranjang Belanja</h2>
    <p>Review pesanan Anda sebelum checkout</p>
</div>

<div class="cart-container">
    <div class="breadcrumb">
        <a href="index.php">🏠 Home</a> › 
        <a href="menu.php">Menu</a> › 
        <span>Keranjang</span>
    </div>

    <?php if (count($cart_items) > 0): ?>
        <div class="cart-content">
            <!-- CART ITEMS -->
            <div class="cart-items">
                <div class="cart-header">
                    <h3>
                        <span>🛒</span>
                        <span>Item di Keranjang</span>
                        <span class="item-count"><?= $total_items ?></span>
                    </h3>
                    <button class="btn-clear-all" onclick="clearCart()">
                        🗑️ Kosongkan Keranjang
                    </button>
                </div>

                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="uploads/produk/<?= htmlspecialchars($item['gambar']) ?>" 
                         alt="<?= htmlspecialchars($item['nama']) ?>" 
                         class="item-image"
                         onerror="this.src='gambar/placeholder.jpg';">
                    
                    <div class="item-details">
                        <h4 class="item-name"><?= htmlspecialchars($item['nama']) ?></h4>
                        <span class="item-category"><?= htmlspecialchars($item['kategori']) ?></span>
                        <div class="item-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                        <div class="item-subtotal">
                            Subtotal: <strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
                        </div>
                    </div>

                    <div class="item-actions">
                        <button class="btn-remove" 
                                onclick="removeItem(<?= $item['id_keranjang'] ?>, '<?= htmlspecialchars($item['nama']) ?>')">
                            ×
                        </button>

                        <div class="quantity-control">
                            <button class="btn-qty" 
                                    onclick="updateQuantity(<?= $item['id_keranjang'] ?>, 'minus')"
                                    <?= $item['jumlah'] <= 1 ? 'disabled' : '' ?>>
                                −
                            </button>
                            <span class="qty-display"><?= $item['jumlah'] ?></span>
                            <button class="btn-qty" 
                                    onclick="updateQuantity(<?= $item['id_keranjang'] ?>, 'plus')">
                                +
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- CART SUMMARY -->
            <div class="cart-summary">
                <h3 class="summary-title">Ringkasan Belanja</h3>
                
                <div class="summary-row">
                    <span class="label">Total Item</span>
                    <span class="value"><?= $total_items ?> item</span>
                </div>

                <div class="summary-row total">
                    <span class="label">Total Harga</span>
                    <span class="value">Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
                </div>

                <a href="checkout.php" class="btn-checkout">
                    <span>✓</span>
                    <span>Lanjut ke Checkout</span>
                </a>

                <a href="menu.php" class="btn-continue">
                    <span>←</span>
                    <span>Lanjut Belanja</span>
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- EMPTY CART -->

        <div class="empty-cart">
            <div class="empty-icon">🛒</div>
            <h3>Keranjang Kosong</h3>
            <p>Anda belum menambahkan produk ke keranjang</p>
            <a href="menu.php" class="btn-shop">
                <span>🍽️</span>
                <span>Mulai Belanja</span>
            </a>
        </div>
    <?php endif; ?>
</div>

<footer>
    <div class="footer-brand">Zarali's Catering</div>
    <div class="footer-text">
        Kelompok 5 | Melayani dengan Hati untuk Setiap Acara Anda<br>
        © 2024 Zarali's Catering. All Rights Reserved.
    </div>
</footer>

<!-- FLOATING CART - SMART BACK BUTTON -->
<div class="floating-cart back-mode">
    <a href="#" onclick="smartBack(event)" class="cart-button" title="Kembali">
        <div class="cart-icon">←</div>
        <span class="cart-badge <?= $total_items == 0 ? 'empty' : '' ?>">
            <?= $total_items ?>
        </span>
    </a>
    <div class="back-tooltip">← Kembali Belanja</div>
</div>

<script>
function smartBack(event) {
    event.preventDefault();
    
    // Add scroll effect to floating cart
    window.addEventListener('scroll', function() {
        const floatingCart = document.querySelector('.floating-cart');
        if (floatingCart && window.scrollY > 100) {
            floatingCart.classList.add('scrolled');
        } else if (floatingCart) {
            floatingCart.classList.remove('scrolled');
        }
    });

    // Cek apakah ada history sebelumnya
    if (document.referrer && 
        document.referrer !== window.location.href && 
        !document.referrer.includes('login.php') &&
        !document.referrer.includes('register.php') &&
        !document.referrer.includes('keranjang.php')) {
        // Kembali ke halaman sebelumnya
        window.history.back();
    } else {
        // Default ke menu jika tidak ada history yang valid
        window.location.href = 'menu.php';
    }
}
</script>

</body>
</html>