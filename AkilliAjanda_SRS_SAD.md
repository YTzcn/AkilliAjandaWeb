# Akıllı Ajanda Projesi: Yazılım Gereksinim Şartnamesi (SRS) ve Yazılım Mimari Tasarımı (SAD)

## Bölüm 1: Yazılım Gereksinim Şartnamesi (SRS)

### 1. Giriş

#### 1.1. Amaç
Bu dokümanın amacı, "Akıllı Ajanda" adlı web ve potansiyel mobil uygulamanın yazılım gereksinimlerini ve mimari tasarımını kapsamlı bir şekilde tanımlamaktır. Proje, kullanıcıların günlük etkinliklerini, görevlerini yönetmelerine, Google Takvim ile senkronize olmalarına ve yapay zeka destekli özelliklerden faydalanmalarına olanak tanır.

#### 1.2. Kapsam
Bu doküman, Akıllı Ajanda uygulamasının aşağıdaki bileşenlerini kapsar:
*   Web Uygulaması (Laravel tabanlı backend ve frontend)
*   API (Mobil uygulama ve harici servisler için)
*   Google Takvim entegrasyonu
*   Pusher ile gerçek zamanlı bildirimler
*   Google Gemini API ile LLM (Büyük Dil Modeli) entegrasyonu
*   Potansiyel React Native tabanlı Mobil Uygulama (tasarım detayları `react-native-mobil-design-prd.md` dosyasında belirtilmiştir)

#### 1.3. Tanımlar ve Kısaltmalar
*   **SRS**: Yazılım Gereksinim Şartnamesi
*   **SAD**: Yazılım Mimari Tasarımı
*   **LLM**: Büyük Dil Modeli (Large Language Model)
*   **API**: Uygulama Programlama Arayüzü (Application Programming Interface)
*   **CRUD**: Oluşturma, Okuma, Güncelleme, Silme (Create, Read, Update, Delete)
*   **UI**: Kullanıcı Arayüzü (User Interface)
*   **UX**: Kullanıcı Deneyimi (User Experience)
*   **FCM**: Firebase Cloud Messaging

### 2. Genel Açıklama

#### 2.1. Ürün Perspektifi
Akıllı Ajanda, kullanıcıların kişisel ve iş yaşamlarını daha etkin bir şekilde planlamalarına yardımcı olmak üzere tasarlanmış bağımsız bir web ve mobil uygulamadır. Google Takvim, Pusher ve Google Gemini gibi harici servislerle entegre çalışır.

#### 2.2. Ürün Fonksiyonları (Özet)
Uygulamanın temel fonksiyonları şunlardır:
*   Kapsamlı Kullanıcı Yönetimi (Kayıt, Giriş, Profil, Google ile Bağlantı)
*   Etkinlik Yönetimi (CRUD, takvim görünümü)
*   Görev Yönetimi (CRUD, önceliklendirme, kategorizasyon, tamamlama durumu)
*   Google Takvim ile Çift Yönlü Senkronizasyon (Etkinlikler ve Görevler)
*   LLM Destekli Akıllı Özellikler (Doğal dil ile etkinlik/görev oluşturma ve sorgulama)
*   Gerçek Zamanlı Bildirimler (Yaklaşan etkinlikler, vadesi gelen görevler)
*   Mesajlaşma Sistemi (LLM etkileşim logları ve analizleri)
*   Kullanıcı Dostu Dashboard

#### 2.3. Kullanıcı Karakteristikleri
Hedef kitle, yoğun bir programa sahip olan, zamanını verimli kullanmak isteyen ve teknolojiye yatkın bireyler ve profesyonellerdir. Temel bilgisayar ve akıllı telefon kullanım becerisine sahip olmaları beklenir.

#### 2.4. Genel Kısıtlamalar
*   Uygulama Laravel 12 ve PHP 8.2+ kullanılarak geliştirilmiştir.
*   Harici API'lere (Google Calendar, Google Gemini, Pusher) bağımlılık bulunmaktadır.
*   Mobil uygulama için React Native CLI ve JavaScript kullanılmaktadır

