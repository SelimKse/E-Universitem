package com.selimkose.euniversitem;

import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.Configuration;
import android.os.Build;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;
import android.os.CountDownTimer;


import androidx.appcompat.app.AppCompatActivity;
import androidx.annotation.NonNull;
import androidx.biometric.BiometricPrompt;
import androidx.core.content.ContextCompat;

import com.android.volley.AuthFailureError;
import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.journeyapps.barcodescanner.CaptureActivity;
import com.selimkose.euniversitem.utils.SecurePreferences;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.Executor;
import java.util.concurrent.Executors;



public class StudentMainPage extends AppCompatActivity {

    private static final int REQUEST_CODE_SCAN = 100; // QR kod tarama isteği için kullanılan sabit
    private CountDownTimer countDownTimer;
    private long timeLeftInMillis = 15 * 60 * 1000; // 15 dakika (15 dakika * 60 saniye * 1000 ms)
    private AlertDialog customDialog;
    private TextView dynamicTextView;
    private boolean isCountdownActive = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.student_main_page);

        // SecurePreferences'dan veriyi çekme
        SecurePreferences securePreferences = SecurePreferences.getInstance(this);
        String ogrenci_adi = securePreferences.get("ogrenci_adi", "Ad");
        String ogrenci_soyadi = securePreferences.get("ogrenci_soyadi", "Soyad");

        // İsim ve soyismi birleştirme
        String fullName = ogrenci_adi + " " + ogrenci_soyadi;

        // Toast ile yazdırma
        Toast.makeText(this, "Merhaba " + fullName + " 👋", Toast.LENGTH_LONG).show();

        // QR Kod Okuma Butonunu tanımla
        Button qrCodeButton = findViewById(R.id.qrCodeButton);

        // QR kod okutma butonuna tıklanabilirlik ekle
        qrCodeButton.setOnClickListener(view -> {
            // Bitiş tarihini al
            long endTime = getEndTime();
            long currentTime = System.currentTimeMillis();

            if (endTime > currentTime) {
                // Süre bitmemişse, kalan zamanı göster
                long remainingTime = endTime - currentTime;
                showProgressDialog(remainingTime);
            } else {
                // Süre bitmişse, QR kod tarayıcıyı başlat
                Intent intent = new Intent(StudentMainPage.this, CaptureActivity.class);
                startActivityForResult(intent, REQUEST_CODE_SCAN);
            }
        });

        Button sendCodeButton = findViewById(R.id.sendCodeButton);
        EditText inputCodeEditText = findViewById(R.id.inputYoklamaCode);

        sendCodeButton.setOnClickListener(view -> {
            String code = inputCodeEditText.getText().toString().trim();

            // SecurePreferences'dan veriyi çekme
            String ogrenci_no = securePreferences.get("ogrenci_no", "ogrenci_no");

            if (code.length() == 8) {
                Executor executor = ContextCompat.getMainExecutor(this);

                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) {
                    // BiometricPrompt.Callback tanımla
                    BiometricPrompt.AuthenticationCallback callback = new BiometricPrompt.AuthenticationCallback() {
                        @Override
                        public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                            super.onAuthenticationError(errorCode, errString);

                            if (errorCode == 13) {
                                runOnUiThread(() -> Toast.makeText(StudentMainPage.this, "İptal Edildi!", Toast.LENGTH_SHORT).show());
                            } else {
                                runOnUiThread(() -> Toast.makeText(StudentMainPage.this, "Hata: " + errString, Toast.LENGTH_SHORT).show());
                            }
                        }

                        @Override
                        public void onAuthenticationSucceeded(@NonNull BiometricPrompt.AuthenticationResult result) {
                            super.onAuthenticationSucceeded(result);
                            runOnUiThread(() -> {
                                Toast.makeText(StudentMainPage.this, "Doğrulama Başarılı!", Toast.LENGTH_SHORT).show();
                                yoklamaKatil(code, ogrenci_no);
                            });
                        }
                    };

                    // BiometricPrompt nesnesini oluştur
                    BiometricPrompt biometricPrompt = new BiometricPrompt(this, executor, callback);

                    // BiometricPrompt PromptInfo nesnesi oluştur
                    BiometricPrompt.PromptInfo promptInfo = new BiometricPrompt.PromptInfo.Builder()
                            .setTitle("Biometrik Doğrulama")
                            .setSubtitle("Devam etmek için parmak izi veya biyometrik kimlik doğrulaması yapın.")
                            .setNegativeButtonText("İptal")
                            .build();

                    // BiometricPrompt başlat
                    biometricPrompt.authenticate(promptInfo);
                } else {
                    // Biometric doğrulama API 28 altı cihazlarda desteklenmiyor
                    if (Build.VERSION.SDK_INT < Build.VERSION_CODES.P) {
                        new AlertDialog.Builder(this)
                                .setTitle("Uyarı")
                                .setMessage("Cihazınız biyometrik doğrulamayı desteklemiyor.")
                                .setPositiveButton("Tamam", null)
                                .show();
                    }
                }
            } else {
                Toast.makeText(StudentMainPage.this, "Lütfen geçerli 8 haneli bir kod girin.", Toast.LENGTH_SHORT).show();
            }
        });

        // Çıkış butonunu tanımla
        ImageButton logoutButton = findViewById(R.id.logoutButton);
        logoutButton.setOnClickListener(view -> {
            // alert dialog oluştur
            AlertDialog.Builder builder = new AlertDialog.Builder(StudentMainPage.this);
            builder.setTitle("Çıkış Yap");
            builder.setMessage("Çıkış yapmayı onaylıyor musunuz?");
            builder.setNegativeButton("Hayır", null);
            builder.setPositiveButton("Evet", new DialogInterface.OnClickListener() {
                public void onClick(DialogInterface dialog, int id) {
                    securePreferences.clear();
                    Intent intent = new Intent(StudentMainPage.this, MainActivity.class);
                    startActivity(intent);
                    finish();
                }
            });

            AlertDialog dialog = builder.create();
            dialog.show();
        });

        ImageView logoImageView = findViewById(R.id.logoImage);

        if (isDarkMode()) {
            // Kararmış temada logo değiştir
            logoImageView.setImageResource(R.drawable.logobeyaz);
        } else {
            // Açık temada logo değiştir
            logoImageView.setImageResource(R.drawable.logosiyah);
        }

    }

    private boolean isDarkMode() {
        int nightModeFlags = getResources().getConfiguration().uiMode & Configuration.UI_MODE_NIGHT_MASK;
        return nightModeFlags == Configuration.UI_MODE_NIGHT_YES;
    }

    @Override
    protected void onResume() {
        super.onResume();
        // Arka planda iken tekrar açıldığında mesajı göstermemek için onResume'da herhangi bir işlem yapmadık.
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        // QR kod tarama işlemi tamamlandığında
        if (requestCode == REQUEST_CODE_SCAN && resultCode == RESULT_OK) {
            // QR koddan gelen veri
            String qrCodeResult = data.getStringExtra("SCAN_RESULT");

            String veri = qrCodeResult;

            String[] veriler = veri.split("-");
            String yoklama_kodu = veriler[1];

            // SecurePreferences'dan veriyi çekme
            SecurePreferences securePreferences = SecurePreferences.getInstance(this);
            String ogrenci_no = securePreferences.get("ogrenci_no", "ogrenci_no");

            Executor executor = ContextCompat.getMainExecutor(this);

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) {
                // BiometricPrompt.Callback tanımla
                BiometricPrompt.AuthenticationCallback callback = new BiometricPrompt.AuthenticationCallback() {
                    @Override
                    public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                        super.onAuthenticationError(errorCode, errString);

                        if (errorCode == 13) {
                            runOnUiThread(() -> Toast.makeText(StudentMainPage.this, "İptal Edildi!", Toast.LENGTH_SHORT).show());
                        } else {
                            runOnUiThread(() -> Toast.makeText(StudentMainPage.this, "Hata: " + errString, Toast.LENGTH_SHORT).show());
                        }
                    }

                    @Override
                    public void onAuthenticationSucceeded(@NonNull BiometricPrompt.AuthenticationResult result) {
                        super.onAuthenticationSucceeded(result);
                        runOnUiThread(() -> {
                            Toast.makeText(StudentMainPage.this, "Doğrulama Başarılı!", Toast.LENGTH_SHORT).show();
                            yoklamaKatil(yoklama_kodu, ogrenci_no);
                        });
                    }
                };

                // BiometricPrompt nesnesini oluştur
                BiometricPrompt biometricPrompt = new BiometricPrompt(this, executor, callback);

                // BiometricPrompt PromptInfo nesnesi oluştur
                BiometricPrompt.PromptInfo promptInfo = new BiometricPrompt.PromptInfo.Builder()
                        .setTitle("Biometrik Doğrulama")
                        .setSubtitle("Devam etmek için parmak izi veya biyometrik kimlik doğrulaması yapın.")
                        .setNegativeButtonText("İptal")
                        .build();

                // BiometricPrompt başlat
                biometricPrompt.authenticate(promptInfo);
            } else {
                // Biometric doğrulama API 28 altı cihazlarda desteklenmiyor
                if (Build.VERSION.SDK_INT < Build.VERSION_CODES.P) {
                    new AlertDialog.Builder(this)
                            .setTitle("Uyarı")
                            .setMessage("Cihazınız biyometrik doğrulamayı desteklemiyor.")
                            .setPositiveButton("Tamam", null)
                            .show();
                }
            }
        }
    }


    private void yoklamaKatil(String yoklamaKodu, String ogrenciNo) {
        String url = "https://api.e-universitem.com/yoklamalar/katil"; // API URL'sini yaz
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        showLoader();

        // JSON objesi oluştur
        JSONObject katilParams = new JSONObject();
        try {
            katilParams.put("yoklama_kodu", yoklamaKodu);
            katilParams.put("ogrenci_no", ogrenciNo);
        } catch (JSONException e) {
            e.printStackTrace();
            Toast.makeText(StudentMainPage.this, "JSON oluşturulurken hata oluştu!", Toast.LENGTH_SHORT).show();
            hideLoader();
            return;
        }

        // Özelleştirilmiş JsonObjectRequest
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.POST,
                url,
                katilParams,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                        hideLoader();

                        try {
                            // Gelen mesaja göre işlem yap
                            String status = response.getString("status");
                            String message = response.getString("message");

                            if ("success".equals(status)) {
                                // Başarılı mesajını göster
                                AlertDialog.Builder builder = new AlertDialog.Builder(StudentMainPage.this);
                                builder.setTitle("Başarıyla Katıldınız!");
                                builder.setMessage("Yoklamaya başarıyla katıldınız.");

                                // "Tamam" butonunu ekleyin ve butona tıklanınca yapılacak işlemi belirtin
                                builder.setPositiveButton("Tamam", new DialogInterface.OnClickListener() {
                                    @Override
                                    public void onClick(DialogInterface dialog, int which) {
                                        // "Tamam" butonuna tıklayınca yapılacak işlemler
                                        // Örneğin, ana sayfaya geri dönebilirsiniz veya başka bir işlem yapabilirsiniz
                                        dialog.dismiss();  // Dialogu kapat
                                    }
                                });

                                // Dialog'u oluşturup göster
                                AlertDialog dialog = builder.create();
                                dialog.show();
                            } else {
                                // Hata mesajını göster
                                AlertDialog.Builder builder = new AlertDialog.Builder(StudentMainPage.this);
                                builder.setTitle("Hata");
                                builder.setMessage(message);
                                builder.setPositiveButton("Tamam", null);
                                builder.show();

                                Toast.makeText(StudentMainPage.this, message, Toast.LENGTH_SHORT).show();

                                hideLoader();
                                return;
                            }

                        } catch (JSONException e) {
                            e.printStackTrace();
                            Toast.makeText(StudentMainPage.this, "Yanıt işlenirken hata oluştu!", Toast.LENGTH_SHORT).show();
                        }

                        // Loader'ı gizle
                        hideLoader();
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        hideLoader();

                        if (error.networkResponse != null) {
                            int statusCode = error.networkResponse.statusCode;
                            String errorData = new String(error.networkResponse.data);

                            try {
                                // Gelen veri JSONObject formatında
                                JSONObject errorResponse = new JSONObject(errorData);

                                // "message" alanını alıyoruz (JSONArray olduğu için ilk elemanı alıyoruz)
                                JSONArray messageArray = errorResponse.optJSONArray("message");

                                // Eğer message array varsa, ilk mesajı alalım
                                if (messageArray != null && messageArray.length() > 0) {
                                    String errorMessage = messageArray.getString(0);
                                    AlertDialog.Builder builder = new AlertDialog.Builder(StudentMainPage.this);
                                    builder.setTitle("Hata");
                                    builder.setMessage(errorMessage);
                                    builder.setPositiveButton("Tamam", null);
                                    builder.show();

                                    hideLoader();
                                    return;
                                } else {
                                    // Eğer "message" yoksa, varsayılan bir hata mesajı göster
                                    String errorMessage = errorResponse.optString("message", "Bir hata oluştu!");
                                    AlertDialog.Builder builder = new AlertDialog.Builder(StudentMainPage.this);
                                    builder.setTitle("Hata");
                                    builder.setMessage(errorMessage);
                                    builder.setPositiveButton("Tamam", null);
                                    builder.show();

                                    hideLoader();
                                }

                                // Hata kodunu ve mesajı konsola yazdır
                                System.out.println("Hata kodu: " + statusCode);
                                System.out.println("Hata Bilgisi: " + errorData);

                            } catch (JSONException e) {
                                // JSON parse hatası durumunda genel hata mesajı
                                String errorMessage = "Yanıt işlenirken hata oluştu!";
                                Toast.makeText(StudentMainPage.this, errorMessage, Toast.LENGTH_SHORT).show();
                                System.out.println("JSON parsing error: " + e.getMessage());
                            }

                        } else {
                            // Ağ hatası durumu
                            System.out.println("Hata: " + error.getMessage());
                            Toast.makeText(StudentMainPage.this, "Ağ hatası: " + error.getMessage(), Toast.LENGTH_SHORT).show();
                        }

                        if (error instanceof com.android.volley.TimeoutError) {
                            Log.e("NetworkError", "Timeout occurred");
                            Toast.makeText(StudentMainPage.this, "İnternet bağlantınızı kontrol edin!", Toast.LENGTH_SHORT).show();
                        } else if (error.networkResponse != null) {
                            Log.e("NetworkError", "Status Code: " + error.networkResponse.statusCode);
                        }

                    }
                }

        ) {
            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                // Header bilgilerini buraya ekle
                Map<String, String> headers = new HashMap<>();
                headers.put("Content-Type", "application/json"); // JSON formatında gönderim
                headers.put("X-Api-Key", "c6533523ec7d0b5371a30269ef3d526064552c6f90f872618eebb14af33c2093"); // API anahtarını buraya ekle
                return headers;
            }
        };

        jsonObjectRequest.setRetryPolicy(new com.android.volley.DefaultRetryPolicy(
                30000, // Timeout süresi (ms cinsinden)
                DefaultRetryPolicy.DEFAULT_MAX_RETRIES, // Tekrar deneme sayısı
                DefaultRetryPolicy.DEFAULT_BACKOFF_MULT // Geri dönüş katsayısı
        ));

        // İsteği sıraya ekle
        requestQueue.add(jsonObjectRequest);
    }

    private ProgressDialog progressDialog;

    private void showLoader() {
        progressDialog = new ProgressDialog(StudentMainPage.this);
        progressDialog.setMessage("İşlem yapılıyor, lütfen bekleyin...");
        progressDialog.setCancelable(false); // Kullanıcı iptal edemez
        progressDialog.show();
    }


    private void hideLoader() {
        if (progressDialog != null && progressDialog.isShowing()) {
            progressDialog.dismiss();
        }
    }

    private void saveEndTime(long endTimeInMillis) {
        SecurePreferences securePreferences = SecurePreferences.getInstance(this);
        securePreferences.save("end_time", String.valueOf(endTimeInMillis));
    }

    private long getEndTime() {
        SecurePreferences securePreferences = SecurePreferences.getInstance(this);
        String endTime = securePreferences.get("end_time", "0");
        return Long.parseLong(endTime);
    }

    private void showProgressDialog(long remainingTimeInMillis) {
        // Sadece zamanlayıcı aktifse progress dialogu göster
        if (isCountdownActive) {
            ProgressDialog progressDialog = new ProgressDialog(StudentMainPage.this);
            progressDialog.setMessage("Kalan süre: " + formatTime(remainingTimeInMillis));
            progressDialog.setCancelable(false);
            progressDialog.show();

            // Geri sayım için zamanlayıcı başlat
            new CountDownTimer(remainingTimeInMillis, 1000) {
                @Override
                public void onTick(long millisUntilFinished) {
                    // Kalan süreyi güncelle
                    progressDialog.setMessage("Kalan süre: " + formatTime(millisUntilFinished));
                }

                @Override
                public void onFinish() {
                    progressDialog.dismiss();
                    // Süre bitince QR kod tarama ekranını aç
                    Intent intent = new Intent(StudentMainPage.this, CaptureActivity.class);
                    startActivityForResult(intent, REQUEST_CODE_SCAN);
                }
            }.start();
        }
    }

    // Zaman formatını dakika:saniye olarak döndüren metot
    private String formatTime(long millis) {
        long minutes = millis / 1000 / 60;
        long seconds = (millis / 1000) % 60;
        return String.format("%02d:%02d", minutes, seconds);
    }

}


