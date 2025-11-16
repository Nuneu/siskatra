<?php
session_start();
// Jika belum login, arahkan ke login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Keluar — SISKATRA</title>
  <link rel="website icon" type="png" href="assets/logo_siskatrabaru.png">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(135deg, #0046ad, #284e7f);
      margin: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .logout-box {
      background: white;
      padding: 40px;
      border-radius: 20px;
      width: 400px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .logo {
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      display: block;
    }

    h2 {
      color: #0046ad;
      margin-bottom: 20px;
      font-weight: 700;
    }

    .user-info {
      background: #f5f9ff;
      border-radius: 12px;
      padding: 15px;
      margin: 20px 0;
      font-size: 15px;
      color: #333;
    }

    .btn-group {
      display: flex;
      gap: 12px;
      margin-top: 25px;
    }

    .btn {
      flex: 1;
      padding: 12px;
      border-radius: 10px;
      font-weight: bold;
      font-size: 15px;
      cursor: pointer;
      border: none;
      transition: all 0.3s;
    }

    .btn-cancel {
      background: #e0e0e0;
      color: #555;
    }

    .btn-cancel:hover {
      background: #d0d0d0;
    }

    .btn-logout {
      background: #d32f2f;
      color: white;
    }

    .btn-logout:hover {
      background: #b71c1c;
      transform: translateY(-2px);
    }

    .back-link {
      display: inline-block;
      margin-top: 20px;
      color: #0046ad;
      text-decoration: none;
      font-weight: 600;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="logout-box">
    <img src="assets/logo_siskatrabaru.png" alt="SISKATRA" class="logo">
    <h2>🚪 Keluar dari Akun?</h2>
    
    <div class="user-info">
      <strong><?= htmlspecialchars($_SESSION['username']) ?></strong><br>
      <small><?= ucfirst($_SESSION['role']) ?></small>
    </div>

    <p style="color:#666; margin-bottom:25px;">
      Yakin ingin keluar? Anda perlu login lagi untuk mengakses akun.
    </p>

    <div class="btn-group">
      <a href="dashboard.php" class="btn btn-cancel">❌ Batal</a>
      <a href="do_logout.php" class="btn btn-logout">✅ Ya, Keluar</a>
    </div>

    <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
  </div>
</body>
</html>