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

async function logout(event) {
  const response = await fetch("../../function/logout.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  });

  const request = await response.json();

  if (request.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Çıkış Başarılı!",
    });
  } else {
    Toast.fire({
      icon: "success",
      title: "Çıkış Başarılı!",
    });
  }

  setTimeout(() => {
    window.location.href = "/";
  }, 3000);
}

// ------------------------------------------------------------------------------- \\

var sidebar = document.querySelector(".sidebar");

// Sidebar'a hover yapıldığında accordion menülerin açılmasını sağlayan fonksiyon
function toggleAccordion(event, id) {
  event.preventDefault();
  var accordionMenu = document.getElementById(id);
  var arrow = event.target.querySelector(".arrow") || event.target.parentElement.querySelector(".arrow") || event.target.children[2]; // Ok öğesini seç

  // Menü açık mı kontrol et
  var isOpen = accordionMenu.style.maxHeight;

  if (isOpen) {
    accordionMenu.style.maxHeight = null; // Menü kapat
    arrow.classList.remove("rotate"); // Oku eski haline çevir
  } else {
    accordionMenu.style.maxHeight = accordionMenu.scrollHeight + "px"; // Menü aç
    arrow.classList.add("rotate"); // Oku döndür
  }

  // Diğer menüleri kapat ve okları eski haline getir
  var menus = document.querySelectorAll(".accordion-menu");
  menus.forEach(menu => {
    if (menu.id !== id) {
      menu.style.maxHeight = null; // Kapalı menüyü kapat
      var otherArrow = menu.previousElementSibling.querySelector(".arrow"); // Ok öğesini seç
      if (otherArrow) {
        otherArrow.classList.remove("rotate"); // Diğer okları eski haline çevir
      }
    }
  });
}

// Sidebar üzerine gelindiğinde 'active' sınıfını ekleyin
sidebar.addEventListener("mouseenter", function () {
  sidebar.classList.add("active");
});

// Sidebar'dan ayrıldığında menüleri kapatın ve 'active' sınıfını kaldırın
sidebar.addEventListener("mouseleave", function () {
  sidebar.classList.remove("active");

  // Tüm accordion menüleri kapat
  var menus = document.querySelectorAll(".accordion-menu");
  menus.forEach(menu => {
    menu.style.maxHeight = null;
    var arrow = menu.previousElementSibling.querySelector(".arrow");
    if (arrow) {
      arrow.classList.remove("rotate"); // Okları eski haline çevir
    }
  });
});

function checkFontLoaded() {
  const font = new FontFaceObserver("Font Awesome 6 Free");
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
          console.error("İconlar yüklenirken tekrar bir sorun oluştu.");
          retryLoad(); // Hata durumunda tekrar dene
        });
    }, 500); // 500 ms sonra yeniden dene
  }

  font
    .load()
    .then(hideLoader)
    .catch(() => {
      console.error("İconlar yüklenirken bir sorun oluştu.");
      retryLoad();
    });
}

window.addEventListener("load", () => {
  checkFontLoaded();
});

// Tema değiştirme fonksiyonu
document.addEventListener("DOMContentLoaded", function () {
  const theme = localStorage.getItem("theme") || "dark";
  if (theme) {
    changeTheme(theme);
  }
});

function changeTheme(theme) {
  if (theme === "light") {
    document.body.classList.remove("dark");
    localStorage.setItem("theme", "light");
  } else {
    document.body.classList.add("dark");
    localStorage.setItem("theme", "dark");
  }
}

// ------------------------------------------------------------------------------------------ \\
// Button click event
const kaydet = document.getElementById("kaydetButton");
const iptal = document.getElementById("cancelButton");
const form = document.getElementById("ogretmenEkleForm");

kaydet.addEventListener("click", async function (event) {
  event.preventDefault(); // Sayfanın yenilenmesini engelle
  ogretmenEkle(event);
});

iptal.addEventListener("click", function () {
  Toast.fire({
    icon: "info",
    title: "İşlem iptal Edildi! 3 saniye içinde yönlendirileceksiniz.",
  });
  setTimeout(() => {
    window.location.href = "/dashboard.php";
  }, 3000);
});

// ------------------------------------------------------------------------------------------ \\

function changeTheme() {
  const theme = localStorage.getItem("theme");
  const root = document.querySelector("html");
  const themeButton = document.getElementById("themeButton");

  if (theme === "dark") {
    root.classList.add("dark");
    root.classList.remove("light");
    themeButton.classList.add("fa-sun");
    themeButton.classList.remove("fa-moon");
    localStorage.setItem("theme", "dark"); // Varsayılan olarak 'dark' ayarla
  } else {
    root.classList.add("light");
    root.classList.remove("dark");
    themeButton.classList.add("fa-moon");
    themeButton.classList.remove("fa-sun");
    localStorage.setItem("theme", "light"); // Varsayılan olarak 'light' ayarla
  }
}

function toggleTheme() {
  const theme = localStorage.getItem("theme") === "dark" ? "light" : "dark";
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

function toggleMenu() {
  const menu = document.getElementsByClassName("sidebar")[0];
  menu.classList.toggle("active");
}

// Öğrenci ekleme fonksiyonu
async function ogretmenEkle(event) {
  event.preventDefault(); // Sayfanın yenilenmesini engelle
  const ogretmenNo = document.getElementById("ogretmenNo").value;
  const ogretmenAdı = document.getElementById("ogretmenAdı").value;
  const ogretmenSoyadı = document.getElementById("ogretmenSoyadı").value;
  const ogretmenEmail = document.getElementById("ogretmenEmail").value;
  const ogretmenPhoneNo = document.getElementById("ogretmenPhoneNo").value;
  const ogretmenSifre = document.getElementById("ogretmenSifre").value;

  const ogrenciEkleForm = document.getElementById("ogretmenEkleForm");

  if (ogrenciEkleForm.checkValidity()) {
    const response = await fetch("../../function/admin/ogretmen_ekle.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        ogretmenNo,
        ogretmenAdı,
        ogretmenSoyadı,
        ogretmenEmail,
        ogretmenPhoneNo,
        ogretmenSifre,
      }),
    });

    const request = await response.json();

    if (request.status === "success") {
      Toast.fire({
        icon: "success",
        title: "Öğretmen Başarıyla Başarıyla Eklendi!",
      });
    } else {
      Toast.fire({
        icon: "error",
        title: `${request.message}`,
      });
    }

    ogrenciEkleForm.reset();
  } else {
    Toast.fire({
      icon: "warning",
      title: "Lütfen Tüm Alanları Doldurun!",
    });
    ogrenciEkleForm.reportValidity();
  }
}