#### 2.5. Varsayımlar ve Bağımlılıklar
*   Kullanıcıların geçerli bir Google hesabına sahip olması Google Takvim entegrasyonu için gereklidir.
*   Harici API servislerinin (Google, Pusher, Gemini) çalışır durumda ve erişilebilir olduğu varsayılır.
*   İnternet bağlantısı, uygulamanın tüm özelliklerinin (özellikle senkronizasyon ve LLM) çalışması için gereklidir.

### 3. Özel Gereksinimler (Fonksiyonel Gereksinimler)

### 5. Arayüz Gereksinimleri (Genel)

#### 5.1. Web Arayüzü
*   `routes/web.php` dosyasında tanımlanan rotalara göre temel ekranlar:
    *   Giriş, Kayıt, Şifremi Unuttum
    *   Dashboard
    *   Etkinlikler (Liste, Oluştur, Düzenle, Detay)
    *   Görevler (Liste, Oluştur, Düzenle, Detay)
    *   Profil (Düzenle)
    *   Mesajlar (LLM Etkileşim Logları)
    *   Takvim Senkronizasyon Sayfası (`calendar.sync`) ve Ayarları (`calendar.settings`)
*   Stil için Tailwind CSS ve etkileşim için Alpine.js kullanılmaktadır.

#### 5.2. API Arayüzü
*   `routes/api.php` dosyasında tanımlanan RESTful API endpoint'leri.
*   İstek ve yanıt formatı JSON.
*   Kimlik doğrulama Laravel Sanctum ile Bearer Token.
*   API dokümantasyonu `darkaonline/l5-swagger` ile sağlanmaktadır.

#### 5.3. Mobil Arayüz (React Native)
    *   Giriş Sayfası
    *   Kayıt Sayfası
    *   Şifremi Unuttum Sayfası
    *   Profil Sayfası
    *   Takvim Senkronizazyon Sayfası


## Bölüm 2: Yazılım Mimari Tasarımı (SAD)

### 1. Giriş

#### 1.1. Amaç
Bu bölüm, Akıllı Ajanda uygulamasının yazılım mimarisini, ana bileşenlerini, aralarındaki ilişkileri ve kullanılan teknolojileri tanımlar.

#### 1.2. Kapsam
Bu SAD, SRS'te tanımlanan gereksinimleri karşılamak üzere tasarlanmış sistemin yapısını açıklar.

### 2. Mimari Temsili ve Stili
Akıllı Ajanda projesi, **Katmanlı Mimari (Layered Architecture)** ve **Model-View-Controller (MVC)** tasarım desenlerini temel almaktadır. Laravel framework'ü bu yapıyı doğal olarak destekler.

graph TB
    subgraph "Kullanıcı Arayüzü (Web/Mobil)"
        WebUI[Web Frontend (Vite, Tailwind, Alpine.js)]
        MobileUI[Mobil App (React Native)]
    end

    subgraph "API Gateway / Rotalama (Laravel)"
        WebRoutes[web.php]
        ApiRoutes[api.php]
    end

    subgraph "Uygulama Katmanı (Laravel)"
        Controllers[Controllers (App/Http/Controllers)]
        Services[Servis Sınıfları (Örn: App/Services)]
        Middleware[Middleware (Auth, vb.)]
    end

    subgraph "Alan (Domain) Katmanı (Laravel)"
        Models[Eloquent Modelleri (App/Models)]
        BusinessLogic[İş Mantığı (Servisler içinde)]
    end

    subgraph "Altyapı Katmanı"
        Database[Veritabanı (MySQL/PostgreSQL/SQLite)]
        PusherClient[Pusher (Gerçek Zamanlı)]
        GoogleAPIClient[Google API İstemcisi (Calendar, Auth)]
        GeminiClient[Gemini API İstemcisi (LLM)]
        Queue[Kuyruk Sistemi (Redis/Database)]
        FileSystem[Dosya Sistemi]
        Cache[Önbellek (Redis/File)]
    end

    subgraph "Harici Servisler"
        GoogleAPIs[Google Servisleri (Takvim, OAuth, Gemini)]
        PusherService[Pusher Servisi]
    end

    WebUI --> WebRoutes
    MobileUI --> ApiRoutes
    WebRoutes --> Controllers
    ApiRoutes --> Controllers
    Controllers --> Services
    Controllers --> Models
    Services --> Models
    Services --> GoogleAPIClient
    Services --> GeminiClient
    Services --> PusherClient
    Services --> Queue
    Models -- ORM --> Database
    GoogleAPIClient --> GoogleAPIs
    GeminiClient --> GoogleAPIs
    PusherClient --> PusherService
    Queue --> Database
