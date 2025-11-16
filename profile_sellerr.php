<?php
session_start();

// DEBUG - hapus setelah selesai
//echo "Session Username: " . ($_SESSION['username'] ?? 'tidak ada') . "<br>";
//echo "Session Role: " . ($_SESSION['role'] ?? 'tidak ada') . "<br>";
//exit(); // hapus setelah cek

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Cek apakah user adalah buyer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
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

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Ambil produk yang dijual seller
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.seller_id = ? 
        ORDER BY p.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute([$user['id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = [];
}

// Ambil pesanan untuk produk seller
try {
    $stmt = $pdo->prepare("
        SELECT o.*, p.name as product_name, u.name as buyer_name, u.username as buyer_username
        FROM orders o 
        JOIN products p ON o.product_id = p.id
        JOIN users u ON o.buyer_id = u.id
        WHERE o.seller_id = ? 
        ORDER BY o.order_date DESC 
        LIMIT 6
    ");
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Seller - SISKATRA</title>
    <link rel="website icon" type="png" 
    href="assets/logo_siskatrabaru.png">
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        /* Header dengan pattern keranjang */
        .header {
            position: relative;
            width: 100%;
            height: 200px; /* tinggi banner bisa disesuaikan */
            overflow: hidden;
            border-bottom: 3px solid #eee;
        }

        .banner-profile {
            width: 100%;
            height: 100%;
            object-fit: cover;  /* biar gambar nutup penuh */
            display: block;
        }

        .profile-label {
            position: absolute;
            bottom: 10px;
            left: 20px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #333;
            font-size: 20px;
            font-weight: bold;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Pattern background dengan keranjang belanja */
        .header::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                url('assets/keranjang.png'), 
                url('assets/keranjang.png'),
                url('assets/keranjang.png'),
                url('assets/keranjang.png');
            background-size: 80px 80px;
            background-position: 
                100px 20px, 
                300px 80px, 
                500px 40px, 
                700px 100px;
            background-repeat: no-repeat;
            opacity: 0.3;
        }

        /* Back button */
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #333;
            font-size: 20px;
            font-weight: bold;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Logo di header */
        .logo {
            height: 80px;       /* atur tinggi logo */
            width: auto;        /* biar proporsional */
            display: block;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .logo {
                height: 60px;   /* kecilin kalau layar HP */
            }
        }

        .siskatra-text {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .siskatra-logo {
            color: #FFD43B;
            font-size: 48px;
            font-family: 'Lilita One', cursive;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 5px;
        }

        .subtitle {
            color: #FFD43B;
            font-size: 14px;
            opacity: 0.9;
        }

        .profile-label {
            position: absolute;
            top: 10px;
            left: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            font-weight: normal;
        }

        /* Main content area */
        .main-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Profile section */
        .profile-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 40px;
        }

        .profile-header {
            padding: 30px;
            border-bottom: 2px solid #eee;
        }

        .profile-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 30px;
            font-family: 'Lilita One', cursive;
            font-weight: normal;
        }

        .profile-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .user-details p {
            font-size: 16px;
            color: #666;
        }

        .menu-btn {
            width: 45px;
            height: 45px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: #333;
        }

        /* Section headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 40px 0 25px 0;
            padding: 0 10px;
        }

        .section-title {
            font-size: 28px;
            color: #333;
            font-weight: bold;
        }

        .expand-btn {
            width: 40px;
            height: 40px;
            background: #284E7F;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
        }

        /* Products grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        /* Product card */
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            position: relative;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .product-image {
            width: 100%;
            height: 120px;
            background: #D9D9D9;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #666;
        }

        .product-info h4 {
            font-size: 18px;
            color: #333;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .product-price {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #BEBEBE;
            cursor: pointer;
            transition: background 0.2s;
            display: block;
            margin-bottom: 8px;
        }

        .btn-primary {
            background: #284E7F;
            color: white;
            border-color: #284E7F;
        }

        .btn-primary:hover {
            background: #1e3a5f;
        }

        /* Edit button */
        .edit-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 35px;
            height: 35px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #666;
            border: 1px solid #ddd;
        }

        .edit-btn:hover {
            background: #f8f9fa;
        }

        /* Orders section */
        .orders-section {
            margin-top: 40px;
        }

        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 0 10px;
        }

        .share-btn {
            width: 40px;
            height: 40px;
            background: #284E7F;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .siskatra-logo {
                font-size: 36px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
    <img src="assets/banner_profile.png" alt="Profile Banner" class="banner-profile"> 
    <a href="dashboard.php" class="back-btn">←</a>
    </div>

    <!-- 🔹 TOMBOL LOGOUT (Buyer) -->
<div style="text-align: right; padding: 0 20px; margin-bottom: 15px;">
  <a href="logout.php" 
     style="background: #0046ad; color: white; border-radius: 8px; padding: 8px 16px; font-size: 14px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
    <span>🚪</span> Keluar dari Akun
  </a>
</div>

    <div class="main-content">
        <!-- Profile Section -->
        <div class="profile-section">
            <div class="profile-header">
                <h2 class="profile-title">PROFILE</h2>
                
                <div class="profile-info">
                    <div class="user-info">
                        <div class="user-avatar">
                            <img src="assets/profile.png" alt="Profile">
                            </div>
                            <div class="user-details">
                            <h3>NAMA AKUN</h3>
                            <p>Seller</p>
                        </div>
                    </img>
                    
                    <button class="menu-btn">☰</button>
                </div>
            </div>
        </div>

        <!-- Produk Anda Section -->
        <div class="section-header">
            <h3 class="section-title">Produk Anda</h3>
            <a href="#" class="expand-btn">V</a>
        </div>

        <div class="products-grid">
            <?php if (empty($products)): ?>
                <!-- Empty Product Cards -->
                <div class="product-card">
                    <div class="product-image">📦</div>
                    <div class="product-info">
                        <h4>Nama Produk</h4>
                        <p class="product-price">Kategori</p>
                    </div>
                    <a href="#" class="btn btn-primary">Lihat Pesanan</a>
                    <a href="#" class="edit-btn">✏️</a>
                </div>

                <div class="product-card">
                    <div class="product-image">📦</div>
                    <div class="product-info">
                        <h4>Nama Produk</h4>
                        <p class="product-price">Kategori</p>
                    </div>
                    <a href="#" class="btn btn-primary">Lihat Pesanan</a>
                    <a href="#" class="edit-btn">✏️</a>
                </div>

                <div class="product-card">
                    <div class="product-image">📦</div>
                    <div class="product-info">
                        <h4>Nama Produk</h4>
                        <p class="product-price">Kategori</p>
                    </div>
                    <a href="#" class="btn btn-primary">Lihat Pesanan</a>
                    <a href="#" class="edit-btn">✏️</a>
                </div>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['image_url'] && file_exists('assets/' . $product['image_url'])): ?>
                                <img src="assets/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p class="product-price"><?php echo htmlspecialchars($product['category_name'] ?? 'Kategori'); ?></p>
                            <p class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        </div>
                        <a href="view_product_orders.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Lihat Pesanan</a>
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="edit-btn">✏️</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- List Pesanan Section -->
        <div class="orders-section">
            <div class="orders-header">
                <h3 class="section-title">List Pesanan</h3>
                <a href="#" class="share-btn">↗</a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <p>Belum ada pesanan masuk</p>
                    <p style="font-size: 14px; color: #999;">Pesanan untuk produk Anda akan muncul di sini</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach($orders as $order): ?>
                        <div class="product-card">
                            <div class="product-image">📋</div>
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                                <p class="product-price">Buyer: <?php echo htmlspecialchars($order['buyer_username']); ?></p>
                                <p class="product-price">Status: <?php echo ucfirst($order['status']); ?></p>
                                <p class="product-price">Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></p>
                            </div>
                            <a href="view_order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">Lihat Detail</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Menu button functionality
        document.querySelector('.menu-btn').addEventListener('click', function() {
            alert('Menu akan ditambahkan: \n- Edit Profile\n- Tambah Produk\n- Pengaturan\n- Logout');
        });

        // Expand button functionality
        document.querySelectorAll('.expand-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Fitur expand akan menampilkan semua item');
            });
        });
    </script>
    <!-- ====== PROFIL USER ====== -->
