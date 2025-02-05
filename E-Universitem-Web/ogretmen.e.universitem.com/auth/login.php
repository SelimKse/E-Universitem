<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş</title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <script>
        // Token URL'den alınıyor veya başka bir şekilde belirleniyor
        const params = new URLSearchParams(window.location.search);
        const token = params.get('token');

        // PHP dosyasına AJAX isteği gönderiliyor
        $.ajax({
            url: '../function/login_check.php',
            method: 'GET',
            data: { token: token },
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Giriş başarılı!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    // Giriş başarılı ise, anasayfaya yönlendir
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 1500);
                } else {
                    // Giriş başarısız ise, hata mesajını göster
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: response.message,
                    });

                    // Hata mesajını gösterdikten sonra, anasayfaya yönlendir
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 3000);
                }
            },
            error: function (response) {
                // Hata oluştuğunda hata mesajını göster
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Bir hata oluştu! Lütfen tekrar deneyin.',
                });
            }
        });
    </script>

</body>

</html>