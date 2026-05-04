# Performans ve Lighthouse notları (Hafta 10)

Bu dosya kısa ölçüm / denetim notlarını içerir; CI’a bağlı değildir.

## Yapılan iyileştirmeler (özet)

- **Görev listesi:** `per_page` ile sayfalandırma, filtrelerle birlikte `withQueryString()`.
- **N+1:** Dashboard yakın etkinlik sorgusu `categories` eager load; takvim etkinlikleri seçili kolon + ilişki.
- **Takvim penceresi:** `CalendarQueryWindow` ile istemci aralığı en fazla 120 güne indirgenir; etkinlikler örtüşme (`start <= rangeEnd` ve `end >= rangeStart`) ile çekilir.
- **Arayüz:** FullCalendar `lazyFetching`, günlük hücrede `dayMaxEventRows: 4`, ilk yüklemede iskelet; flash mesajlar toast.

## Lighthouse (Chrome, gizli pencere)

1. Üretim benzeri ortamda `php artisan serve` veya dağıtım URL’si aç.
2. DevTools → Lighthouse → Mod: **Navigation**, Cihaz: **Mobil** önerilir.
3. Kategoriler: Performance, Accessibility, Best Practices (isteğe bağlı SEO).
4. Tekrarlanabilirlik için aynı profille 2–3 çalıştırıp medyan al.

### Hedef eşikler (yönlendirici)

| Metrik            | Not |
| ----------------- | --- |
| LCP               | Üçüncü parti CDN (FullCalendar, font) LCP’yi etkiler; kritik CSS zaten sayfada. |
| TBT               | Takvim + sohbet script’leri ana iş parçacığını meşgul edebilir; `dayMaxEventRows` ile hafifletildi. |
| CLS               | Takvim iskeleti yerleşik yükseklikle CLS riskini azaltır. |

## Sonraki adaylar

- FullCalendar ve ikon fontlarını Vite paketine alarak self-host (CDN gecikmesi ↓).
- Sohbet offcanvas script’ini dashboard’tan sonra lazy import (ayrı bundle).
