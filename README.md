<!-- Akıllı Ajanda Web — README: editoryal / ürün sayfası tonu; Laravel varsayılan şablonu kasıtlı olarak kullanılmıyor. -->

<div align="center">

# Akıllı Ajanda · Web

**Etkinlik, görev ve takvim — tek merkez.**  
Google Calendar ile konuşan, gerçek zamanlı güncellenen, doğal dille yönetilen bir ajanda.

[![PHP](https://img.shields.io/badge/PHP-8.2-1a1a2e?style=for-the-badge&labelColor=16213e)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-e94560?style=for-the-badge&labelColor=1a1a2e&logo=laravel&logoColor=white)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169e1?style=for-the-badge&labelColor=0f3460&logo=postgresql&logoColor=white)](https://www.postgresql.org/)

</div>

---

> *“Yarın 15:00’te toplantı ekle”, “bu haftanın görevlerini göster”, “önümüzdeki üç günü özetle”* — ajandanı hem klasik arayüzle hem LLM destekli sohbetle yönet.

---

## Neden bu proje?

Dağınık takvimler ve görev listeleri yerine **tek uygulama**: PostgreSQL üzerinde tutarlı veri, Laravel ile güvenli kimlik ve API katmanı, Pusher ile sayfa yenilemeden güncellenen arayüz. İstersen Google Calendar ile iki yönlü senkron; istersen raporları CSV/PDF olarak dışa aktar.

| Katman | Seçim |
|--------|--------|
| Sunum | Blade, Tailwind, Alpine.js; kimlik akışlarında Bootstrap |
| Uygulama | Laravel 12, Sanctum |
| Veri | PostgreSQL, Eloquent |
| Gerçek zamanlı | Pusher (yayın / bildirim) |
| Entegrasyon | Google Calendar, Gemini / LLM sağlayıcıları |
| Ön uç derleme | Vite 6 |

---

## Öne çıkanlar

- **Görev & etkinlik** — filtreleme, sıralama, kategori, metin araması (`pg_trgm` ile tamamlayıcı arama)
- **Takvim & özet** — haftalık pencere, dashboard özetleri
- **Raporlama** — tarih aralığı, CSV ve PDF çıktı
- **Kimlik** — Breeze tabanlı kayıt/giriş, **e-posta kodu** ile doğrulama, onboarding ve yardım sayfaları
- **API** — Swagger (L5-Swagger) ile dokümante edilebilir uçlar
- **Test** — PHPUnit ile birim ve özellik testleri (`php artisan test`)

Proje gereksinimleri ve zaman çizelgesi: `SRS_SRD_AkilliAjandaMobil.md`, `HAFTALIK_GELISTIRME_PLANI.md`.

---

## Hızlı başlangıç

```bash
git clone <repo-url> AkilliAjandaWeb && cd AkilliAjandaWeb
cp .env.example .env
composer install
npm install
```

Uygulama anahtarı ve veritabanı:

```bash
php artisan key:generate
# .env içinde DB_* alanlarını PostgreSQL'e göre düzenle
php artisan migrate
```

Geliştirme sunucuları (iki terminal):

```bash
php artisan serve
npm run dev
```

> **Not:** `.env.example` içinde `PHP_CLI_SERVER_WORKERS=1` gibi yerel geliştirme notları vardır; üretimde kendi dağıtım rehberinize göre ayarlayın.

---

## Komutlar

| Amaç | Komut |
|------|--------|
| Testler | `php artisan test` |
| Kod stili (PHP) | `./vendor/bin/pint` |
| Vite üretim derlemesi | `npm run build` |

---

## Dizin ipuçları

| Yol | İçerik |
|-----|--------|
| `app/Http/Controllers` | HTTP giriş noktaları |
| `app/Services` | İş kuralları (takvim, görev, arama, rapor vb.) |
| `resources/views` | Blade arayüzleri |
| `routes/web.php` | Web rotaları |
| `tests/` | PHPUnit testleri |

---

## Güvenlik

Güvenlik açığı bildirimi için lütfen depo sahibiyle **özel kanaldan** iletişime geçin; açık issue’larda hassas detay paylaşmayın.

---

## Lisans

Bu depodaki uygulama kodu **MIT** ile lisanslanır (Laravel ve diğer bağımlılıklar kendi lisanslarına tabidir).

---

<div align="center">

**Akıllı Ajanda** — planını tek yerde tut.

</div>
