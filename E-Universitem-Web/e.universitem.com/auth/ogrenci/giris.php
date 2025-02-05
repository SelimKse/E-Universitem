<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Girişi</title>
    <link rel="stylesheet" href="../../public/css/ogrenci_giris.css">
    <script src="https://kit.fontawesome.com/3c6e7d6957.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="shortcut icon" href="/assets/img/icon-beyaz.png" type="image/x-icon">

</head>

<body>
    <div class="loader-div">
        <div class="loader"></div>
    </div>
    <div class="container">
        <div class="login-form">
            <div class="img">
                <img alt="Student">
            </div>
            <div class="form-box">
                <div class="form">
                    <form method="POST" onsubmit="login(event)">
                        <h2>Öğrenci Girişi</h2>
                        <input type="email" id="email" name="email" placeholder="E-posta" required>
                        <input type="password" id="password" name="password" placeholder="Şifre" required>
                        <div class="remember-me">
                            <label>
                                <input type="checkbox" class="input" name="remember_me" id="remember_me">
                                <span class="custom-checkbox"></span>
                                Beni Hatırla
                            </label>
                        </div>
                        <button type="submit">Giriş Yap</button>
                    </form>
                </div>
                <div class="links">
                    <a href="/">Ana Sayfa</a>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="../../public/js/ogrenci_giris.js"></script>

</html>