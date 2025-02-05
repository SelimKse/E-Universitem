<?php
// Oturumu başlat
session_start();

// Kullanıcı oturumu kontrol et
if (isset($_SESSION['username'])) {
    // Eğer username tanımlıysa, yönlendir
    header("Location: dashboard.php"); // Yönlendirilecek sayfa
    exit(); // Kodun devamını çalıştırma
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş</title>
    <link rel="stylesheet" href="./public/css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
</head>

<body>

    <div class="loader-div">
        <div class="loader"></div>
    </div>

    <div class="container">
        <div class="login-box">
            <h1>Yönetici Girişi</h1>
            <form method="POST" onsubmit="login(event)">
                <input type="text" name="username" id="username" placeholder="Kullanıcı Adı" required>
                <input type="password" name="password" id="password" placeholder="Şifre" required>
                <button type="submit" id="login-button">Giriş Yap</button>
            </form>
        </div>
    </div>
</body>

<script src="./public/js/admin_index.js"></script>

</html>