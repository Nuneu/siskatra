<?php
// Form register
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="website icon" type="png" 
  href="assets/logo_siskatrabaru.png">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      background-color: #f2f2f2;
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
    .register-box {
      box-shadow: 20px -10px 10px rgba(0, 0, 0, 0.2);
      background: #0046ad;
      padding: 30px;
      border-radius: 10px;
      width: 350px;
      color: white;
      text-align: center;
    }
    input, select {
      width: 90%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      border: none;
    }
    button {
      background: #FFD43B;
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
  z-index: 9999;
}

.notif.success { background: #3674B5; }   /* biru tua */
.notif.failed { background: #E74C3C; }    /* merah error */

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}


  </style>
</head>
<body>
  <div class="register-box">
    <h2>REGISTER</h2>
    <img src="assets/logo_siskatrabaru.png" alt="Logo SISKATRA" class="logo">
    <form action="proses_register.php" method="POST">
      <input type="text" name="username" placeholder="Username" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <select name="role" required>
        <option value="" style="color: gray;">Pilih Role Anda Disini</option>
        <option value="buyer">Buyer</option>
        <option value="seller">Seller</option>
      </select><br>
      <button type="submit">Register</button>
    </form>
    <p>Sudah punya akun? <a href="login.php" style="color:white;">Login</a></p>
  </div>

  <?php if (isset($_GET['status'])): ?>
<div class="notif <?php echo $_GET['status']; ?>">
  <?php
    if ($_GET['status'] == 'success') {
      echo "Registrasi berhasil! Silakan login sekarang.";
    } elseif ($_GET['status'] == 'failed') {
      echo "Registrasi gagal! Username mungkin sudah ada.";
    }
  ?>
</div>
<script>
  setTimeout(() => {
    document.querySelector('.notif').style.display = 'none';
  }, 4000); // hilang otomatis 4 detik
</script>
<?php endif; ?>

</body>
</html>