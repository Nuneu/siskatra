<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

$product_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

// Validasi token keamanan
$expected_token = hash('sha256', session_id() . $product_id);
if ($token !== $expected_token) {
    die("Akses ditolak: token tidak valid.");
}

// Pastikan produk milik seller ini
$stmt = $conn->prepare("SELECT seller_id, image_url FROM products p LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1 WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Produk tidak ditemukan.");
}

$row = $result->fetch_assoc();
if ($row['seller_id'] != $_SESSION['user_id']) {
    die("Anda tidak berhak menghapus produk ini.");
}

// Hapus file gambar dari folder uploads/
if (!empty($row['image_url'])) {
    $file_path = __DIR__ . '/uploads/' . $row['image_url'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Hapus dari database (cascade ke product_images & orders otomatis)
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
$stmt->execute();

header("Location: dashboard.php?status=product_deleted");
exit();
?>