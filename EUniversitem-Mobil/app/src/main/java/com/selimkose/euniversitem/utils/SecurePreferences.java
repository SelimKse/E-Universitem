package com.selimkose.euniversitem.utils;

import android.content.Context;
import android.content.SharedPreferences;

import androidx.security.crypto.EncryptedSharedPreferences;
import androidx.security.crypto.MasterKey;

import java.io.IOException;
import java.security.GeneralSecurityException;

public class SecurePreferences {

    private static final String PREFS_NAME = "EncryptedUserPrefs";
    private static SecurePreferences instance;
    private final SharedPreferences sharedPreferences;

    // Singleton tasarımı
    private SecurePreferences(Context context) {
        try {
            MasterKey masterKey = new MasterKey.Builder(context)
                    .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
                    .build();

            sharedPreferences = EncryptedSharedPreferences.create(
                    context,
                    PREFS_NAME,
                    masterKey,
                    EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
                    EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
            );
        } catch (GeneralSecurityException | IOException e) {
            e.printStackTrace();
            throw new RuntimeException("EncryptedSharedPreferences oluşturulurken hata oluştu.");
        }
    }

    // Tek bir instance oluşturulur
    public static synchronized SecurePreferences getInstance(Context context) {
        if (instance == null) {
            instance = new SecurePreferences(context);
        }
        return instance;
    }

    // Veriyi kaydet
    public void save(String key, String value) {
        SharedPreferences.Editor editor = sharedPreferences.edit();
        editor.putString(key, value);
        editor.apply();
    }

    // Veriyi oku
    public String get(String key, String defaultValue) {
        return sharedPreferences.getString(key, defaultValue);
    }

    // Veriyi sil
    public void remove(String key) {
        SharedPreferences.Editor editor = sharedPreferences.edit();
        editor.remove(key);
        editor.apply();
    }

    // Tüm verileri sil
    public void clear() {
        SharedPreferences.Editor editor = sharedPreferences.edit();
        editor.clear();
        editor.apply();
    }
}
