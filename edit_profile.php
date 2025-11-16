<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $conn->prepare("SELECT username, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User tidak ditemukan.");
}

$success = $_GET['success'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil - SISKATRA</title>
    <link rel="website icon" type="png" href="assets/logo_siskatrabaru.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat'; background: #f0f4ff; margin: 0; }
        .container { max-width: 400px; margin: 50px auto; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #0046ad; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; }
        button { width: 100%; padding: 12px; background: #0046ad; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        button:hover { background: #00337c; }
        .back { text-align: center; margin-top: 15px; }
        .back a { color: #0046ad; text-decoration: none; font-weight: 600; }
        .notif { padding: 12px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Edit Profil</h2>

        <?php if ($success): ?>
            <div class="notif">✅ Profil berhasil diperbarui!</div>
        <?php endif; ?>

        <form method="POST" action="proses_edit_profile.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label>No HP (untuk WhatsApp)</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Contoh: 081234567890">
            </div>
            <button type="submit">Simpan Perubahan</button>
        </form>

        <div class="back">
            <a href="<?= $_SESSION['role'] === 'seller' ? 'profile_sellerr.php' : 'profile_buyyer.php' ?>">
                ← Kembali ke Profil
            </a>
        </div>
    </div>
</body>
</html>