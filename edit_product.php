<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID produk tidak valid.");
}

$product_id = (int)$_GET['id'];
$seller_id = $_SESSION['user_id'];

// Ambil data produk + kategori + gambar utama
$stmt = $conn->prepare("
    SELECT p.*, c.category_name, pi.image_url AS main_image
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
    WHERE p.product_id = ? AND p.seller_id = ?
");
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Produk tidak ditemukan atau bukan milik Anda.");
}

$product = $result->fetch_assoc();

// Ambil semua kategori untuk dropdown
$cat_stmt = $conn->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name");
$cat_stmt->execute();
$categories = $cat_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - SISKATRA</title>
    <link rel="website icon" type="png" href="assets/logo_siskatrabaru.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f2f2f2;
            margin: 0;
        }
        .container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #284e7f;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
        }
        textarea { height: 100px; resize: vertical; }
        button {
            width: 100%;
            padding: 12px;
            background: #FFD43B;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background: #f7ca1e; }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #0046ad;
            text-decoration: none;
            font-weight: 600;
        }
        .preview-img {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .preview-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        #newImagePreview { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Edit Produk</h2>

        <form action="proses_edit_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="category" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" 
                                <?= $cat['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="price" value="<?= $product['price'] ?>" min="1000" step="100" required>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0" required>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Gambar Saat Ini</label>
                <div class="preview-img">
                    <?php if (!empty($product['main_image']) && file_exists("uploads/" . $product['main_image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($product['main_image']) ?>" alt="Gambar Produk">
                    <?php else: ?>
                        📦
                    <?php endif; ?>
                </div>
                <label style="margin-top:10px;">Ganti Gambar (opsional)</label>
                <input type="file" name="image" accept="image/*" onchange="previewNewImage(event)">
                <div class="preview-img" id="newImagePreview"></div>
            </div>

            <button type="submit">Simpan Perubahan</button>
            <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
        </form>
    </div>

    <script>
        function previewNewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('newImagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}">`;
                    preview.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>