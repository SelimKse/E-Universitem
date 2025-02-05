<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Üniversitem</title>
    <meta name="description"
        content="E-Üniversitem projesi, öğrenci yoklama sistemi (ÖYS) üzerine geliştirilmiş bir web uygulamasıdır.">
    <meta name="keywords"
        content="e-üniversitem, öğrenci yoklama sistemi, öğrenci yoklama uygulaması, öğrenci yoklama projesi">
    <link rel="shortcut icon" href="/assets/img/logo.png" type="image/x-icon">
    <meta name="author" content="Selim Köse">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="1 days">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="public/css/index.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="shortcut icon" href="/assets/img/icon-beyaz.png" type="image/x-icon">
</head>



<body>

    <div class="loader-div">
        <div class="loader"></div>
    </div>


    <header>
        <div class="logo">
            <img id="logo" src="assets/img/logo-renkli-2.png" alt="Logo">
        </div>
        <nav>
            <ul>
                <li><a href="#hizmetler">Hizmetler</a></li>
                <li><a href="#hedefimiz">Hedefimiz</a></li>
                <li><a href="#">İletişim</a></li>
            </ul>
        </nav>

        <div class="buttons">
            <button class="button settings-button" onclick="settingsMenuButton(event)">
                <i class="fas fa-cog"></i>
            </button>
            <button class="button login-button" onclick="login(this)"><span>Giriş Yap</span></button>
            <button class="mini-navbar" onclick="menuFunction()">
                <div class="bar1"></div>
                <div class="bar2"></div>
                <div class="bar3"></div>
            </button>
        </div>

        <div class="dropdown-menu">
            <div class="radio" onclick="changeTheme(`light`)">
                <span class="material-symbols-outlined">
                    light_mode
                </span>
                <h6>Açık Tema</h6>
            </div>
            <div class="radio" onclick="changeTheme(`dark`)">
                <span class="material-symbols-outlined">
                    dark_mode
                </span>
                <h6>Koyu Tema</h6>
            </div>
        </div>
    </header>

    <div class="container">

        <div class="mobile-menu" id="mobile-menu">
            <button class="navbar-close" onclick="menuFunction()">
                <i class="fas fa-times"></i>
            </button>
            <div class="links" id="links">
                <ul>
                    <li><a href="#hizmetler" id="menu-button">Hizmetler</a></li>
                    <li><a href="#hedefimiz" id="menu-button">Hedefimiz</a></li>
                    <li><a href="#" id="menu-button">İletişim</a></li>
                </ul>
            </div>

            <div class="buttons">
                <button class="button login-button" onclick="login(this)"><span>Giriş Yap</span></button>
            </div>

        </div>

        <div class="box1">
            <div class="text">
                <h2>E-Üniversitem Nedir?</h2>
                <p>E-Üniversitem projesi, öğrenci yoklama sistemi (ÖYS) üzerine geliştirilmiş bir web uygulamasıdır.
                    Öğrenci yoklama sistemi (ÖYS), üniversitelerde ve eğitim kurumlarında yoklama sürecini
                    dijitalleştirerek
                    hızlandıran ve güvenli hale getiren bir platformdur. Öğrenciler, derslere katılım sağlamak için QR
                    kod,
                    parmak izi veya öğretmen tarafından oluşturulan benzersiz kodları kullanarak yoklama onayı verirler.
                    Sistem
                    hem web hem de mobil uygulama üzerinden kullanılabilir ve öğretmenler, anlık olarak yoklama
                    sonuçlarını
                    takip edebilir. Bu modern ve esnek yapı, kağıt kalemle yoklama alma süreçlerini ortadan kaldırarak,
                    eğitim
                    kurumlarına zaman kazandırır ve daha güvenilir bir takip sağlar.
                </p>
            </div>
            <div class="img">
                <img class="educationLogo" src="assets/img/education.png" alt="EducationPNG">
            </div>
        </div>

        <div class="hizmetler_header" id="hizmetler">
            <h2>Sağlanan Hizmetler</h2>
            <p>
                Sistemimiz, öğrencilere ve öğretmenlere aşağıdaki hizmetleri sunmaktadır!
            </p>
        </div>

        <div class="hizmetler">
            <div class="hizmet">
                <img src="assets/img/qr-code.png" alt="QRCode">
                <h3>QR Kod İle Yoklama</h3>
                <p>Öğrenciler, derslere katılım sağlamak için QR kodları tarayarak yoklama onayı verebilirler.</p>
            </div>
            <div class="hizmet">
                <img src="assets/img/fingerprint.png" alt="Fingerprint">
                <h3>Parmak İzi İle Yoklama</h3>
                <p>Öğrenciler, parmak izi okuyucu cihazlar aracılığıyla yoklama onayı verebilirler.</p>
            </div>
            <div class="hizmet">
                <img src="assets/img/unique-code.png" alt="UniqueCode">
                <h3>Kod İle Yoklama</h3>
                <p>Öğrenciler, öğretmenler tarafından oluşturulan kodları kullanarak yoklama onayı verebilirler.</p>
            </div>
        </div>

        <div class="hedefimiz_box" id="hedefimiz">
            <h2>Hedefimiz</h2>
            <p>
                Öğrenci Yoklama Sistemi (ÖYS) projesi, eğitim kurumlarında yoklama işlemlerini dijitalleştirerek
                geleneksel yöntemlerin getirdiği zorlukları aşmayı hedeflemektedir. Eğitimde dijitalleşme çağında, zaman
                yönetimi ve verimliliği artıracak bir çözüm sunmak kaçınılmaz hale gelmiştir. ÖYS, hem öğretmenlerin hem
                de öğrencilerin kullanabileceği modern bir platform sunarak bu ihtiyaca cevap vermektedir.

                Projemizin temel amacı, yoklama sürecini hızlı, güvenilir ve kullanıcı dostu bir hale getirmektir.
                Öğrenciler, derslerde yoklama vermek için QR kod, parmak izi veya öğretmen tarafından oluşturulan
                benzersiz kodları kullanarak katılımlarını onaylayabileceklerdir. Bu yenilikçi yaklaşım, eğitimde zaman
                kaybını ortadan kaldıracak, güvenilir veri takibi sağlayacak ve kağıt temelli işlemleri dijital
                platformlara taşıyacaktır. Öğretmenler, yoklama bilgilerine anlık olarak ulaşabilecek ve derslerin
                yönetimini daha kolay yapabileceklerdir.

                Projenin bir diğer önemli hedefi ise, kullanıcı deneyimini (UX) en üst düzeye çıkarmaktır. Hem mobil
                uygulama hem de web sitesi üzerinden kullanılabilen sistem, her türlü cihazda erişilebilir olacak
                şekilde tasarlanmıştır. Bu sayede öğrenciler ve öğretmenler, kampüs ortamında veya uzaktan eğitimde dahi
                rahatça yoklama işlemlerini tamamlayabileceklerdir. Platformun güvenlik açısından da güçlü olması,
                parmak izi gibi biyometrik doğrulama yöntemleriyle kişisel bilgilerin korunmasını sağlayacaktır.

                Uzun vadede, ÖYS'yi sadece bir yoklama platformu olmanın ötesine taşıyarak, eğitimde dijitalleşmenin
                diğer alanlarına da katkı sunmayı hedefliyoruz. Gelişen teknolojiyle birlikte, bu sistemi daha fazla
                fonksiyon ekleyerek (ders materyalleri paylaşımı, sınav yönetimi, öğrenci performans takibi vb.)
                kapsamlı bir eğitim yönetim sistemi haline getirmeyi planlıyoruz.

                Sonuç olarak, ÖYS projesi ile amacımız, üniversiteler başta olmak üzere eğitim kurumlarına modern, hızlı
                ve güvenilir bir yoklama çözümü sunarak eğitim süreçlerinde bir devrim yaratmak ve dijitalleşmenin
                sağladığı kolaylıkları eğitim sistemine entegre etmektir. Bu projeyle hem eğitimciler hem de öğrenciler
                için daha verimli, güvenli ve kullanıcı dostu bir deneyim sağlamayı hedefliyoruz.
            </p>
        </div>

        <footer>
            <p>© 2024 e-universitem.com Tüm hakları saklıdır.</p>
            <p>Selim Köse tarafından kodlanmıştır ❤️</p>
        </footer>

    </div>

</body>



<script src="public/js/index.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>