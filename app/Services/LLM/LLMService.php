<?php

namespace App\Services\LLM;

use App\Services\LLM\Providers\ProviderInterface;
use App\Services\LLM\Handlers\TaskHandler;
use App\Services\LLM\Handlers\EventHandler;
use App\Models\Task;
use App\Models\Event;
use Carbon\Carbon;
use Exception;

class LLMService
{
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
        try {
            // Mesajı LLM ile analiz et
            $analysis = $this->provider->processMessage($message);
            
            // İşlem türüne göre uygun fonksiyonu çağır
            return match($analysis['type']) {
                'takvim_sorgulama' => $this->handleCalendarQuery($analysis['data']),
                'yeni_etkinlik' => $this->eventHandler->handleNewEvent($analysis['data']),
                'yeni_görev' => $this->taskHandler->handleNewTask($analysis['data']),
                'gorev_guncelleme' => $this->taskHandler->handleTaskUpdate($analysis['data']),
                'ozet_bilgi' => $this->eventHandler->handleSummaryRequest($analysis['data'], $this->provider),
                default => 'Üzgünüm, mesajınızı anlayamadım. Lütfen farklı bir şekilde ifade eder misiniz?'
            };
        } catch (Exception $e) {
            // Hata durumunda temel bir yanıt dön
            return 'Hata oluştu: ' . $e->getMessage();
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
        try {
            // Data kontrolü
            $startDate = null;
            $endDate = null;
            
            // Tarih değerlerini belirle
            if (isset($data['start_date'])) {
                $startDate = $data['start_date'];
            }
            if (isset($data['end_date'])) {
                $endDate = $data['end_date'];
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
                              ->orderBy('start_date')
                              ->get();
            }
            
            if ($contentType == 'tasks' || $contentType == 'görevler' || $contentType == 'both' || $contentType == 'her ikisi') {
                $tasks = Task::whereBetween('due_date', [$parsedStartDate, $parsedEndDate])
                             ->orderBy('due_date')
                             ->get();
            }
            
            // Kullanıcıya göre filtrele (eğer belirtildiyse)
            if (isset($data['user_id']) || isset($data['kullanıcı_id'])) {
                $userId = $data['user_id'] ?? $data['kullanıcı_id'];
                
                if (!empty($events)) {
                    $events = $events->where('user_id', $userId);
                }
                if (!empty($tasks)) {
                    $tasks = $tasks->where('user_id', $userId);
                }
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
            
            // LLM ile özetle
            $summary = $this->provider->generateContent($prompt);
            
            return $summary;
        } catch (Exception $e) {
            return 'Takvim sorgulanırken bir hata oluştu: ' . $e->getMessage();
        }
    }
} 