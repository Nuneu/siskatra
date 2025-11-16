<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Cek apakah user adalah buyer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

// Konfigurasi database
$db_host = "localhost";
$db_name = "siskatra_db";
$db_user = "root";
$db_pass = "";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Ambil informasi user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kalau user tidak ditemukan
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Buyer - SISKATRA</title>
    <link rel="website icon" type="png" 
    href="assets/logo_siskatrabaru.png">
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lilita One', cursive;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #284e7f);
            padding: 20px;
            min-height: 180px;
            position: relative;
            color: white;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
        }

        .header-content {
            text-align: center;
            margin-top: 20px;
        }

        .siskatra-logo {
            color: #FFD43B;
            font-size: 48px;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #FFD43B;
            font-size: 14px;
        }

        .profile-label {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            margin-top: 20px;
            padding-left: 20px;
        }

        /* Profile */
        .profile-section {
            background: white;
            margin: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .profile-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .profile-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .profile-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: #FFD43B;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            color: white;
        }

        .user-details h3 {
            font-size: 18px;
            margin: 0 0 5px;
        }

        .user-details p {
            font-size: 12px;
            color: #666;
            font-family: Arial, sans-serif;
        }

        /* Products */
        .products-section {
            padding: 20px;
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .products-grid {
            display: grid;
            gap: 20px;
        }

        .product-card {
            width: 471px;
            height: 259px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .product-left {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .product-image {
            width: 150px;
            height: 120px;
            background: #e9ecef;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .product-info h4 {
            font-size: 15px;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            font-family: Arial, sans-serif;
        }

        .view-order-btn {
            background: #2c5aa0;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }

        .product-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .chat-admin {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            text-decoration: none;
            color: #333;
        }

        .whatsapp-icon {
            width: 20px;
            height: 20px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 12px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .action-btn {
            background: #2c5aa0;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 12px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <a href="dashboard.php" class="back-btn">←</a>
        <div class="header-content">
            <h1 class="siskatra-logo">SISKATRA 👀</h1>
            <p class="subtitle">Sistem Katalog Produk Kreatif STM</p>
        </div>
        <p class="profile-label">PROFILE BUYER</p>
    </div>

    <!-- Profile -->
    <div class="profile-section">
        <div class="profile-header">
            <h2 class="profile-title">PROFILE</h2>
            <div class="profile-info">
                <div class="user-info">
                    <div class="user-avatar">👤</div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p>Buyer</p>
                    </div>
                </div>
                <button class="menu-btn">☰</button>
            </div>
        </div>

        <!-- 🔹 TOMBOL LOGOUT (Buyer) -->
<div style="text-align: right; padding: 0 20px; margin-bottom: 15px;">
  <a href="logout.php" 
     style="background: #0046ad; color: white; border-radius: 8px; padding: 8px 16px; font-size: 14px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
    <span>🚪</span> Keluar dari Akun
  </a>
</div>

        <!-- Products -->
        <div class="products-section">
            <h3 class="section-title">Pesanan Anda</h3>
            <div class="products-grid">
                <!-- Card 1 -->
                <div class="product-card">
                    <div class="product-left">
                        <div class="product-image">📦</div>
                        <div class="product-info">
                            <h4>Nama Produk</h4>
                            <p class="product-price">Makanan</p>
                            <a href="#" class="view-order-btn">Lihat Pesanan</a>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="chat-admin">
                            <div class="whatsapp-icon">💬</div>
                            Chat Admin
                        </a>
                        <div class="action-buttons">
                            <button class="action-btn">Ubah Pesanan ?</button>
                            <button class="action-btn">Batalkan Pesanan ?</button>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="product-card">
                    <div class="product-left">
                        <div class="product-image">📦</div>
                        <div class="product-info">
                            <h4>Nama Produk</h4>
                            <p class="product-price">Minuman</p>
                            <a href="#" class="view-order-btn">Lihat Pesanan</a>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="chat-admin">
                            <div class="whatsapp-icon">💬</div>
                            Chat Admin
                        </a>
                        <div class="action-buttons">
                            <button class="action-btn">Ubah Pesanan ?</button>
                            <button class="action-btn">Batalkan Pesanan ?</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
