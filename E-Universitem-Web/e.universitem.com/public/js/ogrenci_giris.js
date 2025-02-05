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

// Form verilerini JS ile gönder
async function login(event) {
  event.preventDefault(); // Sayfanın yenilenmesini engelle

  // Formdan verileri al
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const remember = document.getElementById("remember_me").checked;

  try {
    const data = {
      email: email,
      password: password,
      remember: remember,
    };

    // fetch API ile PHP dosyasına POST isteği gönder
    const response = await fetch("../../function/ogrenci_giris.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email: email, password: password }),
    });

    // Yanıtı JSON formatında al
    const result = await response.json();
    console.log(result.token);

    // Yanıtı kontrol et
    if (result.status === "success") {
      Toast.fire({
        icon: "success",
        title: "Giriş Başarılı!",
      });
      setTimeout(() => {
        window.location.href = "https://ogrenci.e-universitem.com/auth/login.php?token=" + result.token;
      }, 2000);
    } else {
      Toast.fire({
        icon: "error",
        title: `${result.message}`,
      });
    }
  } catch (error) {
    console.error("Hata oluştu:", error); // Hata yakalayıcı
    Toast.fire({
      icon: "error",
      title: "Bir hata oluştu!",
    });
  }
}

window.onload = function () {
  const loader = document.querySelector(".loader-div");
  loader.style.display = "none";
};

document.addEventListener("DOMContentLoaded", function () {
  const theme = localStorage.getItem("theme") || "dark";
  if (theme) {
    changeTheme(theme);
  }
  changeImage();
});

function changeTheme(section) {
  const root = document.querySelector("html");
  if (section === "light") {
    root.classList.remove("dark");
    root.classList.add("light");
    localStorage.setItem("theme", "light");
  } else {
    root.classList.remove("light");
    root.classList.add("dark");
    localStorage.setItem("theme", "dark");
  }
}

function changeImage() {
  const image = document.querySelector(".img img");
  const theme = localStorage.getItem("theme") || "dark";

  if (theme === "dark") {
    image.src = "../../assets/img/login-image-dark.png";
  } else {
    image.src = "../../assets/img/login-image-light.png";
  }
}