<div class="user-profile">
  <div class="user-info">
    <span class="username-display">ZahraNurulA</span>
    <span class="menu-icon" onclick="toggleBiodata()">&#9776;</span>
  </div>

  <!-- Biodata tersembunyi -->
  <div id="biodata-box" class="biodata">
    <p><strong>Username:</strong> ZahraNurulA</p>
    <p><strong>No HP:</strong> 081234567890</p>
    <div class="biodata-actions">
      <button onclick="ubahData()">Ubah Data</button>
      <button onclick="simpanData()">Simpan</button>
      <button onclick="batalUbah()">Tidak</button>
    </div>
  </div>
</div>

<!-- ====== CSS TAMBAHAN ====== -->
<style>
.user-profile {
  position: relative;
  display: inline-block;
  text-align: left;
  font-family: 'Montserrat', sans-serif;
  color: white;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.menu-icon {
  font-size: 20px;
  cursor: pointer;
  transition: 0.3s;
}

.menu-icon:hover {
  color: #ccc;
}

.biodata {
  display: none;
  background: rgba(255,255,255,0.1);
  border-radius: 10px;
  padding: 10px;
  margin-top: 8px;
  font-size: 14px;
  animation: slideDown 0.3s ease;
}

.biodata p {
  margin: 4px 0;
}

.biodata-actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.biodata-actions button {
  background-color: #fff;
  border: none;
  border-radius: 5px;
  padding: 5px 10px;
  cursor: pointer;
  font-weight: bold;
}

.biodata-actions button:hover {
  background-color: #ddd;
}

@keyframes slideDown {
  from {opacity: 0; transform: translateY(-10px);}
  to {opacity: 1; transform: translateY(0);}
}
</style>

<!-- ====== JAVASCRIPT TAMBAHAN ====== -->
<script>
function toggleBiodata() {
  const box = document.getElementById('biodata-box');
  box.style.display = (box.style.display === 'block') ? 'none' : 'block';
}

function ubahData() {
  alert("Mode ubah data diaktifkan (contoh saja dulu)");
}

function simpanData() {
  alert("Data berhasil disimpan (contoh saja dulu)");
}

function batalUbah() {
  document.getElementById('biodata-box').style.display = 'none';
}
</script>

</body>
</html>