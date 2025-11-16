<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// 🔹 Query produk: join products + users + categories + product_images (gambar utama)
$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.product_name AS name,
        p.price,
        p.stock,
        p.description,
        COALESCE(c.category_name, 'Kategori Tidak Diketahui') AS category,
        pi.image_url AS image,
        u.username AS seller_name,
        u.phone AS seller_phone
    FROM products p
    INNER JOIN users u ON p.seller_id = u.id
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
    WHERE p.stock > 0
    ORDER BY p.created_at DESC
    LIMIT 12
");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - SISKATRA</title>
  <link rel="website icon" type="png" href="assets/logo_siskatrabaru.png">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- 🔹 TAMBAHKAN CRYPTO-JS UNTUK TOKEN AMAN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      margin: 0;
      background-color: #f2f2f2;
    }

    /* Header */
    .header {
      background: #ffffff;
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .header img.logo {
      height: 40px;
    }

    .header img.profile {
      background: #FFD43B;
      height: 35px;
      width: 35px;
      border-radius: 50%;
      cursor: pointer;
    }

    /* Banner */
    .banner {
      background: #0046ad;
      border-radius: 20px;
      margin: 30px auto;
      width: 90%;
      height: 180px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 22px;
      overflow: hidden;
    }

    .banner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 20px;
    }

    /* Search bar */
    .search-container {
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 20px auto;
      width: 80%;
      background: white;
      border-radius: 30px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      padding: 10px 15px;
    }

    .search-container img {
      height: 25px;
      margin-right: 10px;
    }

    .search-container input {
      border: none;
      outline: none;
      flex: 1;
      font-size: 16px;
    }

    .search-container button {
      background: #FFD43B;
      border: none;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: bold;
      cursor: pointer;
    }

    /* Konten */
    .content {
      padding: 20px;
      text-align: center;
    }

    h2 {color: #284e7f;}

    /* Wrapper Story + Kategori */
    .story-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      width: 90%;
      margin: 0 auto 40px auto;
      gap: 30px;
    }

    /* Story */
    .story-container {
      flex: 3;
      text-align: left;
    }

    .story-container h3 {
      font-size: 18px;
      margin-bottom: 10px;
      color: #333;
    }

    .story-list {
      display: flex;
      gap: 20px;
      overflow-x: auto;
      padding: 10px 0;
      scrollbar-width: none;
    }

    .story-list::-webkit-scrollbar {display: none;}

    .story-item {
      min-width: 140px;
      height: 180px;
      background-color: #e6e6e6;
      border-radius: 15px;
      flex-shrink: 0;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      transition: transform 0.3s;
      position: relative;
    }

    .story-item:hover {transform: scale(1.05);}

    .story-item img {
      width: 100%;
      height: 130px;
      object-fit: cover;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
    }

    .story-item p {
      margin: 8px 0;
      color: #333;
      font-size: 14px;
    }

    .story-badge {
      position: absolute;
      bottom: 8px;
      right: 8px;
      background: #0046ad;
      color: white;
      font-size: 10px;
      padding: 3px 6px;
      border-radius: 8px;
    }

    .story-add {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      border: 2px dashed #0046ad;
      color: #0046ad;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .story-add:hover {
      background: #0046ad;
      color: white;
    }

    .story-add span {
      font-size: 40px;
      line-height: 1;
      margin-bottom: 5px;
    }

    /* Kategori Produk */
    .kategori-produk {
      flex: 1.5;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 15px;
      text-align: center;
    }

    .kategori-produk h3 {
      font-size: 16px;
      margin-bottom: 15px;
      color: #333;
    }

    .kategori-list {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      justify-content: center;
    }

    .kategori-item {
      background-color: #f3f3f3;
      border-radius: 12px;
      padding: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .kategori-item:hover {
      background-color: #ffe680;
    }

    .kategori-icon {
      width: 40px;
      height: 40px;
      background-color: #ddd;
      border-radius: 8px;
      margin: 0 auto 5px;
    }

    .kategori-item p {
      margin: 0;
      font-size: 13px;
      font-weight: 600;
      color: #333;
    }

    /* Produk Terbaru */
    .produk-container {
      width: 90%;
      margin: 0 auto 60px auto;
      text-align: left;
    }

    .produk-title {
      font-size: 18px;
      color: #333;
      margin-bottom: 15px;
    }

    .produk-list {
      display: flex;
      gap: 25px;
      flex-wrap: wrap;
      justify-content: flex-start;
    }

    .produk-item {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      width: 180px;
      padding: 10px;
      text-align: center;
      transition: all 0.3s ease;
    }

    .produk-item:hover {transform: translateY(-5px);}

    .produk-img {
      width: 100%;
      height: 120px;
      background-color: #e6e6e6;
      border-radius: 10px;
      margin-bottom: 10px;
      background-size: cover;
      background-position: center;
    }

    .produk-item h4 {
      margin: 5px 0 2px 0;
      font-size: 15px;
      font-weight: 600;
      color: #333;
    }

    .produk-item p {
      font-size: 13px;
      color: #555;
      margin-bottom: 10px;
    }

    .produk-btn {
      background: #FFD43B;
      border: none;
      padding: 8px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .produk-btn:hover {background: #f7ca1e;}

    /* ===== POPUP DETAIL PRODUK ===== */
    .popup {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .popup-content {
      background: #fff;
      border-radius: 20px;
      width: 350px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      text-align: center;
      position: relative;
    }

    .popup-content img {
      width: 100%;
      height: 200px;
      background: #ddd;
      border-radius: 15px;
      margin-bottom: 15px;
      object-fit: cover;
    }

    .popup-content h4 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .popup-content p {
      font-size: 14px;
      color: #555;
      margin: 5px 0;
    }

    .popup-content textarea {
      width: 100%;
      height: 80px;
      border: none;
      background: #f6f6f6;
      border-radius: 10px;
      resize: none;
      padding: 10px;
      font-family: 'Montserrat', sans-serif;
      margin-bottom: 15px;
    }

    .wa-btn {
      display: block;
      width: 100%;
      background: #25D366;
      color: white;
      text-decoration: none;
      padding: 12px;
      border-radius: 8px;
      font-weight: bold;
      margin-top: 10px;
      text-align: center;
      transition: background 0.3s;
    }

    .wa-btn:hover {
      background: #1ebc59;
      transform: scale(1.02);
    }

    .close-popup {
      position: absolute;
      top: 10px;
      left: 15px;
      cursor: pointer;
      font-size: 20px;
    }

    /* Tombol Tambah Produk (Seller Only) */
    .tambah-produk-btn {
      display: inline-block;
      background: #FFD43B;
      color: #000;
      padding: 10px 25px;
      border-radius: 20px;
      font-weight: bold;
      text-decoration: none;
      margin: 20px auto;
      display: block;
      width: fit-content;
    }

    .tambah-produk-btn:hover {
      background: #f7ca1e;
    }

    /* ===== POPUP STORY ===== */
    .story-popup {
      display: none;
      justify-content: center;
      align-items: center;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: 999;
    }

    .story-popup-content {
      background: #d9d9d9;
      width: 360px;
      height: 600px;
      border-radius: 20px;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .story-header {
      display: flex;
      align-items: center;
      padding: 10px 15px;
    }

    .back-arrow {
      font-size: 20px;
      cursor: pointer;
      margin-right: 10px;
    }

    .story-profile {
      width: 25px;
      height: 25px;
      background: white;
      border-radius: 50%;
      margin-right: 10px;
    }

    .story-nama {
      font-weight: 600;
      font-size: 14px;
      color: #333;
    }

    .story-popup-content hr {
      margin: 0 15px;
      border: none;
      border-top: 2px solid rgba(255,255,255,0.4);
    }

    .story-body {
      flex: 1;
      margin: 10px;
      background: #d9d9d9;
      border-radius: 15px;
    }

    .story-wa {
      position: absolute;
      bottom: 10px;
      right: 10px;
    }

    .story-wa img {
      width: 28px;
      height: 28px;
    }

    /* Responsif versi kecil */
    @media (max-width: 480px) {
      .story-popup-content {
        width: 250px;
        height: 420px;
      }
      .story-wa img {
        width: 22px;
        height: 22px;
      }
    }

    /* ===== POPUP TAMBAH STORY ===== */
    #popupTambahStory {
      display: none;
      justify-content: center;
      align-items: center;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
    }

    .popup-tambah-story {
      background: white;
      width: 320px;
      padding: 25px;
      border-radius: 20px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.25);
      font-family: 'Montserrat', sans-serif;
    }

    .popup-tambah-story h3 {
      font-size: 16px;
      color: #333;
      margin-bottom: 20px;
    }

    .tambah-story-opsi {
      display: flex;
      justify-content: space-around;
    }

    .tambah-story-item {
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .tambah-story-item:hover {
      transform: scale(1.05);
    }

    .tambah-story-icon {
      width: 70px;
      height: 60px;
      background-color: #d9d9d9;
      border-radius: 10px;
      margin: 0 auto 8px auto;
    }

    .tambah-story-item p {
      font-weight: 600;
      font-size: 14px;
      color: #333;
    }

    /* Notifikasi sukses */
    .notif {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 25px;
      border-radius: 10px;
      font-family: 'Montserrat', sans-serif;
      font-size: 16px;
      color: white;
      background: #4caf50;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      z-index: 1000;
      animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* POPUP KONFIRMASI HAPUS */
    #confirmDeletePopup {
      display: none;
    }
  </style>
</head>

<body>
  <!-- Header -->
<div class="header">
  <img src="assets/logo_siskatra_text.gif" alt="SISKATRA" class="logo">

  <div class="header-right">
    <!-- Profil -->
    <?php if ($_SESSION['role'] === 'seller'): ?>
      <a href="./profile_sellerr.php">
        <img src="assets/profile.png" alt="Profile" class="profile">
      </a>
    <?php else: ?>
      <a href="./profile_buyyer.php">
        <img src="assets/profile.png" alt="Profile" class="profile">
      </a>
    <?php endif; ?>

    <!-- 🔹 TOMBOL LOGOUT -->
    <a href="logout.php" 
       style="background: #0046ad; color: white; border-radius: 8px; padding: 6px 12px; font-size: 13px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 6px;">
      <img src="assets/logout_icon.png" alt="Logout" style="width:16px;height:16px;"> 
      Keluar
    </a>
  </div>
</div>

  <!-- Notifikasi sukses -->
  <?php if (isset($_GET['status']) && $_GET['status'] === 'product_deleted'): ?>
    <div class="notif">✅ Produk berhasil dihapus!</div>
    <script>setTimeout(() => document.querySelector('.notif').remove(), 3000);</script>
  <?php endif; ?>

  <!-- Banner -->
  <div class="banner">
    <img src="assets/Banner_Iklan.gif" alt="Iklan">
  </div>

  <!-- Tombol Tambah Produk (Hanya untuk Seller) -->
  <?php if ($_SESSION['role'] === 'seller'): ?>
    <div style="text-align: center;">
      <a href="add_product.php" class="tambah-produk-btn">➕ Tambah Produk Baru</a>
    </div>
  <?php endif; ?>

  <!-- Search -->
  <div class="search-container">
    <img src="assets/search_bar.png" alt="Search">
    <input type="text" id="searchInput" placeholder="Produk apa yang anda inginkan ?">
    <button id="searchBtn">CARI</button>
  </div>

  <!-- Konten -->
  <div class="content">
    <h2>Halo, Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
  </div>

  <!-- Story + Kategori -->
  <div class="story-wrapper">
    <div class="story-container">
      <h3>Lihat apa saja yang ada saat ini dengan fitur story</h3>
      <div class="story-list">
        <div class="story-item story-add" id="tambahStory">
          <span>+</span>
          <p>Tambah Story</p>
        </div>

        <div class="story-item" onclick="openStoryPopup('Nama Toko 1')">
          <img src="assets/story1.png" alt="Story 1">
          <p>Nama Toko 1</p>
          <span class="story-badge">Baru</span>
        </div>

        <div class="story-item" onclick="openStoryPopup('Nama Toko 2')">
          <img src="assets/story2.png" alt="Story 2">
          <p>Nama Toko 2</p>
        </div>

        <div class="story-item" onclick="openStoryPopup('Nama Toko 3')">
          <img src="assets/story3.png" alt="Story 3">
          <p>Nama Toko 3</p>
        </div>

        <div class="story-item" onclick="openStoryPopup('Nama Toko 4')">
          <img src="assets/story4.png" alt="Story 4">
          <p>Nama Toko 4</p>
        </div>
      </div>
    </div>

    <div class="kategori-produk">
      <h3>Kategori Produk</h3>
      <div class="kategori-list">
        <div class="kategori-item"><div class="kategori-icon"></div><p>Minuman</p></div>
        <div class="kategori-item"><div class="kategori-icon"></div><p>Makanan</p></div>
        <div class="kategori-item"><div class="kategori-icon"></div><p>Barang</p></div>
        <div class="kategori-item"><div class="kategori-icon"></div><p>Jasa</p></div>
      </div>
    </div>
  </div>

  <!-- Produk Terbaru -->
  <div class="produk-container">
    <h3 class="produk-title">Produk Terbaru</h3>
    <div class="produk-list">
      <?php if (empty($products)): ?>
        <p style="width:100%; text-align:center; color:#666;">Belum ada produk. Seller, ayo tambahkan produk pertamamu! 🚀</p>
      <?php else: ?>
        <?php foreach($products as $p): ?>
          <div class="produk-item">
            <div class="produk-img" 
                 style="background-image: url('<?= $p['image'] ? 'uploads/'.htmlspecialchars($p['image']) : 'assets/story1.png' ?>');">
            </div>
            <h4><?= htmlspecialchars($p['name']) ?></h4>
            <p><?= ucfirst(htmlspecialchars($p['category'])) ?></p>

            <?php if ($_SESSION['role'] === 'seller'): ?>
              <!-- Tombol untuk SELLER -->
              <div style="display:flex;gap:5px;flex-direction:column;margin-top:8px;">
                <a href="edit_product.php?id=<?= $p['product_id'] ?>" 
                   class="produk-btn" style="background:#0046ad;color:white;font-size:13px;text-decoration:none;">
                  ✏️ Edit Produk
                </a>
                <button class="produk-btn" 
                        style="background:#d32f2f;color:white;font-size:13px;"
                        onclick="showDeleteConfirm(<?= $p['product_id'] ?>, <?= json_encode($p['name']) ?>)">
                  🗑️ Hapus Produk
                </button>
              </div>
            <?php else: ?>
              <!-- Tombol untuk BUYER -->
              <button class="produk-btn" 
                      onclick="showPopup({
                        id: <?= (int)$p['product_id'] ?>,
                        name: <?= json_encode($p['name']) ?>,
                        category: <?= json_encode($p['category']) ?>,
                        price: <?= (float)$p['price'] ?>,
                        description: <?= json_encode($p['description'] ?? 'Tidak ada deskripsi') ?>,
                        image: <?= json_encode($p['image'] ? 'uploads/'.$p['image'] : 'assets/story1.png') ?>,
                        seller: <?= json_encode($p['seller_name'] ?? 'Seller') ?>,
                        phone: <?= json_encode($p['seller_phone'] ?? '') ?>
                      })">
                Lihat Produk
              </button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- POPUP DETAIL PRODUK -->
  <div class="popup" id="popup">
    <div class="popup-content">
      <span class="close-popup" onclick="closePopup()">←</span>
      <h3 id="popupTitle">Detail Produk</h3>
      <hr style="margin:10px 0;">
      <img id="popupImage" src="assets/story1.png" alt="Gambar Produk">
      <h4 id="popupNama">Nama Produk</h4>
      <p id="popupKategori">Kategori</p>
      <p id="popupHarga">Rp 0</p>
      <textarea id="popupDeskripsi" readonly>Deskripsi...</textarea>
      <a id="waButton" href="#" class="wa-btn">💬 WhatsApp Seller</a>
    </div>
  </div>

  <!-- POPUP STORY -->
<div class="story-popup" id="storyPopup">
  <div class="story-popup-content">
    <div class="story-header">
      <span class="back-arrow" onclick="closeStoryPopup()">&#9664;</span>
      <div class="story-profile"></div>
      <p class="story-nama">Nama Toko</p>
    </div>
    <hr>
    <!-- 🔹 TAMBAHKAN GAMBAR DI SINI -->
    <div class="story-body">
      <img id="storyImage" src="assets/story1.png" alt="Gambar Story" style="width:100%;height:auto;border-radius:10px;object-fit:cover;">
    </div>
    <a href="#" class="story-wa">
      <img src="assets/wa.png" alt="WA">
    </a>
  </div>
</div>

  <!-- POPUP TAMBAH STORY -->
  <div class="popup" id="popupTambahStory">
    <div class="popup-tambah-story">
      <h3>Ambil Gambar Dari</h3>
      <div class="tambah-story-opsi">
        <div class="tambah-story-item" id="fromGaleri">
          <div class="tambah-story-icon"></div>
          <p>Galeri</p>
        </div>
        <div class="tambah-story-item" id="fromKamera">
          <div class="tambah-story-icon"></div>
          <p>Kamera</p>
        </div>
      </div>
    </div>
  </div>

  <!-- POPUP KONFIRMASI HAPUS -->
  <div id="confirmDeletePopup" class="popup">
    <div class="popup-content" style="width:320px;">
      <h3 style="margin:0 0 15px;font-size:18px;">⚠️ Konfirmasi</h3>
      <p id="confirmDeleteMessage" style="margin-bottom:20px;">Anda yakin ingin menghapus produk ini?</p>
      <div style="display:flex;gap:10px;">
        <button id="confirmDeleteYes" class="wa-btn" style="background:#d32f2f;padding:10px;font-size:14px;">
          ✅ Ya, Hapus
        </button>
        <button id="confirmDeleteNo" class="wa-btn" style="background:#6c757d;padding:10px;font-size:14px;">
          ❌ Tidak
        </button>
      </div>
    </div>
  </div>

  <script>
    // Simpan session ID sekali (untuk generate token)
    sessionStorage.setItem('sid', '<?= session_id() ?>');

    // === POPUP PRODUK ===
    const popup = document.getElementById("popup");
    function showPopup(product) {
      document.getElementById('popupTitle').textContent = product.name;
      document.getElementById('popupNama').textContent = product.name;
      document.getElementById('popupKategori').textContent = product.category;
      document.getElementById('popupHarga').textContent = `Rp ${parseInt(product.price).toLocaleString('id-ID')}`;
      document.getElementById('popupDeskripsi').textContent = product.description;

      const imgPath = product.image || 'assets/story1.png';
      document.getElementById('popupImage').src = imgPath;

      if (product.phone) {
        const cleanPhone = product.phone.replace(/\D/g, '');
        if (cleanPhone.length >= 10) {
          const text = `Halo ${encodeURIComponent(product.seller)}, saya tertarik dengan produk Anda: *${encodeURIComponent(product.name)}* (Rp ${parseInt(product.price).toLocaleString('id-ID')}). Apakah masih tersedia?`;
          const waLink = `https://wa.me/62${cleanPhone}?text=${encodeURIComponent(text)}`;
          document.getElementById('waButton').href = waLink;
          document.getElementById('waButton').style.display = 'block';
        } else {
          document.getElementById('waButton').style.display = 'none';
        }
      } else {
        document.getElementById('waButton').style.display = 'none';
      }
      popup.style.display = "flex";
    }
    function closePopup() { 
      popup.style.display = "none"; 
    }

    // === FITUR PENCARIAN ===
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    const produkItems = document.querySelectorAll('.produk-item');
    function resetProduk() {
      produkItems.forEach(item => {
        item.style.display = 'block';
        const h4 = item.querySelector('h4');
        if (h4) h4.innerHTML = h4.textContent;
      });
    }
    searchBtn.addEventListener('click', function() {
      const keyword = searchInput.value.trim().toLowerCase();
      if (!keyword) { resetProduk(); return; }
      produkItems.forEach(item => {
        const h4 = item.querySelector('h4');
        const p = item.querySelector('p');
        const teks = (h4?.textContent || '') + ' ' + (p?.textContent || '');
        if (teks.toLowerCase().includes(keyword)) {
          item.style.display = 'block';
          const regex = new RegExp(`(${keyword})`, 'gi');
          h4.innerHTML = h4.textContent.replace(regex, '<mark style="background:#FFD43B;font-weight:bold;">$1</mark>');
        } else {
          item.style.display = 'none';
        }
      });
    });
    searchInput.addEventListener('keypress', e => { if (e.key === 'Enter') searchBtn.click(); });
    searchInput.addEventListener('input', () => { if (searchInput.value.trim() === '') resetProduk(); });

    // === STORY POPUP ===
    function openStoryPopup(namaToko, imageUrl = 'assets/story1.png') {
      document.getElementById("storyPopup").style.display = "flex";
      document.querySelector(".story-nama").textContent = namaToko;
      document.querySelector("#storyImage").src = imageUrl;
    }
    function closeStoryPopup() { 
      document.getElementById("storyPopup").style.display = "none"; 
    }
    document.querySelectorAll(".story-item").forEach(story => {
      story.addEventListener("click", () => {
        const namaToko = story.querySelector("p")?.textContent || "Nama Toko";
        const imgSrc = story.querySelector("img")?.src || 'assets/story1.png';
        openStoryPopup(namaToko, imgSrc);
      });
    });

    // === TAMBAH STORY ===
    const popupTambahStory = document.getElementById('popupTambahStory');
    document.querySelector('.story-add').addEventListener('click', e => {
      e.stopPropagation();
      popupTambahStory.style.display = 'flex';
    });
    window.addEventListener('click', e => {
      if (e.target === popupTambahStory) popupTambahStory.style.display = 'none';
    });
    ['fromGaleri','fromKamera'].forEach(id => {
      document.getElementById(id).addEventListener('click', () => {
        alert(`Fitur ambil gambar dari ${id === 'fromGaleri' ? 'Galeri' : 'Kamera'} (contoh saja).`);
        popupTambahStory.style.display = 'none';
      });
    });

    // === KONFIRMASI HAPUS ===
    let productIdToDelete = null;
    function showDeleteConfirm(productId, productName) {
      document.getElementById('confirmDeleteMessage').textContent = 
        `Anda yakin ingin menghapus produk "${productName}"?`;
      productIdToDelete = productId;
      document.getElementById('confirmDeletePopup').style.display = 'flex';
    }
    function hideDeleteConfirm() {
      document.getElementById('confirmDeletePopup').style.display = 'none';
      productIdToDelete = null;
    }
    document.getElementById('confirmDeleteYes').addEventListener('click', () => {
      if (productIdToDelete) {
        const token = CryptoJS.SHA256(sessionStorage.getItem('sid') + productIdToDelete).toString();
        window.location.href = `hapus_produk.php?id=${productIdToDelete}&token=${token}`;
      }
      hideDeleteConfirm();
    });
    document.getElementById('confirmDeleteNo').addEventListener('click', hideDeleteConfirm);
    document.getElementById('confirmDeletePopup').addEventListener('click', e => {
      if (e.target.id === 'confirmDeletePopup') hideDeleteConfirm();
    });

    // Nonaktifkan fitur seller untuk buyer
    if ('<?= $_SESSION['role'] ?>' === 'buyer') {
      const addStory = document.getElementById('tambahStory');
      if (addStory) addStory.style.display = 'none';
    }
</script>
</body>
</html>