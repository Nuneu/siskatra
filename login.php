<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="website icon" type="png"
  href="assets/logo_siskatrabaru.png">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      background-color: #ffffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .logo {
  width: 100px;        /* ukuran fix */
  height: 100px;       /* tinggi fix */
  object-fit: contain; /* jaga proporsi logo */
  margin: 10px auto;   /* center dengan margin */
  display: block;      /* biar tidak ikut text */
}
    .login-box {
      box-shadow: 20px -10px 10px rgba(0, 0, 0, 0.2);
      background: #0046ad;
      padding: 30px;
      border-radius: 10px;
      width: 350px;
      color: white;
      text-align: center;
    }
    input {
      width: 90%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      border: none;
    }
    .role-container {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin: 15px 0;
    }
    button {
      background: #FFD43B;
      font-family: 'Lilita One', cursive;
      border: none;
      padding: 10px;
      width: 95%;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }
    .notif {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 15px 25px;
  border-radius: 10px;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  color: white;
  animation: fadeIn 0.5s ease-in-out;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.notif.success { background: #3674B5; }    /* biru tua */
.notif.wrongpass { background: #578FCA; }  /* biru medium */
.notif.nouser { background: #A1E3F9; color: #000; } /* biru muda */

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

  </style>
</head>
<body>
  <div class="login-box">
    <h2>LOGIN</h2>
    <img src="assets/logo_siskatrabaru.png" alt="Logo SISKATRA" class="logo">
    <form action="proses_login.php" method="POST">
      <input type="text" name="username" placeholder="Username" required><br>
      <input type="password" name="password" placeholder="Password" required><br>

      <div class="role-container">
        <label><input type="radio" name="role" value="buyer" required> Buyer</label>
        <label><input type="radio" name="role" value="seller"> Seller</label>
      </div>

      <?php if (isset($_GET['status']) && $_GET['status'] === 'logged_out'): ?>
<div class="notif success">
  ✅ Anda telah berhasil keluar.
</div>
<script>
  setTimeout(() => document.querySelector('.notif').remove(), 3000);
</script>
<?php endif; ?>

      <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="register.php" style="color:white;">Register</a></p>
  </div>
  <?php if (isset($_GET['status'])): ?>
<div class="notif <?php echo $_GET['status']; ?>">
  <?php
    if ($_GET['status'] == 'success') {
      echo "Login berhasil!";
    } elseif ($_GET['status'] == 'wrongpass') {
      echo "Password salah!";
    } elseif ($_GET['status'] == 'nouser') {
      echo "User tidak ditemukan!";
    }
  ?>
</div>
<script>
  setTimeout(() => {
    document.querySelector('.notif').style.display = 'none';
  }, 3000); // hilang otomatis 3 detik
</script>
<?php endif; ?>
</body>
</html>