```

#### 2.2. Katmanların Açıklaması
*   **Sunum Katmanı (Presentation Layer):**
    *   **Web:** Kullanıcıların tarayıcı üzerinden etkileşimde bulunduğu arayüz. HTML, CSS (Tailwind), JavaScript (Alpine.js, Vite ile derlenmiş). Laravel Blade şablonları ile sunulur.
    *   **Mobil:** React Native ile geliştirilen, platforma özgü kullanıcı arayüzü. API üzerinden backend ile iletişim kurar. (`react-native-mobil-design-prd.md`)
*   **API Gateway / Rotalama Katmanı:**
    *   Laravel'in rotalama sistemi (`routes/web.php`, `routes/api.php`) gelen HTTP isteklerini uygun Controller aksiyonlarına yönlendirir.
*   **Uygulama Katmanı (Application Layer):**
    *   **Controller'lar (`App/Http/Controllers`):** HTTP isteklerini alır, doğrular, ilgili servisleri çağırır ve yanıtları oluşturur.
    *   **Servis Sınıfları (Örn: `App/Services` - `component_diagram.md`'de belirtilmiş):** Kontrolcülerden çağrılan, belirli bir işlevselliğe odaklanmış iş mantığını içerir. Alan katmanı ve altyapı katmanı ile etkileşir. (Örn: `TaskService`, `EventService`, `GoogleCalendarService`, `LLMService`). Proje yapısında bu servislerin ayrı bir `App/Services` dizininde olup olmadığı kontrol edilmelidir, eğer yoksa iş mantığı doğrudan Controller'lar veya Modeller içinde olabilir. `component_diagram.md` varlıklarını belirtir.
    *   **Middleware'ler:** HTTP istek/yanıt döngüsünde araya girerek filtreleme, kimlik doğrulama, yetkilendirme gibi işlemleri gerçekleştirir. (Örn: `auth:sanctum`, `verified`).
*   **Alan (Domain) Katmanı:**
    *   **Eloquent Modelleri (`App/Models`):** Veritabanı tablolarını temsil eder ve iş mantığının bir kısmını içerebilir (ilişkiler, erişimciler/değiştiriciler, kapsamlar).
    *   **İş Mantığı:** Servis sınıfları içinde veya karmaşık olmayan durumlarda modeller içinde yer alır.
*   **Altyapı Katmanı (Infrastructure Layer):**
    *   **Veritabanı Erişimi:** Eloquent ORM aracılığıyla veritabanı işlemleri.
    *   **Harici API İstemcileri:** Google API (`google/apiclient`, `spatie/laravel-google-calendar`), Gemini API (`google-gemini-php/laravel`), Pusher (`pusher/pusher-php-server`).
    *   **Kuyruk Sistemi:** Arka plan işlemleri için (örn: bildirim gönderme, uzun süren senkronizasyonlar). Laravel Queues kullanılır.
    *   **Dosya Sistemi:** Gerekirse dosya yükleme/saklama işlemleri için.
    *   **Önbellekleme:** Performansı artırmak için sık erişilen verilerin önbelleğe alınması.

### 3. Mantıksal Tasarım

#### 3.1. Ana Bileşenler ve Sorumlulukları

*   **Kullanıcı Arayüzü (Web/Mobil):** Kullanıcı etkileşimini yönetir, veriyi sunar, backend API'sine istek gönderir.
*   **Laravel Backend:**
    *   **Routing:** Gelen istekleri uygun işleyicilere yönlendirir.
    *   **Authentication & Authorization:** Kullanıcı kimliğini doğrular ve erişim haklarını kontrol eder (Laravel Breeze, Sanctum, Policies, Gates).
    *   **Controllers:** İstekleri işler, servisleri koordine eder, yanıtlar üretir.
    *   **Services (`component_diagram.md`'ye göre):**
        *   `TaskService`, `EventService`: Görev ve etkinliklerle ilgili iş mantığı.
        *   `MessageService`: LLM etkileşim logları ve işlenmesi.
        *   `NotificationService`: Bildirim oluşturma ve yönetimi.
        *   `CalendarSyncService`, `GoogleCalendarService`: Google Takvim senkronizasyonu ve API etkileşimi.
        *   `LLMService`: Gemini API ile etkileşim, kullanıcı girdilerini işleme.
    *   **Models (Eloquent):** Veritabanı etkileşimi, veri bütünlüğü, ilişkiler.
    *   **Veritabanı (`database/migrations`):** Verilerin kalıcı olarak saklandığı yer.
    *   **Pusher Entegrasyonu:** Gerçek zamanlı olayların (örn: yeni bildirim) istemcilere iletilmesi.
    *   **Gemini Entegrasyonu:** Doğal dil işleme ve akıllı özellikler için.
    *   **Queue Workers:** Uzun süren veya zaman alan işlemleri (örn: e-posta gönderme, toplu senkronizasyon) arka planda yürütür.
    *   **Scheduler:** Zamanlanmış görevleri (örn: periyodik senkronizasyon, bildirim kontrolü) çalıştırır.

#### 3.2. Veri Modeli (Detaylı ERD)
Migration dosyalarına göre ana tablolar ve önemli alanlar:
*   **`users`**: `id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `google_calendar_id`, `google_calendar_access_token`, `google_calendar_refresh_token`, `google_calendar_token_expires_at`, `created_at`, `updated_at`.
*   **`events`**: `id`, `user_id` (FK to users), `title`, `description`, `start_time`, `end_time`, `location`, `google_event_id`, `notification_sent`, `created_at`, `updated_at`.
*   **`tasks`**: `id`, `user_id` (FK to users), `title`, `description`, `due_date`, `completed`, `priority`, `google_task_id`, `notification_sent`, `created_at`, `updated_at`.
*   **`categories`**: `id`, `name`, `created_at`, `updated_at`.
*   **`category_task`** (Pivot): `category_id` (FK to categories), `task_id` (FK to tasks).
*   **`messages`**: `id`, `user_id` (FK to users), `user_message`, `ai_response`, `ai_analysis`, `message_type`, `processed_data`, `model_used`, `is_successful`, `error_message`, `created_at`, `updated_at`, `deleted_at`.
*   **`notifications`**: `id`, `user_id` (FK to users), `type`, `data` (JSON), `read_at`, `created_at`, `updated_at`.
*   **`user_devices`**: `id`, `user_id` (FK to users), `device_token`, `platform`, `created_at`, `updated_at`.
*   **`personal_access_tokens`**: Laravel Sanctum için.
*   **`cache`**, **`jobs`**, **`failed_jobs`**: Laravel altyapı tabloları.

