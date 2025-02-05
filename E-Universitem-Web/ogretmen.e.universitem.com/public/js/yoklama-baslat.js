const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: toast => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

window.addEventListener("load", () => {
  changeTheme();
  checkFontLoaded();
});

function checkFontLoaded() {
  const font = new FontFaceObserver("Material Symbols Outlined");
  const loader = document.querySelector(".loader-div");

  function hideLoader() {
    loader.style.display = "none";
  }

  function retryLoad() {
    setTimeout(() => {
      font
        .load()
        .then(hideLoader)
        .catch(() => {
          retryLoad(); // Hata durumunda tekrar dene
        });
    }, 500); // 500 ms sonra yeniden dene
  }

  font
    .load()
    .then(hideLoader)
    .catch(() => {
      retryLoad();
    });
}

function changeTheme() {
  const theme = localStorage.getItem("theme") || "dark"; // Varsayılan olarak 'dark' temasını kullan
  const root = document.querySelector("html");

  if (theme === "dark") {
    root.classList.add("dark");
    root.classList.remove("light");
    localStorage.setItem("theme", "dark"); // Varsayılan olarak 'dark' ayarla
    document.getElementById("header-logo").src = "../../assets/logo-beyaz.png";
  } else {
    root.classList.add("light");
    root.classList.remove("dark");
    localStorage.setItem("theme", "light"); // Varsayılan olarak 'light' ayarla
    document.getElementById("header-logo").src = "../../assets/logo-siyah.png";
  }
}

function toggleTheme(themes) {
  if (themes) {
    localStorage.setItem("theme", themes);
    changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
    return;
  }
  const theme = localStorage.getItem("theme") === "dark" ? "light" : "dark";
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

function toogleMenu() {
  const menu = document.querySelector(".sidebar");
  menu.classList.toggle("active");
}

var ogretmen_no;
fetch("../../function/get-session.php")
  .then(response => response.json())
  .then(data => {
    ogretmen_no = data.ogretmen_no;
  });

async function yoklamaBaslat(event) {
  event.preventDefault();

  const ders = document.getElementById("yoklama-ders").value;
  const sınıf = document.getElementById("yoklama-sinif").value;
  const ogretmen = document.getElementById("ogretmen").data - ogretmen_no;
  const tarih = document.getElementById("baslangic-tarihi").value;

  if (ders === "null" || sınıf === "null" || ogretmen === "null" || tarih === "") {
    Toast.fire({
      icon: "error",
      title: "Lütfen tüm alanları doldurun!",
    });
    return;
  }

  // tarihe 10 dk ekle
  const date = new Date(tarih);
  date.setMinutes(date.getMinutes() + 10);
  date.setHours(date.getHours() + 3);
  const bitis_tarihi = date.toISOString().slice(0, 19).replace("T", " ");

  const response = await fetch("../../function/yoklama-baslat.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ders: ders,
      sinif: sınıf,
      ogretmen: ogretmen,
      baslangic: tarih,
      bitis: bitis_tarihi,
    }),
  });

  const result = await response.json();

  if (result.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Yoklama başarıyla başlatıldı!",
    });

    try {
      const qr_url = `https://api.e-universitem.com/yoklama/katil.php?yoklama_id=${result.data.yoklama_id}&ogrenci_no=${ogrenci_no}`;
      yoklamaQR(qr_url);
    } catch (error) {
      Toast.fire({
        icon: "error",
        title: "QR kod oluşturulurken bir hata oluştu! Lütfen tekrar deneyin.",
      });
      console.error(error);
    }

    const ders_adi = (document.getElementById("yoklama-ders-adi").textContent = ders);
    const sinif_adi = (document.getElementById("yoklama-sinif-adi").textContent = sınıf);
    const ogretmen_adi = (document.getElementById("yoklama-ogretmen-adi").textContent = ogretmen);
    const baslangic_tarihi = (document.getElementById("yoklama-baslangic-tarihi").textContent = tarih);
    const bitis_tarihi = (document.getElementById("yoklama-bitis-tarihi").textContent = bitis_tarihi);
    const yoklama_kodu = (document.getElementById("yoklama-kodu").textContent = result.data.yoklama_kodu);

    showPopup();
    
  } else {
    Toast.fire({
      icon: "error",
      title: result.message,
    });
  }
}

function yoklamaReset(event) {
  const form = document.getElementById("yoklamaBaslatForm");
  form.reset();
}

// ------------------------------------------------------------------------------------------ \\

async function yoklamaQR(link) {
  function showQRCode() {
    try {
      // QR kod için bir geçici div oluştur
      const tempDiv = document.createElement("div");

      // QR kodu geçici div içinde oluştur
      new QRCode(tempDiv, {
        text: qrData,
        width: 200,
        height: 200,
      });

      // QR kod tamamlandıktan sonra, img'ye aktar
      setTimeout(() => {
        const qrImgElement = document.querySelector(".qr-code-img"); // Hedef <img> etiketi
        const qrImgSrc = tempDiv.querySelector("img").src; // Geçici div içindeki QR kod görüntüsü
        qrImgElement.src = qrImgSrc; // QR kodu img'ye aktar
      }, 100); // QR kodun tamamen oluşturulmasını beklemek için gecikme ekle
    } catch (error) {
      Toast.fire({
        icon: "error",
        title: "QR kod oluşturulurken bir hata oluştu! Lütfen tekrar deneyin.",
      });
      console.error(error);
    }
  }

  showQRCode();
}

function showPopup() {
  const popup = document.querySelector(".yoklama-bilgi-popup");
  popup.classList.toggle("active");
}
