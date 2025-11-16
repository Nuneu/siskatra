<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses tidak diizinkan.");
}

$product_id = (int)($_POST['product_id'] ?? 0);
$seller_id = $_SESSION['user_id'];

// Pastikan produk milik seller ini
$check_stmt = $conn->prepare("SELECT seller_id FROM products WHERE product_id = ?");
$check_stmt->bind_param("i", $product_id);
$check_stmt->execute();
$check = $check_stmt->get_result()->fetch_assoc();

if (!$check || $check['seller_id'] != $seller_id) {
    die("Produk tidak ditemukan atau bukan milik Anda.");
}

// Ambil data
$product_name = trim($_POST['name']);
$category_id = (int)$_POST['category'];
$price = (float)$_POST['price'];
$stock = (int)$_POST['stock'];
$description = trim($_POST['description']);

if (empty($product_name) || $price <= 0 || $category_id <= 0) {
    die("Data tidak valid.");
}

$image_url = null;

// --- UPLOAD GAMBAR BARU (jika ada) ---
if (!empty($_FILES['image']['name'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($file_ext, $allowed)) {
        die("Format gambar tidak diizinkan. Gunakan JPG/PNG.");
    }

    // Hapus gambar lama (jika ada & is_main = 1)
    $old_img_stmt = $conn->prepare("
        SELECT image_url FROM product_images 
        WHERE product_id = ? AND is_main = 1
    ");
    $old_img_stmt->bind_param("i", $product_id);
    $old_img_stmt->execute();
    $old_img = $old_img_stmt->get_result()->fetch_assoc();

    if ($old_img && file_exists($target_dir . $old_img['image_url'])) {
        unlink($target_dir . $old_img['image_url']);
    }

    // Simpan gambar baru
    $image_url = "produk_" . $seller_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $image_url;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        die("Gagal mengupload gambar.");
    }
}

// --- UPDATE PRODUK ---
$stmt = $conn->prepare("
    UPDATE products 
    SET product_name = ?, category_id = ?, description = ?, price = ?, stock = ? 
    WHERE product_id = ? AND seller_id = ?
");
$stmt->bind_param("sisdiii", $product_name, $category_id, $description, $price, $stock, $product_id, $seller_id);

if (!$stmt->execute()) {
    die("Gagal memperbarui produk: " . $stmt->error);
}

// --- UPDATE GAMBAR UTAMA (jika ada upload baru) ---
if ($image_url) {
    // Hapus entri gambar utama lama
    $del_stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ? AND is_main = 1");
    $del_stmt->bind_param("i", $product_id);
    $del_stmt->execute();

    // Tambahkan gambar baru sebagai utama
    $ins_stmt = $conn->prepare("
        INSERT INTO product_images (product_id, image_url, is_main, uploaded_at) 
        VALUES (?, ?, 1, NOW())
    ");
    $ins_stmt->bind_param("is", $product_id, $image_url);
    $ins_stmt->execute();
}

// Sukses
header("Location: profile_sellerr.php?status=product_updated");
exit;
?>