*(İlişkiler: User-Events (1-N), User-Tasks (1-N), User-Messages (1-N), User-Notifications (1-N), User-UserDevices (1-N), Task-Categories (N-M))*

#### 3.3. API Tasarımı
*   **Stil:** RESTful API.
*   **Veri Formatı:** JSON.
*   **Kimlik Doğrulama:** Laravel Sanctum ile Bearer Token (`Authorization: Bearer <token>`).
*   **Ana Endpoint Grupları (`routes/api.php`):**
    *   `/user`: Kullanıcı bilgileri.
    *   `/llm/*`: LLM işlemleri (process, providers, models).
    *   `/messages/*`: LLM etkileşim logları.
    *   `/chat/*`: Sohbet mesajları.
    *   `/notifications/*`: Bildirim yönetimi.
    *   `/register`, `/login`, `/verify-email`, `/resend-verification`, `/forgot-password`, `/reset-password`: Kimlik doğrulama.
    *   `/logout`, `/change-password`: Korumalı kimlik doğrulama işlemleri.
    *   `/google/*`: Google Takvim entegrasyonu (auth-url, callback, disconnect, events, import-events, connection-status, sync-event, remove-event, sync-all-events, sync-tasks).
    *   `/events/*`: Etkinlik CRUD işlemleri.
    *   `/tasks/*`: Görev CRUD işlemleri.
    *   `/device/token`: Cihaz token güncelleme.
*   **Dokümantasyon:** `darkaonline/l5-swagger` paketi ile Swagger/OpenAPI formatında sağlanır.

