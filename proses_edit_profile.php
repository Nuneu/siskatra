<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

$user_id = $_SESSION['user_id'];
$username = trim($_POST['username']);
$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ''); // hanya angka

if (empty($username)) {
    die("Username wajib diisi.");
}

// Pastikan username belum dipakai orang lain
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$stmt->bind_param("si", $username, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    die("Username sudah digunakan oleh pengguna lain.");
}

// Update
$stmt = $conn->prepare("UPDATE users SET username = ?, phone = ? WHERE id = ?");
$stmt->bind_param("ssi", $username, $phone, $user_id);

if ($stmt->execute()) {
    // Update session
    $_SESSION['username'] = $username;
    header("Location: edit_profile.php?success=1");
} else {
    die("Gagal memperbarui profil.");
}
exit;