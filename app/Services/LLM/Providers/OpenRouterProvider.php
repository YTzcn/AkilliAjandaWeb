<?php

namespace App\Services\LLM\Providers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class OpenRouterProvider implements ProviderInterface
{
    /**
     * Kullanılacak model adı
     * 
     * @var string
     */
    protected string $model = 'google/gemini-2.0-flash-001';
    
    /**
     * OpenRouter API anahtarı
     * 
     * @var string|null
     */
    protected ?string $apiKey = null;
    
    /**
     * API taban URL'i
     * 
     * @var string
     */
    protected string $baseUrl = 'https://openrouter.ai/api/v1';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.openrouter.api_key');
        $configModel = Config::get('llm.models.openrouter.default');
        if ($configModel) {
            $this->model = $configModel;
        }
    }
    
    /**
     * Kullanıcı mesajını işler ve analiz eder
     * 
     * @param string $message Kullanıcı mesajı
     * @return array İşlenmiş analiz sonucu
     */
    public function processMessage(string $message): array
    {
        try {
            if (!$this->apiKey) {
                throw new Exception('OpenRouter API anahtarı bulunamadı');
            }
            
            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            $response = $this->callOpenRouter([
                "model" => $this->model,
                "messages" => [
                    [
                        "role" => "system",
                        "content" => 
                            "Sen bir Akıllı Ajanda Uygulamasının Asistanısın. " .
                            "Ana görevin kullanıcıların ajanda işlemlerini yönetmek. " .
                            "Kısa, profesyonel sohbet yapabilirsin; ancak aşağıdaki kurallara kesinlikle uy:" . "\n" .
                            "1. Ajanda dışı cevapları en fazla 2 cümleyle sınırlı tut." . "\n" .
                            "2. Her zaman profesyonel ve saygılı ol." . "\n" .
                            "3. Sohbet yalnızca selamlaşma, hal hatır sorma, teşekkür ve özür dileme konularını içerebilir." . "\n" .
                            "4. Asla kişisel konulara, siyasete, dine, spora veya espriye girme." . "\n" .
                            "5. Sohbet uzarsa nazikçe ajanda konusuna dön." . "\n" .
                            "6. Belirsiz mesajda ajandayla ilgili ne yapmak istediğini sor."
                    ],
                    [
                        "role" => "user",
                        "content" =>
                            "ÖNEMLİ: Şu anki tarih ve saat: " . $currentDate . 
                            ". Bu tarihi referans alarak hareket et.\n\n" .
                            "Kullanıcı mesajı: " . $message . "\n\n" .
                            "Lütfen şu adımları izle:\n" .
                            "1. Mesajı analiz et ve işlem tipini belirle: takvim_sorgulama, yeni_etkinlik, yeni_görev, gorev_guncelleme, etkinlik_guncelleme, ozet_bilgi.\n" .
                            "2. Mesajdaki tarihleri, kişileri ve etkinlik/görev detaylarını yakala.\n" .
                            "3. İçerik türünü tespit et: etkinlik, görev veya ikisi birden.\n" .
                            "4. Görev öncelik ve durumunu ifadeden çıkar:\n" .
                            "   • Öncelik: 1=düşük, 2=orta, 3=yüksek. \"önemli\", \"acil\" vb. → 3; \"önemsiz\", \"vakit buldukça\" vb. → 1; aksi halde 2.\n" .
                            "   • Durum: pending, in_progress, completed, cancelled. \"başla\", \"çalışıyorum\" → in_progress; \"tamamlandı\", \"bitti\" → completed; \"iptal\", \"vazgeçtim\" → cancelled; aksi halde pending.\n" .
                            "5. Yanıtını **yalnızca** şu JSON formatında ver (CODE BLOĞU veya ekstra metin yok):\n" .
                            "{\n" .
                            "  \"type\": \"işlem_tipi\",\n" .
                            "  \"data\": {\n" .
                            "    \"title\": \"Başlık\",\n" .
                            "    \"start_date\": \"YYYY-MM-DD HH:MM:SS\",\n" .
                            "    \"end_date\": \"YYYY-MM-DD HH:MM:SS\",\n" .
                            "    \"due_date\": \"YYYY-MM-DD HH:MM:SS\",\n" .
                            "    \"description\": \"Açıklama\",\n" .
                            "    \"location\": \"Konum\",\n" .
                            "    \"task_id\": \"ID\",\n" .
                            "    \"event_id\": \"ID\", // Etkinlik güncellemesi için ID
" .
                            "    \"all_day\": false,\n" .
                            "    \"status\": \"pending\",\n" .
                            "    \"priority\": 2,\n" .
                            "    \"is_completed\": false,\n" .
                            "    \"content_type\": \"both\",\n" .
                            "    \"user_id\": 1\n" .
                            "  }\n" .
                            "}\n\n" .
                            "Tarihlerde \"bugün\", \"yarın\", \"dün\" ifadelerini sırasıyla:\n" .
                            "- bugün → " . $currentDate . "\n" .
                            "- yarın → " . Carbon::tomorrow()->format('Y-m-d H:i:s') . "\n" .
                            "- dün → " . Carbon::yesterday()->format('Y-m-d H:i:s') . "\n" .
                            "olarak kullan. Hep tam format (YYYY-MM-DD HH:MM:SS) ve verilen " . $currentDate . " referans alınsın.\n\n" .
                            "ÇOK ÖNEMLİ NOTLAR:\n" .
                            "1. Takvim sorgulama işleminde MUTLAKA user_id değerini 1 olarak belirle, NULL BIRAKMA!\n" .
                            "2. Takvim sorgulama işleminde start_date ve end_date değerlerinin her ikisini de doldur, NULL BIRAKMA!\n" .
                            "3. content_type değerini MUTLAKA doldur - etkinlikler, görevler veya her ikisi (both). NULL BIRAKMA!\n" .
                            "4. Eğer bir alan için değer belirtilmemişse, o alanı NULL BIRAKMA. Uygun bir varsayılan değer kullan.\n" .
                            "5. Bugünkü tarihi soruyorsa start_date=" . $currentDate . " ve end_date=" . Carbon::today()->endOfDay()->format('Y-m-d H:i:s') . " olarak ayarla.\n" .
                            "6. Yarınki tarihi soruyorsa start_date=" . Carbon::tomorrow()->startOfDay()->format('Y-m-d H:i:s') . " ve end_date=" . Carbon::tomorrow()->endOfDay()->format('Y-m-d H:i:s') . " olarak ayarla.\n" .
                            "7. ÇOK ÖNEMLİ: Görev veya etkinlik güncelleme işleminde (type: gorev_guncelleme veya etkinlik_guncelleme) şunlara dikkat et:\n" .
                            "    a. Eğer kullanıcı mesajda ID belirtiyorsa (örn: \"#5 numaralı görevi güncelle\", \"etkinlik 12\'nin yerini değiştir\"), task_id veya event_id alanını doldur.\n" .
                            "    b. Eğer kullanıcı ID yerine başlık belirtiyorsa (örn: \"YGA sunumunu güncelle\", \"Doktor randevusunu taşı\"), task_id ve event_id alanlarını BOŞ BIRAK (null yap), bunun yerine title alanına kullanıcının belirttiği başlığı yaz.\n" .
                            "    c. Güncellenmesi istenen diğer alanları (location, start_date, status vb.) normal şekilde doldur.\n" .
                            "8. Eğer kullanıcı görev/etkinlik güncellemesi yapmak istiyor ama ID belirtmemişse, işlem tipini yine gorev_guncelleme/etkinlik_guncelleme olarak belirle, sistem ID'yi bulmaya çalışacak.\n" .
                            "9. Yanıt oluştururken varsa etkinlik ve görevlerin ID bilgilerini MUTLAKA göster, kullanıcının bu bilgileri görmesi önemlidir."
                    ]
                ],
                "response_format" => [
                    "type" => "json_object"
                ]
            ]);
            
            
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new Exception("API geçerli bir yanıt döndürmedi");
            }

            $content = $response['choices'][0]['message']['content'];
            $cleanedContent = $this->cleanJsonResponse($content);
            $analysis = json_decode($cleanedContent, true);
            
            if (!is_array($analysis) || !isset($analysis['type'])) {
                throw new Exception("AI yanıtı düzgün parse edilemedi: " . $cleanedContent);
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
            // Log::error('[OpenRouterProvider] processMessage error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new Exception('OpenRouter mesaj işleme hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * JSON yanıtını markdown kod bloklarından temizler.
     *
     * @param string $response Ham yanıt.
     * @return string Temizlenmiş JSON string.
     */
    private function cleanJsonResponse(string $response): string
    {
        // JSON yanıtını temizle - markdown kod bloklarını kaldır (```json ... ``` veya ``` ... ```)
        // Baştaki ```json veya ``` kalıbını ve boşlukları temizle
        $response = preg_replace('/^```(?:json)?\s*/i', '', $response);
        // Sondaki ``` kalıbını ve boşlukları temizle
        $response = preg_replace('/\s*```$/', '', $response);
        return trim($response);
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
            if (!$this->apiKey) {
                throw new Exception('OpenRouter API anahtarı bulunamadı');
            }
            
            $messages = [];
            
            // Sistem mesajı
            $messages[] = [
                "role" => "system",
                "content" => "Sen bir Akıllı Ajanda Uygulamasının Asistanısın."
            ];
            
            // Kullanıcı mesajı
            $messages[] = [
                "role" => "user",
                "content" => implode("\n", $prompt)
            ];
            
            $response = $this->callOpenRouter([
                "model" => $this->model,
                "messages" => $messages
            ]);
            
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new Exception("API geçerli bir yanıt döndürmedi");
            }
            
            return $response['choices'][0]['message']['content'];
        } catch (Exception $e) {
            throw new Exception('OpenRouter içerik oluşturma hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * OpenRouter API'sine HTTP isteği gönderir
     *
     * @param array $data İstek gövdesi
     * @return array Yanıt
     */
    private function callOpenRouter(array $data): array
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url'),
            'X-Title' => 'Akıllı Ajanda'
        ])->post($this->baseUrl . '/chat/completions', $data);
        
        if ($response->failed()) {
            throw new Exception('OpenRouter API hatası: ' . $response->body());
        }
        
        return $response->json();
    }
    
    /**
     * Modelin mevcut durumda kullanılabilir olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            if (!$this->apiKey) {
                return false;
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'Akıllı Ajanda'
            ])->get($this->baseUrl . '/models');
            
            return $response->successful();
        } catch (Exception $e) {
            return false;
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
            if (!$this->apiKey) {
                return [];
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'Akıllı Ajanda'
            ])->get($this->baseUrl . '/models');
            
            if ($response->failed()) {
                return [];
            }
            
            $models = [];
            $data = $response->json();
            
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $model) {
                    if (isset($model['id'])) {
                        $models[] = $model['id'];
                    }
                }
            }
            
            return $models;
        } catch (Exception $e) {
            return [];
        }
    }
} 