### 4. Fiziksel Tasarım (Dağıtım Mimarisi - Örnek)
*   **Geliştirme Ortamı:**
    *   Laravel Sail (Docker tabanlı yerel geliştirme ortamı).
    *   Veritabanı: Genellikle SQLite (`database/database.sqlite`) veya Sail içindeki MySQL/PostgreSQL.
    *   Web Sunucusu: Sail içindeki Nginx/Apache.
    *   PHP: Sail içindeki PHP sürümü.
    *   Node.js/Vite: Frontend varlıklarının geliştirilmesi ve derlenmesi için.
*   **Üretim Ortamı (Önerilen):**
    *   **Web Sunucusu:** Nginx (reverse proxy, static asset sunumu) veya Apache.
    *   **Uygulama Sunucusu:** PHP-FPM.
    *   **Veritabanı:** PostgreSQL veya MySQL.
    *   **Önbellek:** Redis veya Memcached (Session, Cache).
    *   **Kuyruk Yöneticisi:** Redis (Horizon ile birlikte) veya Database.
    *   **İşletim Sistemi:** Linux dağıtımı (Ubuntu, CentOS vb.).
    *   **Konteynerizasyon (Opsiyonel ama önerilir):** Docker ile uygulamanın ve bağımlılıklarının paketlenmesi.
    *   **CI/CD:** GitHub Actions, GitLab CI, Jenkins gibi araçlarla otomatik test, build ve deployment süreçleri.

### 5. Teknoloji Yığını (Özet)
*   **Backend:** PHP 8.2+, Laravel 12+
*   **Frontend (Web):** Vite, JavaScript, Tailwind CSS v3+, Alpine.js v3+
*   **Frontend (Mobil):** React Native CLI, JavaScript (`react-native-mobil-design-prd.md`)
*   **Veritabanı:** PostgreSQL, MySQL, SQLite (geliştirme)
*   **API Dokümantasyonu:** Swagger/OpenAPI (`darkaonline/l5-swagger`)
*   **Gerçek Zamanlı İletişim:** Pusher (`pusher/pusher-php-server`, `pusher-js`)
*   **Google Entegrasyonları:**
    *   Google API Client Library (`google/apiclient`)
    *   Laravel Google Calendar (`spatie/laravel-google-calendar`)
*   **LLM Entegrasyonu:** Google Gemini PHP Laravel (`google-gemini-php/laravel`)
*   **Kimlik Doğrulama (Web):** Laravel Breeze
*   **Kimlik Doğrulama (API):** Laravel Sanctum
*   **HTTP İstemcisi (JS):** Axios
*   **Geliştirme Ortamı:** Laravel Sail (Docker)
*   **Paket Yöneticileri:** Composer (PHP), NPM/Yarn (JavaScript)
*   **CSS İşleme:** PostCSS, Autoprefixer

### 6. Güvenlik Hususları
*   **Veri Şifreleme:** HTTPS ile transit halindeki veriler, hassas veritabanı alanları için gerekirse ek şifreleme.
*   **Kimlik Doğrulama ve Yetkilendirme:**
    *   Laravel Breeze (web) ve Sanctum (API) ile güçlü kimlik doğrulama.
    *   Rol tabanlı erişim kontrolü (RBAC) eğer gerekirse Laravel Policies/Gates ile implemente edilebilir.
*   **Girdi Doğrulama:** Laravel's validation özellikleri tüm kullanıcı girdileri için kullanılmalıdır.
*   **Çıktı Kodlama:** XSS saldırılarını önlemek için Blade şablonlarında varsayılan kaçış mekanizmaları ve API yanıtlarında uygun içerik türleri.
*   **CSRF Koruması:** Laravel'in yerleşik CSRF koruması web rotaları için aktif olmalıdır.
*   **API Rate Limiting:** Kötüye kullanımı önlemek için API endpoint'lerine hız sınırlaması uygulanabilir.
*   **Bağımlılık Güvenliği:** `composer audit` ve `npm audit` gibi araçlarla düzenli olarak bağımlılık zafiyetleri kontrol edilmelidir.
*   **Hassas Yapılandırma:** API anahtarları, veritabanı şifreleri vb. `.env` dosyasında tutulmalı ve sürüm kontrolüne dahil edilmemelidir.

