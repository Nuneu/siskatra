<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses tidak diizinkan.");
}

$product_name = trim($_POST['name']);
$category_id = (int)$_POST['category'];
$price = (float)$_POST['price'];
$stock = (int)$_POST['stock'];
$description = trim($_POST['description']);
$seller_id = $_SESSION['user_id'];

if (empty($product_name) || $price <= 0 || $stock < 0 || $category_id <= 0) {
    die("Semua field wajib diisi dengan benar.");
}

$image_url = null;

// --- UPLOAD GAMBAR ---
if (!empty($_FILES['image']['name'])) {
    // Gunakan __DIR__ untuk path absolut
    $target_dir = __DIR__ . "/uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($file_ext, $allowed)) {
        die("Format gambar tidak diizinkan. Gunakan JPG/PNG.");
    }

    $image_name = "produk_" . $seller_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $image_name;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        die("Gagal mengupload gambar. Error: " . error_get_last()['message']);
    }

    $image_url = $image_name; // simpan nama file saja
}

// --- SIMPAN KE DB ---
$stmt = $conn->prepare("
    INSERT INTO products (product_name, category_id, description, price, stock, seller_id, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("sisdii", $product_name, $category_id, $description, $price, $stock, $seller_id);

if (!$stmt->execute()) {
    die("Gagal menyimpan produk: " . $stmt->error);
}

$product_id = $stmt->insert_id;

// --- SIMPAN GAMBAR KE product_images ---
if ($image_url) {
    $stmt2 = $conn->prepare("
        INSERT INTO product_images (product_id, image_url, is_main, uploaded_at) 
        VALUES (?, ?, 1, NOW())
    ");
    $stmt2->bind_param("is", $product_id, $image_url);
    $stmt2->execute();
}

header("Location: profile_sellerr.php?status=product_added");
exit;
?>