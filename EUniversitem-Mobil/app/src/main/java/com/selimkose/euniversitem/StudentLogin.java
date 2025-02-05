package com.selimkose.euniversitem;

import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkCapabilities;
import android.os.Build;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.AuthFailureError;
import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.selimkose.euniversitem.utils.SecurePreferences;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

public class StudentLogin extends AppCompatActivity {

    EditText username;
    EditText password;
    Button loginButton;
    Button teacherButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {

        if (!isInternetAvailable()) {
            showNoInternetDialog();
        }

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_student_login);

        username = findViewById(R.id.username);
        password = findViewById(R.id.password);
        loginButton = findViewById(R.id.loginButton);
        teacherButton = findViewById(R.id.loginButtonTeacher);

        loginButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String usernameText = username.getText().toString().trim();
                String passwordText = password.getText().toString().trim();

                // Boş alan kontrolü
                if (usernameText.isEmpty()) {
                    username.setBackgroundResource(R.drawable.error_edittext); // Hatalı durum
                }

                if (passwordText.isEmpty()) {
                    password.setBackgroundResource(R.drawable.error_edittext); // Hatalı durum
                }

                if (!usernameText.isEmpty() && !passwordText.isEmpty()) {
                    username.setBackgroundResource(R.drawable.custom_edittext);
                    password.setBackgroundResource(R.drawable.custom_edittext);
                    loginStudent(usernameText, passwordText);
                } else {
                    Toast.makeText(StudentLogin.this, "Lütfen gerekli alanları doldurun!", Toast.LENGTH_SHORT).show();
                }

            }
        });

        teacherButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // Yeni intent oluşturuluyor (TeacherLogin'e gitmek için)
                Intent intent = new Intent(StudentLogin.this, TeacherLogin.class);

                // Intent ile eski aktivitelerin temizlenmesi için aşağıdaki flag'leri ekliyoruz
                intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);

                // Yeni aktivite başlatılıyor
                startActivity(intent);

                // Bu activity'yi bitiriyoruz (aktif olan activity'nin sonlandırılması için)
                finish();
            }
        });

    }

    @Override
    public void onBackPressed() {
        Intent intent = new Intent(this, MainActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK); // Eski aktiviteleri temizle
        startActivity(intent);
        finish();
    }



    private void loginStudent(String usernameText, String passwordText) {
        String url = "https://api.e-universitem.com/login/ogrenci/giris"; // Buraya kendi API URL'ini yaz
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        showLoader();

        // JSON objesi oluştur
        JSONObject loginParams = new JSONObject();
        try {
            loginParams.put("ogrenci_no", usernameText);
            loginParams.put("ogrenci_sifre", passwordText);
        } catch (JSONException e) {
            e.printStackTrace();
            Toast.makeText(StudentLogin.this, "JSON oluşturulurken hata oluştu!", Toast.LENGTH_SHORT).show();
            hideLoader();
            return;
        }

        // Özelleştirilmiş JsonObjectRequest
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.POST,
                url,
                loginParams,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                        hideLoader();

                        try {
                            // "data" kısmını almak
                            JSONObject dataObject = response.getJSONObject("data");
                            SecurePreferences securePreferences = SecurePreferences.getInstance(StudentLogin.this);

                            for (Iterator<String> it = dataObject.keys(); it.hasNext(); ) {
                                String key = it.next();
                                Object value = dataObject.get(key);

                                // Değerin türünü kontrol et ve yalnızca String olanları kaydet
                                if (value instanceof String) {
                                    securePreferences.save(key, (String) value);
                                } else if (value instanceof JSONObject || value instanceof JSONArray) {
                                    // JSON veya JSONArray ise, bunu String'e dönüştürüp kaydet
                                    securePreferences.save(key, value.toString());
                                } else {
                                    // Diğer türlerde ise, value.toString() ile güvenli bir dönüşüm yap
                                    securePreferences.save(key, value.toString());
                                }
                            }

                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                        showLoader();

                        Intent intent = new Intent(StudentLogin.this, StudentMainPage.class);
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                        startActivity(intent);
                        finish();

                        hideLoader();

                        Toast.makeText(StudentLogin.this, "Giriş Başarılı", Toast.LENGTH_SHORT).show();
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        hideLoader();

                        if (error.networkResponse != null) {
                            int statusCode = error.networkResponse.statusCode;
                            String errorData = new String(error.networkResponse.data);

                            if(statusCode == 401) {
                                Toast.makeText(StudentLogin.this, "Öğrenci No veya Şifre Hatalı!", Toast.LENGTH_SHORT).show();
                            }

                            if (statusCode == 500) {
                                Toast.makeText(StudentLogin.this, "Sunucu Hatası!", Toast.LENGTH_SHORT).show();
                            }

                            System.out.println("Hata kodu:" + statusCode);
                            System.out.println("Hata Bilgi:" + errorData);
                        } else {
                            System.out.println("Hata:" + error.getMessage());
                        }

                        if (error instanceof com.android.volley.TimeoutError) {
                            Toast.makeText(StudentLogin.this, "İnternet bağlantınızı kontrol edin!", Toast.LENGTH_SHORT).show();
                        } else {
                            Toast.makeText(StudentLogin.this, "Bir hata oluştu!", Toast.LENGTH_SHORT).show();
                        }
                    }
                }

        )
        {
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

    // Burada metodun doğru bir şekilde tanımlandığından emin olun.
    private boolean isInternetAvailable() {
        ConnectivityManager connectivityManager =
                (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);

        if (connectivityManager != null) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                NetworkCapabilities capabilities =
                        connectivityManager.getNetworkCapabilities(connectivityManager.getActiveNetwork());
                if (capabilities != null) {
                    return capabilities.hasTransport(NetworkCapabilities.TRANSPORT_WIFI) ||
                            capabilities.hasTransport(NetworkCapabilities.TRANSPORT_CELLULAR) ||
                            capabilities.hasTransport(NetworkCapabilities.TRANSPORT_ETHERNET);
                }
            } else {
                // Eski cihazlar için
                android.net.NetworkInfo networkInfo = connectivityManager.getActiveNetworkInfo();
                return networkInfo != null && networkInfo.isConnected();
            }
        }
        return false;
    }

    // Internet bağlantısının olmadığını bildiren dialog
    private void showNoInternetDialog() {
        new AlertDialog.Builder(StudentLogin.this)
                .setTitle("Bağlantı Hatası")
                .setMessage("İnternet bağlantınız yok. Lütfen bağlandıktan sonra tekrar deneyin.")
                .setCancelable(false) // Dışarıya tıklanamayacak
                .setPositiveButton("Tamam", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        finish(); // Uygulama kapanır
                    }
                })
                .show();
    }

    private ProgressDialog progressDialog;

    private void showLoader() {
        progressDialog = new ProgressDialog(StudentLogin.this);
        progressDialog.setMessage("Giriş yapılıyor, lütfen bekleyin...");
        progressDialog.setCancelable(false); // Kullanıcı iptal edemez
        progressDialog.show();
    }

    private void hideLoader() {
        if (progressDialog != null && progressDialog.isShowing()) {
            progressDialog.dismiss();
        }
    }
}