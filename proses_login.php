<?php
session_start();
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];
$role     = $_POST['role'];

$sql = "SELECT * FROM users WHERE username='$username' AND role='$role'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);

    if ($password === $row['password']) { 
        // Simpan session lama
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];

        // Tambahan supaya cocok dengan profile_buyyer.php
        $_SESSION['user_id']   = $row['id'];     // id user
        $_SESSION['user_type'] = $row['role'];   // samain dengan role

        header("Location: dashboard.php?status=success");
    } else {
        header("Location: login.php?status=wrongpass");
    }
} else {
    header("Location: login.php?status=nouser");
}
exit;
?>
