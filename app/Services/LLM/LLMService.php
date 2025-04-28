<?php

namespace App\Services\LLM;

use App\Services\LLM\Providers\ProviderInterface;
use App\Services\LLM\Handlers\TaskHandler;
use App\Services\LLM\Handlers\EventHandler;
use App\Models\Task;
use App\Models\Event;
use App\Models\Message;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class LLMService
{
    /**
     * Log kanalı
     */
    protected const LOG_CHANNEL = 'llm';

    /**
     * LLM sağlayıcısı
     * 
     * @var ProviderInterface
     */
    protected ProviderInterface $provider;
    
    /**
     * Görev işleyicisi
     * 
     * @var TaskHandler
     */
    protected TaskHandler $taskHandler;
    
    /**
     * Etkinlik işleyicisi
     * 
     * @var EventHandler
     */
    protected EventHandler $eventHandler;
    
    /**
     * Constructor
     * 
     * @param string|null $providerName Kullanılacak sağlayıcı adı (null ise varsayılan sağlayıcı kullanılır)
     */
    public function __construct(?string $providerName = null)
    {
        $this->provider = ProviderFactory::create($providerName);
        $this->taskHandler = new TaskHandler();
        $this->eventHandler = new EventHandler();
    }
    
    /**
     * Kullanılacak sağlayıcıyı değiştirir
     * 
     * @param string $providerName
     * @return self
     */
    public function setProvider(string $providerName): self
    {
        $this->provider = ProviderFactory::create($providerName);
        return $this;
    }
    
    /**
     * Mevcut sağlayıcıyı döndürür
     * 
     * @return ProviderInterface
     */
    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }
    
    /**
     * Kullanıcı mesajını işler ve yanıt verir
     * 
     * @param string $message Kullanıcı mesajı
     * @return string Yanıt
     */
    public function processUserMessage(string $message): string
    {
        $requestId = uniqid('llm_');
        Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Yeni istek başladı", [
            'message' => $message,
            'provider' => get_class($this->provider)
        ]);

        try {
            // Mesajı LLM ile analiz et
            Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Provider analizi başladı");
            $analysis = $this->provider->processMessage($message);
            Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Provider analizi tamamlandı", [
                'type' => $analysis['type'] ?? 'unknown',
                'data' => $analysis['data'] ?? []
            ]);
            
            // Görev güncelleme işlemi için task_id kontrolü
            if (($analysis['type'] ?? '') === 'gorev_guncelleme' && (!isset($analysis['data']['task_id']) || empty($analysis['data']['task_id']))) {
                // Task ID eksikse, son kullanıcı mesajından task ID çıkarmayı dene
                $taskId = $this->extractTaskIdFromMessage($message);
                if ($taskId) {
                    $analysis['data']['task_id'] = $taskId;
                    Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Mesajdan task_id çıkarıldı", [
                        'task_id' => $taskId
                    ]);
                } else {
                    // Kullanıcıya görev ID'sini belirtmesi gerektiğini bildir
                    return 'Görev güncellemek için görev ID bilgisi eksik. Lütfen güncellemek istediğiniz görevin numarasını belirtin.';
                }
            }
            
            // İşlem türüne göre uygun fonksiyonu çağır
            $result = match($analysis['type']) {
                'takvim_sorgulama' => $this->loggedMethod($requestId, 'handleCalendarQuery', fn() => $this->handleCalendarQuery($analysis['data'])),
                'takvim_ozet' => $this->loggedMethod($requestId, 'handleCalendarSummary', fn() => $this->handleCalendarSummary($analysis['data'])),
                'yeni_etkinlik' => $this->loggedMethod($requestId, 'handleNewEvent', fn() => $this->eventHandler->handleNewEvent($analysis['data'])),
                'yeni_görev' => $this->loggedMethod($requestId, 'handleNewTask', fn() => $this->taskHandler->handleNewTask($analysis['data'])),
                'gorev_guncelleme' => $this->loggedMethod($requestId, 'handleUpdateRequest', fn() => $this->handleUpdateRequest($analysis['data'], 'task')),
                'etkinlik_guncelleme' => $this->loggedMethod($requestId, 'handleUpdateRequest', fn() => $this->handleUpdateRequest($analysis['data'], 'event')),
                'ozet_bilgi' => $this->loggedMethod($requestId, 'handleSummaryRequest', fn() => $this->eventHandler->handleSummaryRequest($analysis['data'], $this->provider)),
                'sohbet' => $analysis['data']['message'] ?? 'Üzgünüm, sizi anlayamadım. Ajandanızla ilgili nasıl yardımcı olabilirim?',
                default => 'Üzgünüm, mesajınızı anlayamadım. Ajandanızla ilgili nasıl yardımcı olabilirim?'
            };

            // Mesajı veritabanına kaydet
            Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Mesaj kaydediliyor", [
                'user_id' => auth()->id() ?? 1,
                'message_type' => $analysis['type'],
                'is_successful' => true
            ]);

            Message::create([
                'user_id' => auth()->id() ?? 1,
                'user_message' => $message,
                'ai_response' => $result,
                'ai_analysis' => $analysis,
                'message_type' => $analysis['type'],
                'processed_data' => $analysis['data'],
                'model_used' => $this->provider->getDefaultModel(),
                'is_successful' => true,
                'error_message' => null
            ]);

            Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] İşlem başarıyla tamamlandı");
            return $result;
        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error("[{$requestId}] Hata oluştu", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Hata durumunda mesajı kaydet
            Message::create([
                'user_id' => auth()->id() ?? 1,
                'user_message' => $message,
                'ai_response' => 'Merhaba! Şu anda teknik bir sorun yaşıyorum. Ajandanızla ilgili nasıl yardımcı olabilirim?',
                'ai_analysis' => null,
                'message_type' => 'error',
                'processed_data' => null,
                'model_used' => $this->provider->getDefaultModel(),
                'is_successful' => false,
                'error_message' => $e->getMessage()
            ]);

            return 'Merhaba! Şu anda teknik bir sorun yaşıyorum. Ajandanızla ilgili nasıl yardımcı olabilirim?';
        }
    }
    
    /**
     * Kullanıcı mesajından Task ID'yi çıkarmaya çalışır
     * 
     * @param string $message Kullanıcı mesajı
     * @return int|null Bulunan Task ID veya null
     */
    private function extractTaskIdFromMessage(string $message): ?int
    {
        // Görev ID'si için olası desenler
        $patterns = [
            '/görev\s*#?(\d+)/i',         // "görev #5" veya "görev 5"
            '/görev\s*id\s*:?\s*(\d+)/i', // "görev id: 5" veya "görev id 5"
            '/görev\s*numarası\s*:?\s*(\d+)/i', // "görev numarası: 5"
            '/id\s*:?\s*(\d+)/i',         // "id: 5" veya "id 5"
            '/numara\s*:?\s*(\d+)/i',     // "numara: 5"
            '/no\s*:?\s*(\d+)/i',         // "no: 5"
            '/#(\d+)/i',                  // "#5"
            '/(\d+)\s*numaralı\s*görev/i', // "5 numaralı görev"
            '/(\d+)\s*nolu\s*görev/i',    // "5 nolu görev"
            '/(\d+)\s*no\'?lu\s*görev/i'  // "5 no'lu görev" veya "5 nolu görev"
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return (int)$matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Metod çağrılarını loglayan yardımcı fonksiyon
     */
    protected function loggedMethod(string $requestId, string $methodName, callable $callback)
    {
        Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] {$methodName} metodu başladı");
        
        try {
            $result = $callback();
            
            Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] {$methodName} metodu tamamlandı", [
                'result' => is_string($result) ? $result : json_encode($result)
            ]);
            
            return $result;
        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error("[{$requestId}] {$methodName} metodunda hata", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Takvim sorgulama işlemlerini yönetir
     * 
     * @param array $data İşlem verileri
     * @return string Yanıt
     */
    protected function handleCalendarQuery(array $data): string
    {
        $requestId = uniqid('cal_');
        Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Takvim sorgusu başladı", [
            'data' => $data
        ]);

        try {
            // Data kontrolü
            $startDate = null;
            $endDate = null;
            $userId = auth()->id(); // Varsayılan olarak giriş yapmış kullanıcı ID'si
            
            // Tarih değerlerini belirle
            if (isset($data['start_date'])) {
                $startDate = $data['start_date'];
            }
            if (isset($data['end_date'])) {
                $endDate = $data['end_date'];
            }
            
            // Kullanıcı ID kontrolü
            if (isset($data['user_id']) || isset($data['kullanıcı_id'])) {
                $userId = $data['user_id'] ?? $data['kullanıcı_id'];
            }
            
            // Eğer kullanıcı ID'si null ise 1 olarak ayarla (misafir kullanıcı)
            if ($userId === null) {
                $userId = 1;
            }
            
            // Gerekli bilgiler alt dizisinde olabilir
            if (isset($data['gerekli_bilgiler'])) {
                $gerekli = $data['gerekli_bilgiler'];
                
                // Tarih bilgisi kontrol et
                if (isset($gerekli['tarih'])) {
                    $tarih = $gerekli['tarih'];
                    
                    // "yarın", "bugün", "gelecek hafta" gibi değerleri işle
                    if ($tarih === 'yarın') {
                        $startDate = Carbon::tomorrow()->startOfDay()->format('Y-m-d H:i:s');
                        $endDate = Carbon::tomorrow()->endOfDay()->format('Y-m-d H:i:s');
                    } elseif ($tarih === 'bugün') {
                        $startDate = Carbon::today()->startOfDay()->format('Y-m-d H:i:s');
                        $endDate = Carbon::today()->endOfDay()->format('Y-m-d H:i:s');
                    } elseif ($tarih === 'gelecek hafta' || $tarih === 'önümüzdeki hafta') {
                        $startDate = Carbon::now()->addWeek()->startOfWeek()->format('Y-m-d H:i:s');
                        $endDate = Carbon::now()->addWeek()->endOfWeek()->format('Y-m-d H:i:s');
                    } elseif ($tarih === 'bu hafta') {
                        $startDate = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
                        $endDate = Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');
                    } elseif ($tarih === 'bu ay') {
                        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
                        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
                    }
                }
                
                // Spesifik başlangıç ve bitiş tarihleri
                if (isset($gerekli['start_date'])) {
                    $startDate = $gerekli['start_date'];
                }
                if (isset($gerekli['end_date'])) {
                    $endDate = $gerekli['end_date'];
                }
                
                // Gerekli bilgilerde kullanıcı ID'si kontrolü
                if (isset($gerekli['user_id']) || isset($gerekli['kullanıcı_id'])) {
                    $userId = $gerekli['user_id'] ?? $gerekli['kullanıcı_id'];
                }
            }
            
            // Eğer tarihler belirtilmemişse bugünü kullan
            if (!$startDate) {
                $startDate = Carbon::today()->startOfDay()->format('Y-m-d H:i:s');
            }
            if (!$endDate) {
                $endDate = Carbon::today()->endOfDay()->format('Y-m-d H:i:s');
            }
            
            $parsedStartDate = Carbon::parse($startDate);
            $parsedEndDate = Carbon::parse($endDate);
            
            // İçerik tipini belirle (varsayılan: her ikisi de)
            $contentType = 'both';
            
            if (isset($data['içerik_tipi']) || isset($data['content_type'])) {
                $contentType = $data['içerik_tipi'] ?? $data['content_type'];
            } elseif (isset($data['gerekli_bilgiler']['içerik_tipi']) || isset($data['gerekli_bilgiler']['content_type'])) {
                $contentType = $data['gerekli_bilgiler']['içerik_tipi'] ?? $data['gerekli_bilgiler']['content_type'];
            }
            
            // Tarih aralığına göre etkinlikleri ve/veya görevleri getir
            $events = [];
            $tasks = [];
            
            // İçerik tipine göre etkinlik ve/veya görevleri getir
            if ($contentType == 'events' || $contentType == 'etkinlikler' || $contentType == 'both' || $contentType == 'her ikisi') {
                $events = Event::whereBetween('start_date', [$parsedStartDate, $parsedEndDate])
                              ->where('user_id', $userId) // Kullanıcı filtresi ekle
                              ->orderBy('start_date')
                              ->get(['id', 'title', 'description', 'start_date', 'end_date', 'location', 'all_day', 'user_id', 'created_at', 'updated_at']); // ID'yi açıkça belirtiyoruz
            }
            
            if ($contentType == 'tasks' || $contentType == 'görevler' || $contentType == 'both' || $contentType == 'her ikisi') {
                $tasks = Task::whereBetween('due_date', [$parsedStartDate, $parsedEndDate])
                             ->where('user_id', $userId) // Kullanıcı filtresi ekle
                             ->orderBy('due_date')
                             ->get(['id', 'title', 'description', 'due_date', 'status', 'priority', 'is_completed', 'user_id', 'created_at', 'updated_at']); // ID'yi açıkça belirtiyoruz
            }
            
            // Eğer hem etkinlik hem de görev yoksa
            if (count($events) == 0 && count($tasks) == 0) {
                return 'Bu tarih aralığında herhangi bir etkinlik veya görev bulunmuyor.';
            }
            
            // Verileri LLM ile özetle
            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            $prompt = [
                'ÖNEMLİ: Şu anki gerçek tarih ve saat: ' . $currentDate . ' Bu tarihi referans alarak işlem yap.',
                'Kullanıcıya aşağıdaki verilerle ilgili doğal bir dille özet yap:',
                'Tarih Aralığı: ' . $parsedStartDate->format('d.m.Y') . ' - ' . $parsedEndDate->format('d.m.Y'),
            ];
            
            // Etkinlikler varsa ekle
            if (count($events) > 0) {
                $prompt[] = 'Etkinlikler: ' . json_encode($events, JSON_UNESCAPED_UNICODE);
            } else {
                $prompt[] = 'Etkinlikler: Bu tarih aralığında hiç etkinlik yok.';
            }
            
            // Görevler varsa ekle
            if (count($tasks) > 0) {
                $prompt[] = 'Görevler: ' . json_encode($tasks, JSON_UNESCAPED_UNICODE);
            } else {
                $prompt[] = 'Görevler: Bu tarih aralığında hiç görev yok.';
            }
            
            $prompt[] = 'Yanıtını kullanıcı anlayacak şekilde Türkçe olarak ver ve şu anki tarihle karşılaştırmalı ifadeler kullan.';
            $prompt[] = 'Yanıtta mutlaka etkinlik ve görevlerin ID bilgilerini de belirt, kullanıcının bu ID\'leri görebilmesi önemlidir.';
            
            // LLM ile özetle
            $summary = $this->provider->generateContent($prompt);
            
            Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Takvim sorgusu tamamlandı", [
                'events_count' => count($events ?? []),
                'tasks_count' => count($tasks ?? []),
                'user_id' => $userId
            ]);

            return $summary;
        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error("[{$requestId}] Takvim sorgusunda hata", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Belirli bir gün veya tarih aralığı için özet bilgi döndürür
     * 
     * @param array $data İşlem verileri
     * @return string Yanıt
     */
    protected function handleCalendarSummary(array $data): string
    {
        $requestId = uniqid('sum_');
        Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Takvim özeti başladı", [
            'data' => $data
        ]);

        try {
            $startDate = null;
            $endDate = null;
            $period = $data['period'] ?? 'today'; // today, tomorrow, date_range
            $userId = auth()->id(); // Varsayılan olarak giriş yapmış kullanıcı ID'si
            
            // Kullanıcı ID kontrolü
            if (isset($data['user_id']) || isset($data['kullanıcı_id'])) {
                $userId = $data['user_id'] ?? $data['kullanıcı_id'];
            }
            
            // Eğer kullanıcı ID'si null ise 1 olarak ayarla (misafir kullanıcı)
            if ($userId === null) {
                $userId = 1;
            }
            
            // Tarih aralığını belirle
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'tomorrow':
                    $startDate = Carbon::tomorrow()->startOfDay();
                    $endDate = Carbon::tomorrow()->endOfDay();
                    break;
                case 'date_range':
                    $startDate = Carbon::parse($data['start_date']);
                    $endDate = Carbon::parse($data['end_date']);
                    break;
            }

            if (!$startDate || !$endDate) {
                throw new Exception('Geçerli tarih aralığı belirlenemedi.');
            }

            // Etkinlikleri ve görevleri getir
            $events = Event::whereBetween('start_date', [$startDate, $endDate])
                          ->where('user_id', $userId) // Kullanıcı filtresi ekle
                          ->orderBy('start_date')
                          ->get(['id', 'title', 'description', 'start_date', 'end_date', 'location', 'all_day', 'user_id', 'created_at', 'updated_at']); // ID'yi açıkça belirtiyoruz

            $tasks = Task::whereBetween('due_date', [$startDate, $endDate])
                         ->where('user_id', $userId) // Kullanıcı filtresi ekle
                         ->orderBy('due_date')
                         ->get(['id', 'title', 'description', 'due_date', 'status', 'priority', 'is_completed', 'user_id', 'created_at', 'updated_at']); // ID'yi açıkça belirtiyoruz

            if ($events->isEmpty() && $tasks->isEmpty()) {
                if ($period === 'today') {
                    return 'Bugün için planlanmış herhangi bir etkinlik veya görev bulunmuyor.';
                } elseif ($period === 'tomorrow') {
                    return 'Yarın için planlanmış herhangi bir etkinlik veya görev bulunmuyor.';
                } else {
                    return $startDate->format('d.m.Y') . ' ile ' . $endDate->format('d.m.Y') . ' tarihleri arasında herhangi bir etkinlik veya görev bulunmuyor.';
                }
            }

            // Özet oluştur
            $prompt = [
                'ÖNEMLİ: Şu anki gerçek tarih ve saat: ' . now()->format('Y-m-d H:i:s'),
                'Aşağıdaki veriler için özet oluştur:',
                'Tarih Aralığı: ' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
                'Etkinlikler: ' . json_encode($events->toArray(), JSON_UNESCAPED_UNICODE),
                'Görevler: ' . json_encode($tasks->toArray(), JSON_UNESCAPED_UNICODE),
                'Lütfen yanıtını aşağıdaki formatta ver:',
                '1. Önce tarih aralığını belirt',
                '2. Her gün için ayrı bir başlık kullan',
                '3. Her günün altında etkinlikleri ve görevleri listele',
                '4. Görevlerin önceliklerini ve durumlarını belirt',
                '5. Etkinliklerin saatlerini ve konumlarını belirt',
                '6. Yanıtını kullanıcı dostu ve doğal bir dille ver',
                '7. Her etkinlik ve görev için mutlaka ID bilgisini de göster (örnek: "Görev #12: Toplantı notları", "Etkinlik #5: Doktor randevusu")'
            ];

            $summary = $this->provider->generateContent($prompt);
            
            Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Takvim özeti tamamlandı", [
                'period' => $period,
                'start_date' => $startDate?->format('Y-m-d H:i:s'),
                'end_date' => $endDate?->format('Y-m-d H:i:s'),
                'events_count' => $events?->count(),
                'tasks_count' => $tasks?->count(),
                'user_id' => $userId
            ]);

            return $summary;
        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error("[{$requestId}] Takvim özetinde hata", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Görev veya Etkinlik güncelleme isteğini yönetir.
     * ID belirtilmemişse, başlığa göre arama yapar ve seçenek sunar.
     * 
     * @param array $data LLM analiz verisi
     * @param string $type 'task' veya 'event'
     * @return string Yanıt
     */
    protected function handleUpdateRequest(array $data, string $type): string
    {
        $idKey = ($type === 'task') ? 'task_id' : 'event_id';
        $titleKey = 'title';
        $handlerClass = ($type === 'task') ? $this->taskHandler : $this->eventHandler;
        $updateMethod = ($type === 'task') ? 'handleTaskUpdate' : 'handleEventUpdate';
        $itemModel = ($type === 'task') ? Task::class : Event::class;
        $itemName = ($type === 'task') ? 'görev' : 'etkinlik';

        // ID doğrudan belirtilmiş mi kontrol et
        if (isset($data[$idKey]) && !empty($data[$idKey])) {
            // ID varsa doğrudan güncelleme metodunu çağır
            return $handlerClass->{$updateMethod}($data);
        }
        
        // ID yoksa, başlık veya açıklamaya göre arama yap
        $searchTerm = $data[$titleKey] ?? ($data['description'] ?? null);
        if (empty($searchTerm)) {
            return "Güncellemek istediğiniz {$itemName} için ID veya başlık belirtmelisiniz.";
        }
        
        // Kullanıcının ID'sini al
        $userId = $data['user_id'] ?? auth()->id() ?? 1;
        
        // Başlığa göre ara
        $items = $itemModel::where('user_id', $userId)
                          ->where(function ($query) use ($searchTerm) {
                              $query->where('title', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                          })
                          ->limit(5) // Çok fazla sonuç dönmemesi için limit
                          ->get();
        
        // Arama sonuçlarını işle
        if ($items->isEmpty()) {
            return "'{$searchTerm}' ile eşleşen bir {$itemName} bulunamadı.";
        }
        
        if ($items->count() === 1) {
            // Tek sonuç varsa, ID'yi data'ya ekle ve güncelleme metodunu çağır
            $data[$idKey] = $items->first()->id;
            return $handlerClass->{$updateMethod}($data);
        }
        
        // Birden fazla sonuç varsa, kullanıcıya seçenek sun
        $responseText = "'{$searchTerm}' ile eşleşen birden fazla {$itemName} buldum. Hangisini güncellemek istersiniz? Lütfen numarasını belirtin:\n";
        foreach ($items as $index => $item) {
            $dateInfo = '';
            if ($type === 'task' && $item->due_date) {
                $dateInfo = ' (' . Carbon::parse($item->due_date)->format('d.m.Y') . ')';
            } elseif ($type === 'event' && $item->start_date) {
                $dateInfo = ' (' . Carbon::parse($item->start_date)->format('d.m.Y H:i') . ')';
            }
            $responseText .= ($index + 1) . ". {$itemName} #{$item->id}: {$item->title}{$dateInfo}\n";
        }
        
        return $responseText;
    }
} 