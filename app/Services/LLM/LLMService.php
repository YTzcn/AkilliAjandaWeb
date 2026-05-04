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
     * Kullanıcı mesajını ReAct döngüsü ile işler ve yanıt verir
     * 
     * @param string $message Kullanıcı mesajı
     * @return string Yanıt
     */
    public function processUserMessage(string $message): string
    {
        $requestId = uniqid('llm_');
        $maxIterations = 5;
        $iteration = 0;
        $scratchpad = []; // Mevcut döngüdeki Thought, Action, Observation geçmişi
        
        Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] ReAct döngüsü başladı", [
            'message' => $message,
            'provider' => get_class($this->provider)
        ]);

        try {
            // 1. Konuşma geçmişini getir (Son 5 mesaj)
            $history = Message::where('user_id', auth()->id() ?? 1)
                ->latest()
                ->limit(5)
                ->get()
                ->reverse()
                ->map(function($msg) {
                    return [
                        'user' => $msg->user_message,
                        'assistant' => $msg->ai_response
                    ];
                })->toArray();

            while ($iteration < $maxIterations) {
                $iteration++;
                
                // 2. Prompt oluştur
                $prompt = $this->buildReActPrompt($message, $history, $scratchpad);
                
                // 3. LLM'den yanıt al
                Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] İterasyon {$iteration} başladı");
                $analysis = $this->provider->processMessage($prompt);
                
                $thought = $analysis['thought'] ?? 'Düşünülüyor...';
                $action = $analysis['action'] ?? 'final_answer';
                $actionInput = $analysis['action_input'] ?? [];

                Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] LLM Kararı", [
                    'thought' => $thought,
                    'action' => $action,
                    'input' => $actionInput
                ]);

                // 4. Eğer nihai yanıt ise döngüden çık
                if ($action === 'final_answer' || $action === 'sohbet') {
                    if (is_array($actionInput)) {
                        $finalResult = $actionInput['message'] ?? 
                                      $actionInput['mesaj'] ?? 
                                      $actionInput['text'] ?? 
                                      $actionInput['response'] ?? 
                                      $actionInput['answer'] ?? 
                                      (count($actionInput) === 1 ? reset($actionInput) : json_encode($actionInput, JSON_UNESCAPED_UNICODE));
                    } else {
                        $finalResult = $actionInput;
                    }
                    break;
                }

                // 5. Aracı yürüt
                $observation = $this->executeTool($requestId, $action, $actionInput);
                
                // 6. Scratchpad'e ekle
                $scratchpad[] = [
                    'thought' => $thought,
                    'action' => $action,
                    'action_input' => $actionInput,
                    'observation' => $observation
                ];
                
                Log::channel(self::LOG_CHANNEL)->info("[{$requestId}] Gözlem", ['observation' => $observation]);
            }

            $result = $finalResult ?? 'Üzgünüm, işleminizi tamamlayamadım. Lütfen tekrar dener misiniz?';

            // 7. Mesajı veritabanına kaydet
            Message::create([
                'user_id' => auth()->id() ?? 1,
                'user_message' => $message,
                'ai_response' => $result,
                'ai_analysis' => [
                    'iterations' => $iteration,
                    'scratchpad' => $scratchpad,
                    'final_analysis' => $analysis
                ],
                'message_type' => $analysis['action'] ?? 'react_completion',
                'processed_data' => $scratchpad,
                'model_used' => $this->provider->getDefaultModel(),
                'is_successful' => true
            ]);

            return $result;

        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error("[{$requestId}] ReAct hatası", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Message::create([
                'user_id' => auth()->id() ?? 1,
                'user_message' => $message,
                'ai_response' => 'Şu anda teknik bir sorun yaşıyorum.',
                'is_successful' => false,
                'error_message' => $e->getMessage()
            ]);

            return 'Şu anda teknik bir sorun yaşıyorum. Lütfen biraz sonra tekrar deneyin.';
        }
    }

    /**
     * ReAct prompt'unu inşa eder
     */
    protected function buildReActPrompt(string $message, array $history, array $scratchpad): string
    {
        $currentDate = Carbon::now()->format('Y-m-d H:i:s');
        
        $tools = [
            'takvim_sorgulama' => 'Belirli bir tarih aralığındaki etkinlik ve görevleri listeler. Parametreler: start_date, end_date (YYYY-MM-DD HH:MM:SS), content_type (events, tasks, both)',
            'takvim_ozet' => 'Bugün veya yarın için genel plan özeti verir. Parametreler: period (today, tomorrow)',
            'yeni_etkinlik' => 'Yeni bir takvim etkinliği oluşturur. Parametreler: title, start_date, end_date, description, location',
            'yeni_görev' => 'Yeni bir görev ekler. Parametreler: title, due_date, description, priority (1:düşük, 2:orta, 3:yüksek)',
            'gorev_guncelleme' => 'Mevcut bir görevi günceller. Parametreler: task_id (zorunlu), title, status (pending, in_progress, completed, cancelled), priority',
            'etkinlik_guncelleme' => 'Mevcut bir etkinliği günceller. Parametreler: event_id (zorunlu), title, start_date, end_date, location',
            'ozet_bilgi' => 'Kullanıcının ajandası hakkında genel istatistiksel bilgi verir.'
        ];

        $prompt = "Sen bir Akıllı Ajanda Uygulamasının yetenekli asistanısın. ReAct (Thought -> Action -> Observation) döngüsünü kullanarak kullanıcı isteklerini yerine getirirsin.\n\n";
        $prompt .= "Şu anki tarih ve saat: {$currentDate}\n\n";
        
        $prompt .= "Kullanabileceğin Araçlar:\n";
        foreach ($tools as $name => $desc) {
            $prompt .= "- {$name}: {$desc}\n";
        }
        $prompt .= "- final_answer: Kullanıcıya nihai cevabı vermek için kullanılır. Parametre: { \"message\": \"kullanıcıya verilecek yanıt\" }\n\n";

        if (!empty($history)) {
            $prompt .= "Önceki Konuşmalar:\n";
            foreach ($history as $h) {
                $prompt .= "Kullanıcı: {$h['user']}\nAsistan: {$h['assistant']}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "Şimdiki İstek: {$message}\n\n";
        
        if (!empty($scratchpad)) {
            $prompt .= "Senin Düşünce Sürecin:\n";
            foreach ($scratchpad as $step) {
                $prompt .= "Thought: {$step['thought']}\n";
                $prompt .= "Action: {$step['action']}\n";
                $prompt .= "Action Input: " . json_encode($step['action_input'], JSON_UNESCAPED_UNICODE) . "\n";
                $prompt .= "Observation: {$step['observation']}\n";
            }
        }

        $prompt .= "\nYanıtını mutlaka şu JSON formatında ver (başkaca metin ekleme):\n";
        $prompt .= "{\n  \"thought\": \"Neden bu eylemi seçtiğini açıkla\",\n  \"action\": \"arac_adi\",\n  \"action_input\": { \"param1\": \"değer1\" }\n}";
        
        return $prompt;
    }

    /**
     * Seçilen aracı yürütür
     */
    protected function executeTool(string $requestId, string $action, array $data): string
    {
        try {
            return match($action) {
                'takvim_sorgulama' => $this->handleCalendarQuery($data),
                'takvim_ozet' => $this->handleCalendarSummary($data),
                'yeni_etkinlik' => $this->eventHandler->handleNewEvent($data),
                'yeni_görev' => $this->taskHandler->handleNewTask($data),
                'gorev_guncelleme' => $this->handleUpdateRequest($data, 'task'),
                'etkinlik_guncelleme' => $this->handleUpdateRequest($data, 'event'),
                'ozet_bilgi' => $this->eventHandler->handleSummaryRequest($data, $this->provider),
                default => "Hata: '{$action}' adında bir araç bulunamadı."
            };
        } catch (Exception $e) {
            return "Araç yürütme hatası: " . $e->getMessage();
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