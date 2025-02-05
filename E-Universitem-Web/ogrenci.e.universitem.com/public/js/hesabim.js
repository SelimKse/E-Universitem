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
  checkFontLoaded();
  changeTheme();
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

function toggleTheme() {
  const theme = localStorage.getItem("theme") === "dark" ? "light" : "dark";
  console.log(theme);
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

function sifreDegistirMenu() {
  const sifreDegistirMenu = document.getElementById("sifre-degistir-menu");
  sifreDegistirMenu.classList.toggle("active");

  // bütün inputları temizle
  const inputs = sifreDegistirMenu.querySelectorAll("input");
  inputs.forEach(input => (input.value = ""));
}

function showPassword(inputId) {
  var input = document.getElementById(inputId);
  if (input.type === "password") {
    input.type = "text";
  } else {
    input.type = "password";
  }

  // Change icon material icon
  var icon = document.getElementById("show-password-" + inputId);
  icon.textContent = input.type === "password" ? "visibility_off" : "visibility";

  // Focus the input
  input.focus();
}

async function sifreDegistir() {
  const eskiSifre = document.getElementById("eski-sifre").value;
  const yeniSifre = document.getElementById("yeni-sifre").value;
  const yeniSifreTekrar = document.getElementById("yeni-sifre-tekrar").value;

  if (!eskiSifre || !yeniSifre || !yeniSifreTekrar) {
    Toast.fire({
      icon: "error",
      title: "Lütfen tüm alanları doldurun.",
    });
    return;
  }

  if (yeniSifre !== yeniSifreTekrar) {
    Toast.fire({
      icon: "error",
      title: "Yeni şifreler uyuşmuyor.",
    });
    return;
  }

  const request = await fetch("../../function/get-session.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  });
  const response = await request.json();
  const ogrenciNo = response.data.ogrenci_no;

  const data = {
    ogrenci_no: ogrenciNo,
    eski_sifre: eskiSifre,
    yeni_sifre: yeniSifre,
    yeni_sifre_tekrar: yeniSifreTekrar,
  };

  const sifreDegistirRequest = await fetch("../../function/sifre-degistir.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  const sifreDegistirResponse = await sifreDegistirRequest.json();

  if (sifreDegistirResponse.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Şifreniz başarıyla değiştirildi.",
    });
    sifreDegistirMenu();
  } else {
    Toast.fire({
      icon: "error",
      title: sifreDegistirResponse.message,
    });
  }
}

function toogleMenu() {
  const menu = document.querySelector(".sidebar");
  menu.classList.toggle("active");
}
