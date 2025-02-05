package com.selimkose.euniversitem;

import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkCapabilities;
import android.os.Build;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import com.selimkose.euniversitem.utils.SecurePreferences;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // SecurePreferences ile giriş durumu kontrolü
        SecurePreferences securePreferences = SecurePreferences.getInstance(this);
        String ogrenciNo = securePreferences.get("ogrenci_no", null);
        String ogretmenNo = securePreferences.get("ogretmen_no", null);

        System.out.println(ogrenciNo);
        System.out.println(ogretmenNo);

        // Eğer öğrenci numarası varsa, doğrudan StudentMainPage'e yönlendir
        if (ogrenciNo != null) {
            Intent intent = new Intent(MainActivity.this, StudentMainPage.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK); // Yığındaki tüm aktiviteleri temizler
            startActivity(intent);
            finish();  // Bu Activity'i bitir
            return; // Geriye devam etme
        }

        // Eğer öğretmen numarası varsa, doğrudan TeacherMainPage'e yönlendir
        if (ogretmenNo != null) {
            Intent intent = new Intent(MainActivity.this, TeacherMainPage.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK); // Yığındaki tüm aktiviteleri temizler
            startActivity(intent);
            finish();  // Bu Activity'i bitir
            return; // Geriye devam etme
        }


        // Öğrenci Girişi Butonu
        Button studentButton = findViewById(R.id.studentButton);
        Button teacherButton = findViewById(R.id.teacherButton);

        // İnternet kontrolü
        if (!isInternetAvailable()) {
            studentButton.setEnabled(false);
            teacherButton.setEnabled(false);
            showNoInternetDialog();
        } else {
            studentButton.setEnabled(true);
            teacherButton.setEnabled(true);

            studentButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    Intent intent = new Intent(MainActivity.this, StudentLogin.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK); // Yığındaki tüm aktiviteleri temizler
                    startActivity(intent);
                    finish();  // Bu Activity'i bitir
                }
            });

            teacherButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    Intent intent = new Intent(MainActivity.this, TeacherLogin.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK); // Yığındaki tüm aktiviteleri temizler
                    startActivity(intent);
                    finish();  // Bu Activity'i bitir
                }
            });

        }
    }

    @Override
    public void onBackPressed() {
        new AlertDialog.Builder(this)
                .setMessage("Çıkmak istediğinizden emin misiniz?")
                .setCancelable(false)
                .setPositiveButton("Evet", new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int id) {
                        finishAffinity();
                    }
                })
                .setNegativeButton("Hayır", null)
                .show();
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
        new AlertDialog.Builder(MainActivity.this)
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
}