package com.selimkose.euniversitem;

import android.app.AlertDialog;
import android.app.DatePickerDialog;
import android.app.ProgressDialog;
import android.app.TimePickerDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.Configuration;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TimePicker;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.AuthFailureError;
import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.selimkose.euniversitem.utils.SecurePreferences;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class TeacherMainPage extends AppCompatActivity {
    EditText dateTimeEditText;
    Button yoklamaBaslatButton;
    Map<String, Integer> dersMap = new HashMap<>();
    Map<String, String> sinifMap = new HashMap<>();



    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.teacher_main_page);

        // SecurePreferences'dan veriyi çekme
        SecurePreferences securePreferences = SecurePreferences.getInstance(this);
        String ogretmen_adi = securePreferences.get("ogretmen_adi", "Ad");
        String ogretmne_soyadi = securePreferences.get("ogretmen_soyadi", "Soyad");
        String ogretmen_no = securePreferences.get("ogretmen_no", "ogretmen_no");

        // İsim ve soyismi birleştirme
        String fullName = ogretmen_adi + " " + ogretmne_soyadi;

        // Toast ile yazdırma
        Toast.makeText(this, "Merhaba " + fullName + " 👋", Toast.LENGTH_LONG).show();

        // Çıkış butonunu tanımla
        ImageButton logoutButton = findViewById(R.id.logoutButton);
        logoutButton.setOnClickListener(view -> {
            // alert dialog oluştur
            AlertDialog.Builder builder = new AlertDialog.Builder(TeacherMainPage.this);
            builder.setTitle("Çıkış Yap");
            builder.setMessage("Çıkış yapmayı onaylıyor musunuz?");
            builder.setNegativeButton("Hayır", null);
            builder.setPositiveButton("Evet", new DialogInterface.OnClickListener() {
                public void onClick(DialogInterface dialog, int id) {
                    securePreferences.clear();
                    Intent intent = new Intent(TeacherMainPage.this, MainActivity.class);
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

        dateTimeEditText = findViewById(R.id.dateTimeEditText);

        dateTimeEditText.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                showDateTimePicker();
            }
        });

        // readonly textbox'ı tanımla
        EditText readonlyTextBox = findViewById(R.id.readonlyTextBox);

        readonlyTextBox.setText(fullName);

        // apiden dersleri al
        getDersler(ogretmen_no);

        // apiden sınıfları al
        getSınıflar();

        yoklamaBaslatButton = findViewById(R.id.yoklamaBaslatButton);

        // Tıklama olayını ayarlayın
        yoklamaBaslatButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Ders spinner'ını al
                Spinner spinnerDersler = findViewById(R.id.courseSpinner);
                // Sınıf spinner'ını al
                Spinner spinnerSınıflar = findViewById(R.id.classSpinner);

                // Spinner'dan seçilen ders adını al
                String selectedDersAdi = (String) spinnerDersler.getSelectedItem();

                // Ders ID'sini almak için dersMap'ten ders adını kullanarak ID'yi al
                Integer selectedDersId = dersMap.get(selectedDersAdi);

                // Spinner'dan seçilen sınıfı al
                String selectedSinifAdi = (String) spinnerSınıflar.getSelectedItem();

                // Sınıf ID'sini almak için sinifMap'ten sınıf adını kullanarak ID'yi al
                String selectedSinifId = sinifMap.get(selectedSinifAdi);

                System.out.println(selectedDersId);
                System.out.println(selectedSinifId);
                yoklamaBaslat(String.valueOf(selectedDersId), selectedSinifId, dateTimeEditText.getText().toString());
            }
        });



    }

    // API'den ders verilerini alacak fonksiyon
    private void getDersler(String ogretmen_no) {
        showLoader();
        String url = "https://api.e-universitem.com/ogretmenler/dersler"; // API URL'sini yaz
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        // JSON objesi oluştur
        JSONObject params = new JSONObject();
        try {
            params.put("ogretmen_no", ogretmen_no);
        } catch (JSONException e) {
            e.printStackTrace();
            Toast.makeText(TeacherMainPage.this, "JSON oluşturulurken hata oluştu!", Toast.LENGTH_SHORT).show();
            return;
        }

        // Özelleştirilmiş JsonObjectRequest
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.POST,
                url,
                params,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {

                        try {
                            // Gelen "data" kısmındaki dersleri alıyoruz
                            String status = response.getString("status");
                            if ("success".equals(status)) {
                                // Ders verileri geldi, listeyi spinner'a ekle
                                JSONArray derslerArray = response.getJSONArray("data");

                                for (int i = 0; i < derslerArray.length(); i++) {
                                    JSONObject ders = derslerArray.getJSONObject(i);
                                    String dersAdi = ders.getString("ders_adi");
                                    int dersId = ders.getInt("ders_id");
                                    dersMap.put(dersAdi, dersId);  // Ders adı -> ID şeklinde ekliyoruz
                                }

                                // Spinner'ı güncelle
                                updateDersSpinner(dersMap);
                            } else {
                                // Başarısız durumu kontrol et
                                showErrorDialog("Dersler alınamadı", "Ders verileri alınırken bir hata oluştu.");
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                            showErrorDialog("Yanıt işlenirken hata oluştu", "Yanıt işlenirken hata oluştu!");
                        }

                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {

                        // Ağ hatası durumunda networkResponse varsa
                        if (error.networkResponse != null) {
                            int statusCode = error.networkResponse.statusCode;
                            String errorData = new String(error.networkResponse.data);

                            try {
                                // Gelen veriyi JSONObject formatında işleyelim
                                JSONObject errorResponse = new JSONObject(errorData);

                                // "message" alanı array olabiliyor, onu kontrol edelim
                                JSONArray messageArray = errorResponse.optJSONArray("message");

                                // Eğer message array varsa, ilk mesajı alalım
                                if (messageArray != null && messageArray.length() > 0) {
                                    String errorMessage = messageArray.getString(0);

                                    // Hata mesajını kullanıcıya gösterelim
                                    showAlertDialog("Hata", errorMessage);
                                } else {
                                    // Eğer "message" yoksa, varsayılan bir hata mesajı gösterelim
                                    String errorMessage = errorResponse.optString("message", "Bir hata oluştu!");
                                    showAlertDialog("Hata", errorMessage);
                                }

                            } catch (JSONException e) {
                                // JSON parse hatası durumunda genel hata mesajı
                                showAlertDialog("Hata", "Yanıt işlenirken hata oluştu!");
                                e.printStackTrace();  // Debugging için log yazdırma
                            }


                        } else {
                            // Ağ hatası durumunda
                            String networkError = error.getMessage() != null ? error.getMessage() : "Bir ağ hatası oluştu.";
                            showAlertDialog("Ağ Hatası", networkError);
                        }

                        // Hata kodunu ve mesajı konsola yazdırmak isteyebilirsiniz
                        Log.e("VolleyError", "Hata kodu: " + (error.networkResponse != null ? error.networkResponse.statusCode : "Bilinmiyor"));
                        Log.e("VolleyError", "Hata Mesajı: " + error.getMessage());
                    }

                    // Hata mesajını gösteren metod
                    private void showAlertDialog(String title, String message) {
                        AlertDialog.Builder builder = new AlertDialog.Builder(TeacherMainPage.this);
                        builder.setTitle(title);
                        builder.setMessage(message);
                        builder.setPositiveButton("Tamam", null);
                        builder.show();
                    }
                }
        ) {
            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                // Header bilgileri buraya eklenebilir
                Map<String, String> headers = new HashMap<>();
                headers.put("Content-Type", "application/json");
                headers.put("X-Api-Key", "c6533523ec7d0b5371a30269ef3d526064552c6f90f872618eebb14af33c2093");
                return headers;
            }
        };

        jsonObjectRequest.setRetryPolicy(new DefaultRetryPolicy(
                30000, // Timeout süresi (ms cinsinden)
                DefaultRetryPolicy.DEFAULT_MAX_RETRIES, // Tekrar deneme sayısı
                DefaultRetryPolicy.DEFAULT_BACKOFF_MULT // Geri dönüş katsayısı
        ));

        // İsteği sıraya ekle
        requestQueue.add(jsonObjectRequest);
        hideLoader();
    }

    // API'den ders verilerini alacak fonksiyon
    private void getSınıflar() {
        showLoader();
        String url = "https://api.e-universitem.com/siniflar"; // API URL'sini yaz
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        StringRequest stringRequest = new StringRequest(
                Request.Method.GET,
                url,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {

                        try {
                            // Gelen yanıtı JSON objesine dönüştürüyoruz
                            JSONObject jsonResponse = new JSONObject(response);

                            // "status" alanını kontrol et
                            String status = jsonResponse.getString("status");
                            if ("success".equalsIgnoreCase(status)) {
                                // "data" alanından sınıf bilgilerini alıyoruz
                                JSONArray sınıflarArray = jsonResponse.getJSONArray("data");

                                List<String> sınıfİsimleri = new ArrayList<>();
                                for (int i = 0; i < sınıflarArray.length(); i++) {
                                    JSONObject sınıf = sınıflarArray.getJSONObject(i);
                                    String sinifAdi = sınıf.getString("sinif_adi");
                                    String sinifId = sınıf.getString("sinif_id"); // ID'yi alıyoruz
                                    sinifMap.put(sinifAdi, sinifId); // Sınıf adı -> ID şeklinde ekliyoruz
                                    sınıfİsimleri.add(sinifAdi); // Sınıf isimlerini spinner'a eklemek için listeye ekliyoruz
                                }

                                // Spinner'ı güncelle
                                updateSinifSpinner(sınıfİsimleri);
                            } else {
                                showErrorDialog("Sınıflar alınamadı", "Sınıf verileri alınırken bir hata oluştu.");
                            }
                        } catch (JSONException e) {
                            // JSON işleme hatası
                            e.printStackTrace();
                            showErrorDialog("Hata", "Yanıt işlenirken hata oluştu!");
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {

                        // Ağ hatası durumunda detaylı hata kontrolü
                        if (error.networkResponse != null) {
                            int statusCode = error.networkResponse.statusCode;
                            String errorData = new String(error.networkResponse.data);

                            try {
                                String errorMessage = new JSONObject(errorData).getString("message");
                                showErrorDialog("Hata", errorMessage);
                            } catch (JSONException e) {
                                showErrorDialog("Hata", "Yanıt işlenirken hata oluştu!");
                                e.printStackTrace();
                            }
                        } else {
                            // Genel ağ hatası mesajı
                            String networkError = error.getMessage() != null ? error.getMessage() : "Bir ağ hatası oluştu.";
                            showErrorDialog("Ağ Hatası", networkError);
                        }
                    }
                }
        ) {
            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                // Header bilgileri ekleyin (örneğin API anahtarı)
                Map<String, String> headers = new HashMap<>();
                headers.put("Content-Type", "application/json");
                headers.put("X-Api-Key", "c6533523ec7d0b5371a30269ef3d526064552c6f90f872618eebb14af33c2093");
                return headers;
            }
        };

        stringRequest.setRetryPolicy(new DefaultRetryPolicy(
                10000, // Timeout süresi (ms cinsinden)
                DefaultRetryPolicy.DEFAULT_MAX_RETRIES, // Tekrar deneme sayısı
                DefaultRetryPolicy.DEFAULT_BACKOFF_MULT // Geri dönüş katsayısı
        ));

        // İsteği sıraya ekle
        requestQueue.add(stringRequest);
        hideLoader();
    }

    private void yoklamaBaslat(String dersID, String sınıfID, String baslangicTarihi) {
        showLoader();  // Loader gösterildiği an
        String url = "https://api.e-universitem.com/yoklamalar/olustur"; // API URL'sini güncelledik
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        // SecurePreferences'dan veriyi çekme
        SecurePreferences securePreferences = SecurePreferences.getInstance(this);
        String ogretmen_no = securePreferences.get("ogretmen_no", "ogretmen_no");

        // JSON objesi oluştur
        JSONObject params = new JSONObject();
        try {
            params.put("ders_id", dersID);
            params.put("sinif_id", sınıfID);
            params.put("baslatilma_tarihi", baslangicTarihi);
            params.put("ogretmen_no", ogretmen_no);
        } catch (JSONException e) {
            e.printStackTrace();
            Toast.makeText(TeacherMainPage.this, "JSON oluşturulurken hata oluştu!", Toast.LENGTH_SHORT).show();
            hideLoader();  // Hata durumunda loader'ı gizle
            return;
        }

        // Özelleştirilmiş JsonObjectRequest
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.POST,
                url,
                params,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                            try {
                                // Gelen "data" kısmındaki dersleri alıyoruz
                                String status = response.getString("status");
                                if ("success".equals(status)) {
                                    AlertDialog.Builder builder = new AlertDialog.Builder(TeacherMainPage.this);
                                    builder.setTitle(response.getString("message"));
                                    builder.setMessage(
                                            "Özel Kod: " + response.getString("ozel_kod") +
                                            "\n\nBaşlangıç Tarihi: " + baslangicTarihi
                                    );
                                    builder.setPositiveButton("Tamam", null); // Kullanıcı "Tamam" butonuna tıkladığında kapanır

                                    // Dialog'u göster
                                    builder.show();

                                    hideLoader();  // Başarılı durumunda loader'ı gizle
                                    return;
                                } else {
                                    // Başarısız durumu kontrol et
                                    showErrorDialog("Hata", "Yoklamma başlatılırken bir hata oluştu.");
                                }
                            } catch (JSONException e) {
                                e.printStackTrace();
                                showErrorDialog("Yanıt işlenirken hata oluştu", "Yanıt işlenirken hata oluştu!");
                                System.out.println("Hata oluştu" + e.getMessage());
                            }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        hideLoader(); // Hata durumunda loader'ı gizle
                        handleVolleyError(error);
                    }

                    private void handleVolleyError(VolleyError error) {
                        // Ağ hatası durumunda networkResponse varsa
                        if (error.networkResponse != null) {
                            int statusCode = error.networkResponse.statusCode;
                            String errorData = new String(error.networkResponse.data);

                            try {
                                // Gelen veriyi JSONObject formatında işleyelim
                                JSONObject errorResponse = new JSONObject(errorData);

                                // "message" alanı array olabiliyor, onu kontrol edelim
                                JSONArray messageArray = errorResponse.optJSONArray("message");

                                // Eğer message array varsa, ilk mesajı alalım
                                if (messageArray != null && messageArray.length() > 0) {
                                    String errorMessage = messageArray.getString(0);
                                    showAlertDialog("Hata", errorMessage);
                                } else {
                                    // Eğer "message" yoksa, varsayılan bir hata mesajı gösterelim
                                    String errorMessage = errorResponse.optString("message", "Bir hata oluştu!");
                                    showAlertDialog("Hata", errorMessage);
                                }
                            } catch (JSONException e) {
                                // JSON parse hatası durumunda genel hata mesajı
                                showAlertDialog("Hata", "Yanıt işlenirken hata oluştu!");
                            }
                        } else {
                            // Ağ hatası durumunda
                            String networkError = error.getMessage() != null ? error.getMessage() : "Bir ağ hatası oluştu.";
                            showAlertDialog("Ağ Hatası", networkError);
                        }
                    }
 
                    // Hata mesajını gösteren metod
                    private void showAlertDialog(String title, String message) {
                        AlertDialog.Builder builder = new AlertDialog.Builder(TeacherMainPage.this);
                        builder.setTitle(title);
                        builder.setMessage(message);
                        builder.setPositiveButton("Tamam", null);
                        builder.show();
                    }
                }
        ) {
            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                // Header bilgileri buraya eklenebilir
                Map<String, String> headers = new HashMap<>();
                headers.put("Content-Type", "application/json");
                headers.put("X-Api-Key", "c6533523ec7d0b5371a30269ef3d526064552c6f90f872618eebb14af33c2093");
                return headers;
            }
        };

        jsonObjectRequest.setRetryPolicy(new DefaultRetryPolicy(
                30000, // Timeout süresi (ms cinsinden)
                DefaultRetryPolicy.DEFAULT_MAX_RETRIES, // Tekrar deneme sayısı
                DefaultRetryPolicy.DEFAULT_BACKOFF_MULT // Geri dönüş katsayısı
        ));

        // İsteği sıraya ekle
        requestQueue.add(jsonObjectRequest);
    }


    // Spinner'ı derslerle güncelleme
    private void updateDersSpinner(Map<String, Integer> dersMap) {
        Spinner spinner = findViewById(R.id.courseSpinner);
        List<String> dersList = new ArrayList<>(dersMap.keySet());
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, dersList);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinner.setAdapter(adapter);
    }

    // Spinner'ı sınıflarla güncelleme
    private void updateSinifSpinner(List<String> sinifList) {
        Spinner spinner = findViewById(R.id.classSpinner);
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, sinifList);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinner.setAdapter(adapter);
    }




    // Hata mesajı göstermek için bir AlertDialog fonksiyonu
    private void showErrorDialog(String title, String message) {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle(title);
        builder.setMessage(message);
        builder.setPositiveButton("Tamam", null);
        builder.show();
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

    private void showDateTimePicker() {
        // Tarih ve saati seçmek için Calendar objesi oluşturuyoruz
        final Calendar calendar = Calendar.getInstance();

        int year = calendar.get(Calendar.YEAR);
        int month = calendar.get(Calendar.MONTH);
        int day = calendar.get(Calendar.DAY_OF_MONTH);

        // Tarih seçimi
        DatePickerDialog datePickerDialog = new DatePickerDialog(
                TeacherMainPage.this,
                new DatePickerDialog.OnDateSetListener() {
                    @Override
                    public void onDateSet(DatePicker view, int year, int monthOfYear, int dayOfMonth) {
                        calendar.set(year, monthOfYear, dayOfMonth);
                        showTimePicker(calendar);
                    }
                }, year, month, day);
        datePickerDialog.show();
    }

    private void showTimePicker(final Calendar calendar) {
        int hour = calendar.get(Calendar.HOUR_OF_DAY);
        int minute = calendar.get(Calendar.MINUTE);

        // Saat seçimi
        TimePickerDialog timePickerDialog = new TimePickerDialog(
                TeacherMainPage.this,
                new TimePickerDialog.OnTimeSetListener() {
                    @Override
                    public void onTimeSet(TimePicker view, int hourOfDay, int minute) {
                        calendar.set(Calendar.HOUR_OF_DAY, hourOfDay);
                        calendar.set(Calendar.MINUTE, minute);
                        // Şu şekilde formatlıyoruz: yyyy-MM-dd HH:mm:ss
                        String formattedDateTime = formatDate(calendar);
                        dateTimeEditText.setText(formattedDateTime);
                    }
                }, hour, minute, false);
        timePickerDialog.show();
    }

    private String formatDate(Calendar calendar) {
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return sdf.format(calendar.getTime());
    }

    private ProgressDialog progressDialog;

    private void showLoader() {
        progressDialog = new ProgressDialog(TeacherMainPage.this);
        progressDialog.setMessage("İşlem yapılıyor, lütfen bekleyin...");
        progressDialog.setCancelable(false); // Kullanıcı iptal edemez
        progressDialog.show();
    }


    private void hideLoader() {
        if (progressDialog != null && progressDialog.isShowing()) {
            progressDialog.dismiss();
        }
    }

}
