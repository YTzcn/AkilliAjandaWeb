<?php

namespace App\Services\LLM\Providers;

use Gemini\Laravel\Facades\Gemini;
use Carbon\Carbon;
use Exception;

class GeminiProvider implements ProviderInterface
{
    /**
     * Kullanılacak model adı
     * 
     * @var string
     */
    protected string $model = 'gemini-1.5-pro';
    
    /**
     * Kullanıcı mesajını işler ve analiz eder
     * 
     * @param string $message Kullanıcı mesajı
     * @return array İşlenmiş analiz sonucu
     */
    public function processMessage(string $message): array
    {
        try {
            // Mevcut model kontrolü
            $this->checkAndSetBestModel();
            
            // Gemini API kullanarak mesajı analiz et
            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            $response = Gemini::generativeModel($this->model)->generateContent([
                'Sen bir Akıllı Ajanda Uygulamasının Asistanısın. Kullanıcılar seninle konuşarak ajanda üzerindeki işlemlerini yapabilirler.',
                'ÖNEMLİ: Şu anki gerçek tarih ve saat: ' . $currentDate . ' Bu tarihi referans alarak işlem yap.',
                'Tarih ile ilgili tüm kararlarında bugün olarak yukarıdaki tarihi kabul et ve buna göre hesaplama yap.',
                'Kullanıcı mesajı: ' . $message,
                'Lütfen aşağıdaki adımları izle:',
                '1. Kullanıcının mesajının içeriğini analiz et',
                '2. Kullanıcının ne yapmak istediğini belirle: takvim sorgulama, etkinlik ekleme, görev güncelleme, etkinlik güncelleme, görev ekleme veya özet bilgi isteme',
                '3. Kullanıcının mesajındaki tarihleri, kişileri, etkinlik/görev detaylarını belirle',
                '4. Kullanıcı saat aralığı belirtmişse (örn: "08:00-17:00", "8.00-17.00" gibi), bu saat aralığını start_date ve end_date olarak düzgün biçimde doldur',
                '5. İçerik türünü belirle: Kullanıcı etkinlikleri mi, görevleri mi yoksa her ikisini birden mi sorguluyor',
                '6. Görevler için öncelik ve durum değerlerini kullanıcının ifade tarzından çıkar:',
                '   - Öncelik: 1 (düşük), 2 (orta), 3 (yüksek/acil)',
                '   - İfadede "önemli", "acil", "hemen", "kritik" gibi kelimeler varsa öncelik 3 (yüksek) olmalı',
                '   - İfadede "önemsiz", "acil değil", "vakit buldukça" gibi ifadeler varsa öncelik 1 (düşük) olmalı',
                '   - Belirgin bir vurgu yoksa öncelik 2 (orta) kullan',
                '   - Durum: "beklemede" (pending), "devam_ediyor" (in_progress), "tamamlandı" (completed), "iptal" (cancelled)',
                '   - İfadede "başla", "başlayacağım", "üzerinde çalışıyorum" gibi ifadeler varsa durum "devam_ediyor" olmalı',
                '   - İfadede "tamamlandı", "bitti", "hallettim" gibi kelimeler varsa durum "tamamlandı" olmalı',
                '   - İfadede "iptal", "vazgeçtim", "yapılmayacak" varsa durum "iptal" olmalı',
                '   - Belirgin bir durum belirtilmemişse varsayılan olarak "beklemede" kullan',
                '7. Yanıtını tam olarak aşağıdaki JSON formatında ver (başka bir metin veya açıklama olmadan):',
                '{',
                '  "type": "işlem_tipi", // takvim_sorgulama, yeni_etkinlik, yeni_görev, gorev_guncelleme, etkinlik_guncelleme, ozet_bilgi',
                '  "data": {',
                '    "title": "Etkinlik/Görev başlığı", // Etkinlik veya görev başlığı',
                '    "start_date": "YYYY-MM-DD HH:MM:SS", // Başlangıç tarihi ve saati (etkinlikler için), her zaman tam tarih saat kullan',
                '    "end_date": "YYYY-MM-DD HH:MM:SS", // Bitiş tarihi ve saati (etkinlikler için), her zaman tam tarih saat kullan',
                '    "due_date": "YYYY-MM-DD HH:MM:SS", // Bitiş tarihi (görevler için), her zaman tam tarih saat kullan',
                '    "description": "Açıklama", // Varsa açıklama',
                '    "location": "Konum", // Varsa konum (etkinlikler için)',
                '    "task_id": "id", // Görev güncellemesi için ID',
                '    "event_id": "id", // Etkinlik güncellemesi için ID',
                '    "all_day": false, // Tüm gün etkinliği mi? (etkinlikler için)',
                '    "status": "beklemede", // Görevin durumu (görevler için): beklemede (pending), devam_ediyor (in_progress), tamamlandı (completed), iptal (cancelled)',
                '    "priority": 2, // Görevin önceliği (görevler için): 1 (düşük), 2 (orta), 3 (yüksek/acil)',
                '    "is_completed": false, // Görev tamamlandı mı? (görevler için) - status=completed ise true, değilse false',
                '    "content_type": "both", // Sorgu türü: etkinlikler, görevler veya her ikisi',
                '    "user_id": 1 // Etkinlik veya görev sahibi ID (normalde burası yok sayılacak, sistem otomatik atayacak)',
                '  }',
                '}',
                'ÖNEMLİ NOTLAR:',
                '1. "bugün" ifadesini görürsen MUTLAKA ' . $currentDate . ' tarihini kullan, kendi kafandan tarih uydurma',
                '2. "yarın" ifadesini görürsen MUTLAKA ' . Carbon::tomorrow()->format('Y-m-d H:i:s') . ' tarihini kullan',
                '3. "dün" ifadesini görürsen MUTLAKA ' . Carbon::yesterday()->format('Y-m-d H:i:s"') . ' tarihini kullan',
                '4. Tarihleri yazarken her zaman tam tarih ve saat formatı (YYYY-MM-DD HH:MM:SS) kullan',
                '5. Tarihler HER ZAMAN gerçek şu anki tarihten (YUKARIDA VERİLEN ' . $currentDate . ' TARİHİNDEN) hesaplanmalıdır',
                '6. Kendi bildiğin tarih yerine MUTLAKA yukarıdaki güncel tarihi kullan',
                '7. Görev önceliği (priority) ve durumu (status) kullanıcının ifade tonundan MUTLAKA çıkarılmalıdır',
                '8. Eğer kullanıcı saat aralığı soruyorsa (örn. "yarın 8.00-17.00 arasında" gibi), MUTLAKA takvim_sorgulama tipi ile cevap ver',
                '9. Saat aralıklarını doğru parse et: "8.00-17.00" veya "8:00-17:00" gibi aralıklar için start_date ve end_date\'i aynı gün içinde bu saat aralığı için ayarla',
                '10. Yanıtını sadece JSON formatında ver. Başka açıklama ekleme, yorum yapma veya metinle cevap verme. JSON yanıtı kod bloğu (```) içinde de verme. JSON dışında hiçbir karakter olmamalıdır.',
                '11. ÇOK ÖNEMLİ: Takvim sorgulama işleminde MUTLAKA user_id değerini 1 olarak belirle, NULL BIRAKMA!',
                '12. ÇOK ÖNEMLİ: Takvim sorgulama işleminde start_date ve end_date değerlerinin her ikisini de doldur, NULL BIRAKMA!',
                '13. ÇOK ÖNEMLİ: content_type değerini MUTLAKA doldur - etkinlikler, görevler veya her ikisi (both). NULL BIRAKMA!',
                '14. Eğer bir alan için değer belirtilmemişse, o alanı NULL BIRAKMA. Uygun bir varsayılan değer kullan.',
                '15. ÇOK ÖNEMLİ: Görev veya etkinlik güncelleme işleminde (type: gorev_guncelleme veya etkinlik_guncelleme) şunlara dikkat et:',
                '    a. Eğer kullanıcı mesajda ID belirtiyorsa (örn: "#5 numaralı görevi güncelle", "etkinlik 12\'nin yerini değiştir"), task_id veya event_id alanını doldur.',
                '    b. Eğer kullanıcı ID yerine başlık belirtiyorsa (örn: "YGA sunumunu güncelle", "Doktor randevusunu taşı"), task_id ve event_id alanlarını BOŞ BIRAK (null yap), bunun yerine title alanına kullanıcının belirttiği başlığı yaz.',
                '    c. Güncellenmesi istenen diğer alanları (location, start_date, status vb.) normal şekilde doldur.',
                '16. Eğer kullanıcı görev/etkinlik güncellemesi yapmak istiyor ama ID belirtmemişse, işlem tipini yine gorev_guncelleme/etkinlik_guncelleme olarak belirle, sistem ID\'yi bulmaya çalışacak.',
                '17. Yanıt oluştururken varsa etkinlik ve görevlerin ID bilgilerini MUTLAKA göster, kullanıcının bu bilgileri görmesi önemlidir.'
            ]);

            // API yanıtını güvenli bir şekilde parse et
            $responseText = $response->text();
            
            // JSON yanıtını temizle - markdown kod bloklarını kaldır
            $cleanedResponse = $this->cleanJsonResponse($responseText);
            
            // JSON'ı parse et
            $analysis = json_decode($cleanedResponse, true);
            
            // JSON doğru parse edildi mi kontrol et
            if (!is_array($analysis) || !isset($analysis['type'])) {
                throw new Exception("AI yanıtı düzgün parse edilemedi: " . $responseText);
            }
            
            // Data kontrolü - bazı durumlarda data alanı boş gelebilir
            if (!isset($analysis['data']) || !is_array($analysis['data'])) {
                $analysis['data'] = [];
            }
            
            // Kritik alanların varsayılan değerlerini ata
            if ($analysis['type'] === 'takvim_sorgulama') {
                if (!isset($analysis['data']['user_id']) || $analysis['data']['user_id'] === null) {
                    $analysis['data']['user_id'] = auth()->id() ?? 1;
                }
                
                if (!isset($analysis['data']['content_type']) || $analysis['data']['content_type'] === null) {
                    $analysis['data']['content_type'] = 'both';
                }
                
                // start_date null ise bugün olarak ayarla
                if (!isset($analysis['data']['start_date']) || $analysis['data']['start_date'] === null) {
                    $analysis['data']['start_date'] = Carbon::today()->startOfDay()->format('Y-m-d H:i:s');
                }
                
                // end_date null ise ve start_date varsa start_date ile aynı günün sonu olarak ayarla
                if ((!isset($analysis['data']['end_date']) || $analysis['data']['end_date'] === null) && isset($analysis['data']['start_date'])) {
                    $startDate = Carbon::parse($analysis['data']['start_date']);
                    $analysis['data']['end_date'] = $startDate->copy()->endOfDay()->format('Y-m-d H:i:s');
                }
            }
            
            return $analysis;
        } catch (Exception $e) {
            throw new Exception('Gemini mesaj işleme hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Soru bazlı içerik oluşturur
     * 
     * @param array $prompt İstek için prompt
     * @return string Model tarafından üretilen yanıt
     */
    public function generateContent(array $prompt): string
    {
        try {
            $response = Gemini::generativeModel($this->model)->generateContent($prompt);
            return $response->text();
        } catch (Exception $e) {
            throw new Exception('Gemini içerik oluşturma hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * JSON yanıtını temizler - kod blokları, fazla boşluklar vs. kaldırılır
     *
     * @param string $response
     * @return string
     */
    private function cleanJsonResponse(string $response): string
    {
        // Markdown JSON kod bloklarını temizle (```json ... ``` veya ```{...}```)
        $pattern = '/```(?:json)?\s*(.*?)```/s';
        if (preg_match($pattern, $response, $matches)) {
            return trim($matches[1]);
        }
        
        // Eğer kod bloğu formatında değilse, ilk '{' ve son '}' arasındaki metni al
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            return trim($matches[0]);
        }
        
        // Hiçbir eşleşme bulunamazsa orijinal metni döndür
        return trim($response);
    }
    
    /**
     * Modelin mevcut durumda kullanılabilir olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $models = $this->listModels();
            return !empty($models);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Kullanılabilir en iyi Gemini modelini kontrol eder ve seçer
     * 
     * @return void
     */
    private function checkAndSetBestModel(): void
    {
        try {
            $models = $this->listModels();
            
            // Pro model kontrolü yap
            foreach ($models as $model) {
                // Son model adını kullan
                $modelName = is_array($model) ? ($model['name'] ?? '') : $model;
                
                if (is_string($modelName) && str_contains($modelName, 'gemini-1.5-pro')) {
                    $this->model = $modelName;
                    break;
                }
            }
        } catch (Exception $e) {
            // Hata durumunda varsayılan modeli kullan
        }
    }
    
    /**
     * Varsayılan model adını döndürür
     * 
     * @return string
     */
    public function getDefaultModel(): string
    {
        return $this->model;
    }
    
    /**
     * Kullanılacak modeli ayarlar
     * 
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Kullanılabilir modelleri listeler
     * 
     * @return array
     */
    public function listModels(): array
    {
        try {
            $modelResponse = Gemini::models()->list();
            
            if (isset($modelResponse->models)) {
                $models = [];
                foreach ($modelResponse->models as $model) {
                    $models[] = str_replace('models/', '', $model->name ?? '');
                }
                return $models;
            }
            
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
} 