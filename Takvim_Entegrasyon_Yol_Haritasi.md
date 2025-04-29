# Google ve Notion Takvim Entegrasyonu Yol Haritası

## Genel Bakış

Bu yol haritası, mevcut AkilliAjanda uygulamasına Google Takvim ve Notion Takvim entegrasyonu eklemek için adımları detaylandırmaktadır. Entegrasyon, kullanıcıların Google veya Notion hesapları ile giriş yaparak takvimlere erişim izni vermesini ve bu takvimlerle Event.php ve Task.php modellerinin senkronizasyonunu sağlayacaktır.

## 1. Gerekli Paketlerin Kurulumu

```bash
composer require spatie/laravel-google-calendar
php artisan vendor:publish --provider="Spatie\GoogleCalendar\GoogleCalendarServiceProvider"

```

## 2. Google API Yapılandırması

### 2.1 Google Developer Console'da Proje Oluşturma
- [Google Cloud Console](https://console.cloud.google.com/) üzerinden yeni bir proje oluşturun
- Calendar API'yi etkinleştirin
- OAuth 2.0 kimlik bilgilerini oluşturun
- Yönlendirme URI'larını ayarlayın (örn: `https://sizin-domain.com/auth/google/callback`)
- Client ID ve Client Secret bilgilerini alın

### 2.2 Laravel'de Google API Yapılandırması
- `.env` dosyasında Google API kimlik bilgilerini tanımlayın:
```
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=https://your-domain.com/auth/google/callback
```


## 4. Veritabanı Şeması Güncellemeleri

### 4.1 Kullanıcı Tablosuna Sütun Ekleme
```php
Schema::table('users', function (Blueprint $table) {
    $table->text('google_token')->nullable();
});
```

### 4.2 Event ve Task Modellerine Entegrasyon Bilgileri Ekleme
```php
Schema::table('events', function (Blueprint $table) {
    $table->string('google_event_id')->nullable();
    $table->boolean('synced_to_google')->default(false);

});

Schema::table('tasks', function (Blueprint $table) {
    $table->string('google_task_id')->nullable();
    $table->boolean('synced_to_google')->default(false);
});
```

## 5. Servis Sınıfları Oluşturma

### 5.1 Google Takvim Servisi Oluşturma
- `app/Services/GoogleCalendarService.php` sınıfını oluşturun
- Temel işlemler: Kimlik doğrulama, olay listeleme, oluşturma, güncelleme, silme


### 5.3 Senkronizasyon Servisi Oluşturma
- `app/Services/CalendarSyncService.php` sınıfını oluşturun
- İki yönlü senkronizasyon mantığını uygulayın

## 6. Controller ve Route'ların Oluşturulması

### 6.1 Auth Controller Güncelleme
- Google ile oturum açma metotları ekleyin
- Callback işleyicileri oluşturun

### 6.2 Senkronizasyon Controller'ı Oluşturma
- `app/Http/Controllers/CalendarSyncController.php` oluşturun
- Manuel senkronizasyon ve ayarları yönetme işlevleri ekleyin

### 6.3 Route'ları Tanımlama
```php
// routes/web.php
Route::middleware(['ensure.auth'])->group(function () {
    // Auth Routes
    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

    
    // Sync Routes
    Route::get('/calendar/sync', [CalendarSyncController::class, 'syncPage']);
    Route::post('/calendar/sync/google', [CalendarSyncController::class, 'syncWithGoogle']);
});
```

## 7. Manuel Senkronizasyon 
    - Profil sayfasında bağlı servisler için içe/dışa aktar butonlarıyla işlemler yapılsın içe aktar denilince ilgili servisten alınan takvim verileri dbye kaydedilsin dışa aktar denilince dbdeki etkinlik ve tasklar ilgili servisle eşitlensin

## 8. Arayüz Güncellemeleri

### 8.1 Profil Sayfasına Entegrasyon Ayarları Ekleme
- Kullanıcı profil sayfasına Google bağlantı seçenekleri ekleyin
- Hesap bağlama ve ayırma işlevleri oluşturun

### 8.2 Takvim Senkronizasyon Durumu Gösterme
- Etkinlik ve görevlerin senkronizasyon durumunu gösteren arayüz elemanları ekleyin
- Manuel senkronizasyon düğmeleri ekleyin
