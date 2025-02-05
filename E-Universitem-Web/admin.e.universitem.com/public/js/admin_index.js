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
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;

  // fetch API ile PHP dosyasına POST isteği gönder
  const response = await fetch("../../function/admin_giris.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ username: username, password: password }),
  });

  // Yanıtı JSON formatında al
  const result = await response.json();

  // Yanıtı kontrol et
  if (result.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Giriş Başarılı!",
    });
    setTimeout(() => {
      window.location.href = "/dashboard.php";
    }, 2000);
  } else {
    Toast.fire({
      icon: "error",
      title: `${result.message}`,
    });
  }
}

// Sayfa tamamen yüklendiğinde font kontrolü yap
window.addEventListener("load", () => {
  const loader = document.querySelector(".loader-div");
  loader.style.display = "none";
});
