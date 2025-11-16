<?php
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];
$role     = $_POST['role'];

// Simpan ke database
$sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
if (mysqli_query($conn, $sql)) {
    // Ambil id user terakhir
    $last_id = mysqli_insert_id($conn);

    session_start();
    $_SESSION['username']  = $username;
    $_SESSION['role']      = $role;
    $_SESSION['user_id']   = $last_id;  // tambahin user_id
    $_SESSION['user_type'] = $role;     // tambahin user_type

    header("Location: login.php");
} else {
    header("Location: register.php?status=failed");
}
exit;
?>
