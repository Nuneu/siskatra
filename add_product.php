<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

// Ambil kategori statis (bisa diganti jadi dinamis dari DB nanti)
$categories = ['makanan', 'minuman', 'barang', 'jasa'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - SISKATRA</title>
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
            border: 2px dashed #ccc;
            border-radius: 10px;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #aaa;
        }
        #imagePreview { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>➕ Tambah Produk Baru</h2>

        <form action="proses_add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="category" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= ucfirst($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="price" min="1000" step="1000" required>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" min="0" value="1" required>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" placeholder="Contoh: Rasa original, kemasan 250ml"></textarea>
            </div>

            <div class="form-group">
                <label>Gambar Produk</label>
                <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(event)">
                <div class="preview-img" id="imagePreview">📷</div>
            </div>

            <button type="submit">Simpan Produk</button>
            <a href="profile_sellerr.php" class="back-link">← Kembali ke Profil</a>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`;
                    preview.style.border = "none";
                };
                reader.readAsDataURL(file);
                preview.style.display = "block";
            }
        }
    </script>
</body>
</html>