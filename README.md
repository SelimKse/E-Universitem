# e-Üniversitem Projesi

Bu proje, okul yoklamalarını elektronik ortama taşımayı hedefleyen bir yazılım çözümüdür. Hem web hem de mobil uygulama olarak geliştirilmiştir.

## Proje Hakkında
**e-Üniversitem**, öğretim görevlilerinin ve öğrencilerin yoklama süreçlerini kolay ve etkili bir şekilde yönetmesine olanak tanır. Bu sistem ile:
- Yoklamalar dijital ortamda tutulur.
- Gereksiz kağıt israfı önlenir.
- Yoklama verileri anında analiz edilebilir hale gelir.

## Özellikler
### Web Versiyonu
- Kullanıcı girişi ve yetkilendirme
- Yoklama alma ve raporlama modülleri
- Yönetici paneli

### Mobil Versiyonu
- Kolay ve hızlı yoklama alma
- Kullanıcı dostu arayüz
- Anlık bildirimler

## Kurulum
### Web Versiyonu
1. Bu depoyu bilgisayarına klonla:
    ```bash
    git clone https://github.com/SelimKse/E-Universitem.git
    ```
2. Gereksinimleri yükle:
    ```bash
    php -S localhost:8000
    ```
3. Ortam dosyasını düzenle (varsa):
    ```bash
    cp .env.example .env
    ```
4. Veritabanı ayarlarını yap ve projeyi başlat:
    ```bash
    php -S localhost:8000
    ```

### Mobil Versiyonu
1. Depoyu indir ve Android Studio ile aç.
2. Gereksinimleri yükle.
3. Uygulamayı simülatörde veya bağlı cihazda çalıştır.

## Teknolojiler
- Web: PHP (Laravel olmadan sade PHP), MySQL
- Mobil: Android Studio (Java)

## Katkıda Bulunma
Katkıda bulunmak isterseniz lütfen bir pull request oluşturun.

## Lisans
Bu proje MIT Lisansı ile lisanslanmıştır.

## İletişim
Sorularınız için e-posta: [bilgi@e-universitem.com](mailto:bilgi@e-universitem.com)
