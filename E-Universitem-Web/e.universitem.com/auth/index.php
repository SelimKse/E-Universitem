<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="../public/css/auth_index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="/assets/img/logo.png" type="image/x-icon">
    <meta name="author" content="Selim Köse">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="1 days">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="shortcut icon" href="/assets/img/icon-beyaz.png" type="image/x-icon">

</head>

<body>
    <div class="loader-div">
        <div class="loader"></div>
    </div>
    <div class="container">
        <div class="loginBox">
            <div class="box" id="ogretmen-giris">
                <img src="/assets/img/teacher_login.png" alt="Teacher">
                <h2>Öğretmen Girişi</h2>
                <button onclick="ogretmenGiris()">Giriş Yap</button>
            </div>
            <div class="box" id="ogrenci-giris">
                <img src="/assets/img/student_login.png" alt="Student">
                <h2>Öğrenci Girişi</h2>
                <button onclick="ogrenciGiris()">Giriş Yap</button>
            </div>
        </div>
    </div>
</body>
<script src="../public/js/auth_index.js"></script>

</html>