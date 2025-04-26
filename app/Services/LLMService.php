<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\Event;

class LLMService
{
    public function processUserMessage(string $message)
    {
        try {
            // Mevcut modelleri listele
            $modelResponse = Gemini::models()->list();
            $availableModel = 'gemini-1.5-pro'; // Varsayılan
            
            // Modelleri kontrol et ve kullanabileceğimiz bir model seç
            if (isset($modelResponse->models)) {
                foreach ($modelResponse->models as $model) {
                    // Son model adını kullan
                    $modelName = str_replace('models/', '', $model->name ?? '');
                    
                    if (str_contains($modelName, 'gemini-1.5-pro')) {
                        $availableModel = $modelName;
                        break;
                    }
                }
            }
            
            // Gemini API kullanarak mesajı analiz et
            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            $response = Gemini::generativeModel($availableModel)->generateContent([
                'Sen bir Akıllı Ajanda Uygulamasının Asistanısın. Kullanıcılar seninle konuşarak ajanda üzerindeki işlemlerini yapabilirler.',
                'ÖNEMLİ: Şu anki gerçek tarih ve saat: ' . $currentDate . ' Bu tarihi referans alarak işlem yap.',
                'Tarih ile ilgili tüm kararlarında bugün olarak yukarıdaki tarihi kabul et ve buna göre hesaplama yap.',
                'Kullanıcı mesajı: ' . $message,
                'Lütfen aşağıdaki adımları izle:',
                '1. Kullanıcının mesajının içeriğini analiz et',
                '2. Kullanıcının ne yapmak istediğini belirle: takvim sorgulama, etkinlik ekleme, görev güncelleme, görev ekleme veya özet bilgi isteme',
                '3. Kullanıcının mesajındaki tarihleri, kişileri, etkinlik/görev detaylarını belirle',
                '4. İçerik türünü belirle: Kullanıcı etkinlikleri mi, görevleri mi yoksa her ikisini birden mi sorguluyor',
                '5. Görevler için öncelik ve durum değerlerini kullanıcının ifade tarzından çıkar:',
                '   - Öncelik: 1 (düşük), 2 (orta), 3 (yüksek/acil)',
                '   - İfadede "önemli", "acil", "hemen", "kritik" gibi kelimeler varsa öncelik 3 (yüksek) olmalı',
                '   - İfadede "önemsiz", "acil değil", "vakit buldukça" gibi ifadeler varsa öncelik 1 (düşük) olmalı',
                '   - Belirgin bir vurgu yoksa öncelik 2 (orta) kullan',
                '   - Durum: "beklemede" (pending), "devam_ediyor" (in_progress), "tamamlandı" (completed), "iptal" (cancelled)',
                '   - İfadede "başla", "başlayacağım", "üzerinde çalışıyorum" gibi ifadeler varsa durum "devam_ediyor" olmalı',
                '   - İfadede "tamamlandı", "bitti", "hallettim" gibi kelimeler varsa durum "tamamlandı" olmalı',
                '   - İfadede "iptal", "vazgeçtim", "yapılmayacak" varsa durum "iptal" olmalı',
                '   - Belirgin bir durum belirtilmemişse varsayılan olarak "beklemede" kullan',
                '6. Yanıtını tam olarak aşağıdaki JSON formatında ver (başka bir metin veya açıklama olmadan):',
                '{',
                '  "type": "işlem_tipi", // takvim_sorgulama, yeni_etkinlik, yeni_görev, gorev_guncelleme, ozet_bilgi',
                '  "data": {',
                '    "title": "Etkinlik/Görev başlığı", // Etkinlik veya görev başlığı',
                '    "start_date": "YYYY-MM-DD HH:MM:SS", // Başlangıç tarihi ve saati (etkinlikler için), her zaman tam tarih saat kullan',
                '    "end_date": "YYYY-MM-DD HH:MM:SS", // Bitiş tarihi ve saati (etkinlikler için), her zaman tam tarih saat kullan',
                '    "due_date": "YYYY-MM-DD HH:MM:SS", // Bitiş tarihi (görevler için), her zaman tam tarih saat kullan',
                '    "description": "Açıklama", // Varsa açıklama',
                '    "location": "Konum", // Varsa konum (etkinlikler için)',
                '    "task_id": "id", // Görev güncellemesi için ID',
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
                '8. Yanıtını sadece JSON formatında ver. Başka açıklama ekleme, yorum yapma veya metinle cevap verme. JSON yanıtı kod bloğu (```) içinde de verme. JSON dışında hiçbir karakter olmamalıdır.'
            ]);

            // API yanıtını güvenli bir şekilde parse et
            $responseText = $response->text();
            
            // JSON yanıtını temizle - markdown kod bloklarını kaldır
            $cleanedResponse = $this->cleanJsonResponse($responseText);
            
            // JSON'ı parse et
            $analysis = json_decode($cleanedResponse, true);
            
            // JSON doğru parse edildi mi kontrol et
            if (!is_array($analysis) || !isset($analysis['type'])) {
                return "AI yanıtı düzgün parse edilemedi. Lütfen farklı bir şekilde ifade eder misiniz? AI cevabı: " . $responseText;
            }
            
            // Data kontrolü - bazı durumlarda data alanı boş gelebilir
            if (!isset($analysis['data']) || !is_array($analysis['data'])) {
                $analysis['data'] = [];
            }
            
            return match($analysis['type']) {
                'takvim_sorgulama' => $this->handleCalendarQuery($analysis['data']),
                'yeni_etkinlik' => $this->handleNewEvent($analysis['data']),
                'yeni_görev' => $this->handleNewTask($analysis['data']),
                'gorev_guncelleme' => $this->handleTaskUpdate($analysis['data']),
                'ozet_bilgi' => $this->handleSummaryRequest($analysis['data'], $availableModel),
                default => 'Üzgünüm, mesajınızı anlayamadım. Lütfen farklı bir şekilde ifade eder misiniz?'
            };
        } catch (\Exception $e) {
            // Hata durumunda temel bir yanıt dön
            return 'Hata oluştu: ' . $e->getMessage();
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

    private function handleCalendarQuery(array $data): string
    {
        $events = [];
        $tasks = [];
        $contentType = $data['content_type'] ?? 'both';
        $userId = auth()->id() ?? 1;
        
        $startDate = $data['start_date'] ?? Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $endDate = $data['end_date'] ?? Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
        
        // Carbon nesnelerini oluştur
        $startDateObj = new Carbon($startDate);
        $endDateObj = new Carbon($endDate);
        
        // Eğer başlangıç ve bitiş zamanları aynı gün içindeyse ve saatler belirtilmişse, bu bir saat aralığı sorgusudur
        $isTimeQuery = $startDateObj->format('Y-m-d') === $endDateObj->format('Y-m-d') && 
                      ($startDateObj->format('H:i:s') !== '00:00:00' || $endDateObj->format('H:i:s') !== '23:59:59');
        
        // Saat aralığı sorgusu değilse ve endDate'in saati belirtilmemişse, günün sonuna ayarla
        if (!$isTimeQuery && $endDateObj->format('H:i:s') === '00:00:00') {
            $endDateObj = $endDateObj->endOfDay();
            $endDate = $endDateObj->format('Y-m-d H:i:s');
        }
        
        // Hangi içerik türünü çekeceğimizi belirle
        $fetchEvents = $contentType === 'both' || $contentType === 'etkinlikler';
        $fetchTasks = $contentType === 'both' || $contentType === 'görevler';
        
        // Etkinlikleri çek
        if ($fetchEvents) {
            $eventsQuery = Event::where('user_id', $userId)
                ->where(function ($query) use ($startDateObj, $endDateObj) {
                    $query->whereBetween('start_date', [$startDateObj, $endDateObj])
                          ->orWhereBetween('end_date', [$startDateObj, $endDateObj])
                          ->orWhere(function ($q) use ($startDateObj, $endDateObj) {
                              $q->where('start_date', '<=', $startDateObj)
                                ->where('end_date', '>=', $endDateObj);
                          });
                });
            
            // Başlık araması varsa ekle
            if (isset($data['title']) && !empty($data['title'])) {
                $eventsQuery->where('title', 'like', '%' . $data['title'] . '%');
            }
            
            $events = $eventsQuery->get();
        }
        
        // Görevleri çek
        if ($fetchTasks) {
            $tasksQuery = Task::where('user_id', $userId);
            
            // Saat aralığı sorgusu ise, due_date'i saat aralığı içinde kontrol et
            if ($isTimeQuery) {
                $tasksQuery->whereBetween('due_date', [$startDateObj, $endDateObj]);
            } else {
                // Normal tarih aralığı sorgusu
                $tasksQuery->whereDate('due_date', '>=', $startDateObj->format('Y-m-d'))
                          ->whereDate('due_date', '<=', $endDateObj->format('Y-m-d'));
            }
            
            // Başlık araması varsa ekle
            if (isset($data['title']) && !empty($data['title'])) {
                $tasksQuery->where('title', 'like', '%' . $data['title'] . '%');
            }
            
            $tasks = $tasksQuery->get();
        }
        
        // Sonuç mesajını oluştur
        $startDateStr = $startDateObj->format('d.m.Y') . ($isTimeQuery ? ' ' . $startDateObj->format('H:i') : '');
        $endDateStr = $endDateObj->format('d.m.Y') . ($isTimeQuery ? ' ' . $endDateObj->format('H:i') : '');
        
        $message = '';
        
        if ($startDateObj->isSameDay($endDateObj)) {
            $dateInfo = $startDateObj->format('d.m.Y');
            if ($isTimeQuery) {
                $dateInfo .= ' ' . $startDateObj->format('H:i') . ' - ' . $endDateObj->format('H:i');
            }
            $message = "{$dateInfo} tarihinde ";
        } else {
            $message = "{$startDateStr} - {$endDateStr} tarihleri arasında ";
        }
        
        // Başlık filtresi varsa ekle
        if (isset($data['title']) && !empty($data['title'])) {
            $message .= "'{$data['title']}' ile ilgili ";
        }
        
        // İçerik türüne göre mesaj
        if ($contentType === 'etkinlikler') {
            $message .= count($events) . " etkinlik bulunuyor:\n\n";
        } elseif ($contentType === 'görevler') {
            $message .= count($tasks) . " görev bulunuyor:\n\n";
        } else {
            $message .= count($events) + count($tasks) . " etkinlik ve görev bulunuyor:\n\n";
        }
        
        // Etkinlikleri listele
        if ($fetchEvents && count($events) > 0) {
            $message .= "📅 ETKİNLİKLER:\n";
            foreach ($events as $event) {
                $eventStart = new Carbon($event->start_date);
                $eventEnd = new Carbon($event->end_date);
                
                $eventDateStr = $eventStart->format('d.m.Y');
                $eventTimeStr = $eventStart->format('H:i') . ' - ' . $eventEnd->format('H:i');
                
                $message .= "- {$event->title} ({$eventDateStr} {$eventTimeStr})\n";
                if (!empty($event->location)) {
                    $message .= "  📍 {$event->location}\n";
                }
                if (!empty($event->description)) {
                    $message .= "  ℹ️ {$event->description}\n";
                }
                $message .= "\n";
            }
        } elseif ($fetchEvents) {
            $message .= "📅 Belirtilen tarih aralığında etkinlik bulunmuyor.\n\n";
        }
        
        // Görevleri listele
        if ($fetchTasks && count($tasks) > 0) {
            $message .= "✅ GÖREVLER:\n";
            foreach ($tasks as $task) {
                $dueDate = new Carbon($task->due_date);
                $dueDateStr = $dueDate->format('d.m.Y H:i');
                
                // Öncelik emojisi
                $priorityEmoji = $task->priority == 3 ? "🔴" : ($task->priority == 2 ? "🟠" : "🟢");
                
                // Durum emojisi
                $statusEmoji = $task->status == 'completed' ? "✅" : 
                              ($task->status == 'in_progress' ? "⏳" : 
                              ($task->status == 'cancelled' ? "❌" : "⏱️"));
                
                $message .= "- {$task->title} ({$dueDateStr}) {$priorityEmoji} {$statusEmoji}\n";
                if (!empty($task->description)) {
                    $message .= "  ℹ️ {$task->description}\n";
                }
                $message .= "\n";
            }
        } elseif ($fetchTasks) {
            $message .= "✅ Belirtilen tarih aralığında görev bulunmuyor.\n";
        }
        
        return $message;
    }

    private function handleNewEvent(array $data)
    {
        try {
            // Veri gerekli bilgiler içinde olabilir
            $eventData = $data;
            if (isset($data['gerekli_bilgiler'])) {
                $eventData = $data['gerekli_bilgiler'];
            }
            
            // Data kontrolü
            if (!isset($eventData['title']) && !isset($eventData['başlık'])) {
                return 'Etkinlik eklemek için başlık bilgisi eksik.';
            }
            
            // Başlık değerini al
            $title = $eventData['title'] ?? $eventData['başlık'] ?? null;
            
            // Tarih değerlerini al
            $startDate = null;
            $endDate = null;
            
            // Tarih bilgilerini kontrol et - farklı formatlarda gelebilir
            if (isset($eventData['start_date'])) {
                $startDate = Carbon::parse($eventData['start_date']);
            } elseif (isset($eventData['başlangıç_tarihi'])) {
                $startDate = Carbon::parse($eventData['başlangıç_tarihi']);
            } elseif (isset($eventData['tarih']) && isset($eventData['saat'])) {
                $startDate = Carbon::parse($eventData['tarih'] . ' ' . $eventData['saat']);
                $endDate = Carbon::parse($eventData['tarih'] . ' ' . $eventData['saat'])->addHour();
            }
            
            if (isset($eventData['end_date'])) {
                $endDate = Carbon::parse($eventData['end_date']);
            } elseif (isset($eventData['bitiş_tarihi'])) {
                $endDate = Carbon::parse($eventData['bitiş_tarihi']);
            }
            
            // Eğer bitiş tarihi yoksa, başlangıç tarihinden 1 saat sonra olarak ayarla
            if ($startDate && !$endDate) {
                $endDate = (clone $startDate)->addHour();
            }
            
            if (!$startDate || !$endDate) {
                return 'Etkinlik için geçerli tarih bilgileri bulunamadı.';
            }
            
            // Etkinliği oluştur
            $event = Event::create([
                'title' => $title,
                'description' => $eventData['description'] ?? $eventData['açıklama'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'location' => $eventData['location'] ?? $eventData['konum'] ?? null,
                'user_id' => auth()->id() ?? 1, // Mevcut giriş yapmış kullanıcı veya varsayılan olarak 1
                'all_day' => $eventData['all_day'] ?? $eventData['tüm_gün'] ?? false
            ]);

            return "Yeni etkinlik başarıyla eklendi: {$event->title}";
        } catch (\Exception $e) {
            return 'Etkinlik eklenirken bir hata oluştu: ' . $e->getMessage();
        }
    }

    private function handleTaskUpdate(array $data)
    {
        try {
            // Veri gerekli bilgiler içinde olabilir
            $taskData = $data;
            if (isset($data['gerekli_bilgiler'])) {
                $taskData = $data['gerekli_bilgiler'];
            }
            
            // Data kontrolü
            if (!isset($taskData['task_id']) && !isset($taskData['görev_id'])) {
                return 'Görev güncellemek için görev ID bilgisi eksik.';
            }
            
            $taskId = $taskData['task_id'] ?? $taskData['görev_id'] ?? null;
            $task = Task::find($taskId);
            
            if (!$task) {
                // Görev bulunamadı, yeni görev oluşturmayı deneyelim
                if (isset($taskData['title']) || isset($taskData['başlık'])) {
                    $title = $taskData['title'] ?? $taskData['başlık'];
                    
                    // Tarih kontrolü
                    $dueDate = null;
                    if (isset($taskData['due_date']) || isset($taskData['bitiş_tarihi'])) {
                        $dueDate = Carbon::parse($taskData['due_date'] ?? $taskData['bitiş_tarihi']);
                    } else {
                        $dueDate = Carbon::today()->endOfDay();
                    }
                    
                    // Durum bilgisini al ve İngilizce karşılıklara çevir
                    $status = $taskData['status'] ?? $taskData['durum'] ?? 'beklemede';
                    $statusMap = [
                        'beklemede' => 'pending',
                        'devam_ediyor' => 'in_progress', 
                        'tamamlandı' => 'completed',
                        'iptal' => 'cancelled'
                    ];
                    $status = $statusMap[$status] ?? 'pending';
                    
                    // Öncelik bilgisini al
                    $priority = $taskData['priority'] ?? $taskData['öncelik'] ?? 2; // Varsayılan orta öncelik
                    if (is_string($priority)) {
                        // Metin olarak öncelik belirtilmişse sayıya çevir
                        $priorityMap = [
                            'düşük' => 1,
                            'normal' => 2, 
                            'orta' => 2,
                            'yüksek' => 3,
                            'acil' => 3,
                            'kritik' => 3,
                            'low' => 1,
                            'medium' => 2,
                            'high' => 3,
                            'urgent' => 3,
                            'critical' => 3
                        ];
                        $priority = $priorityMap[strtolower($priority)] ?? 2;
                    }
                    
                    // İs_completed değerini ayarla
                    $isCompleted = $status === 'completed';
                    
                    // Yeni görevi oluştur
                    $task = Task::create([
                        'title' => $title,
                        'description' => $taskData['description'] ?? $taskData['açıklama'] ?? null,
                        'due_date' => $dueDate,
                        'status' => $status,
                        'priority' => $priority,
                        'is_completed' => $isCompleted,
                        'user_id' => auth()->id() ?? 1
                    ]);
                    
                    return "Yeni görev oluşturuldu: {$task->title}";
                }
                
                return 'Belirtilen görev bulunamadı ve yeni görev oluşturmak için yeterli bilgi yok.';
            }

            // Güncelleme değerlerini kontrol et
            $updateData = [];
            
            if (isset($taskData['title']) || isset($taskData['başlık'])) {
                $updateData['title'] = $taskData['title'] ?? $taskData['başlık'];
            }
            
            if (isset($taskData['description']) || isset($taskData['açıklama'])) {
                $updateData['description'] = $taskData['description'] ?? $taskData['açıklama'];
            }
            
            if (isset($taskData['status']) || isset($taskData['durum'])) {
                $status = $taskData['status'] ?? $taskData['durum'];
                $statusMap = [
                    'beklemede' => 'pending',
                    'devam_ediyor' => 'in_progress', 
                    'tamamlandı' => 'completed',
                    'iptal' => 'cancelled'
                ];
                $updateData['status'] = $statusMap[$status] ?? 'pending';
                
                // Durum tamamlandı ise is_completed = true olmalı
                if ($updateData['status'] === 'completed') {
                    $updateData['is_completed'] = true;
                }
            }
            
            if (isset($taskData['due_date']) || isset($taskData['bitiş_tarihi'])) {
                $updateData['due_date'] = Carbon::parse($taskData['due_date'] ?? $taskData['bitiş_tarihi']);
            }
            
            if (isset($taskData['priority']) || isset($taskData['öncelik'])) {
                $priority = $taskData['priority'] ?? $taskData['öncelik'];
                if (is_string($priority)) {
                    // Metin olarak öncelik belirtilmişse sayıya çevir
                    $priorityMap = [
                        'düşük' => 1,
                        'normal' => 2, 
                        'orta' => 2,
                        'yüksek' => 3,
                        'acil' => 3,
                        'kritik' => 3,
                        'low' => 1,
                        'medium' => 2,
                        'high' => 3,
                        'urgent' => 3,
                        'critical' => 3
                    ];
                    $updateData['priority'] = $priorityMap[strtolower($priority)] ?? 2;
                } else {
                    $updateData['priority'] = $priority;
                }
            }
            
            if (isset($taskData['is_completed']) || isset($taskData['tamamlandı'])) {
                $isCompleted = $taskData['is_completed'] ?? $taskData['tamamlandı'];
                if (is_string($isCompleted)) {
                    $updateData['is_completed'] = in_array(strtolower($isCompleted), ['true', 'evet', 'yes', 'tamamlandı', 'completed']);
                } else {
                    $updateData['is_completed'] = $isCompleted;
                }
                
                // Tamamlandı olarak işaretlenmişse statü de completed olmalı
                if ($updateData['is_completed'] === true && !isset($updateData['status'])) {
                    $updateData['status'] = 'completed';
                }
            }
            
            $task->update($updateData);

            return "Görev başarıyla güncellendi: {$task->title}";
        } catch (\Exception $e) {
            return 'Görev güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }

    private function handleSummaryRequest(array $data, string $modelName)
    {
        try {
            // Veri gerekli bilgiler içinde olabilir
            $summaryData = $data;
            if (isset($data['gerekli_bilgiler'])) {
                $summaryData = $data['gerekli_bilgiler'];
            }
            
            // Tarih değerlerini belirleme
            $startDate = null;
            $endDate = null;
            
            // Direkt tarih değerleri
            if (isset($summaryData['start_date'])) {
                $startDate = Carbon::parse($summaryData['start_date']);
            } elseif (isset($summaryData['başlangıç_tarihi'])) {
                $startDate = Carbon::parse($summaryData['başlangıç_tarihi']);
            }
            
            if (isset($summaryData['end_date'])) {
                $endDate = Carbon::parse($summaryData['end_date']);
            } elseif (isset($summaryData['bitiş_tarihi'])) {
                $endDate = Carbon::parse($summaryData['bitiş_tarihi']);
            }
            
            // Özel dönem ifadeleri
            if (isset($summaryData['dönem'])) {
                $period = $summaryData['dönem'];
                
                if ($period === 'önümüzdeki hafta' || $period === 'gelecek hafta') {
                    $startDate = Carbon::now()->addWeek()->startOfWeek();
                    $endDate = Carbon::now()->addWeek()->endOfWeek();
                } elseif ($period === 'bu hafta') {
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                } elseif ($period === 'bu ay') {
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                } elseif ($period === 'önümüzdeki ay' || $period === 'gelecek ay') {
                    $startDate = Carbon::now()->addMonth()->startOfMonth();
                    $endDate = Carbon::now()->addMonth()->endOfMonth();
                }
            }
            
            // Varsayılan değerler
            if (!$startDate) {
                $startDate = Carbon::now()->startOfWeek();
            }
            if (!$endDate) {
                $endDate = Carbon::now()->endOfWeek();
            }

            // Etkinlik ve görevleri getir
            $events = Event::whereBetween('start_date', [$startDate, $endDate])->get();
            $tasks = Task::whereBetween('due_date', [$startDate, $endDate])->get();

            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            $summary = Gemini::generativeModel($modelName)->generateContent([
                'ÖNEMLİ: Şu anki gerçek tarih ve saat: ' . $currentDate . ' Bu tarihi referans alarak işlem yap.',
                'Şu tarih aralığındaki etkinlik ve görevleri kullanıcıya doğal bir dille özetle:',
                'Tarih Aralığı: ' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
                'Etkinlikler: ' . json_encode($events, JSON_UNESCAPED_UNICODE),
                'Görevler: ' . json_encode($tasks, JSON_UNESCAPED_UNICODE),
                'Yanıtını kullanıcı anlayacak şekilde Türkçe olarak ver ve şu anki tarihle karşılaştırmalı ifadeler kullan.'
            ]);

            return $summary->text();
        } catch (\Exception $e) {
            return 'Özet bilgi oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }

    private function handleNewTask(array $data)
    {
        try {
            // Veri gerekli bilgiler içinde olabilir
            $taskData = $data;
            if (isset($data['gerekli_bilgiler'])) {
                $taskData = $data['gerekli_bilgiler'];
            }
            
            // Data kontrolü
            if (!isset($taskData['title']) && !isset($taskData['başlık'])) {
                return 'Görev eklemek için başlık bilgisi eksik.';
            }
            
            // Başlık değerini al
            $title = $taskData['title'] ?? $taskData['başlık'] ?? null;
            
            // Tarih değerini al
            $dueDate = null;
            
            // Tarih bilgilerini kontrol et - farklı formatlarda gelebilir
            if (isset($taskData['due_date'])) {
                $dueDate = Carbon::parse($taskData['due_date']);
            } elseif (isset($taskData['bitiş_tarihi'])) {
                $dueDate = Carbon::parse($taskData['bitiş_tarihi']);
            } elseif (isset($taskData['tarih'])) {
                $dueDate = Carbon::parse($taskData['tarih']);
                
                // Saat bilgisi varsa ekle
                if (isset($taskData['saat'])) {
                    $dueDate->hour(explode(':', $taskData['saat'])[0] ?? 0)
                            ->minute(explode(':', $taskData['saat'])[1] ?? 0);
                }
            }
            
            // Eğer tarih belirtilmemişse bugünü kullan
            if (!$dueDate) {
                $dueDate = Carbon::today()->endOfDay();
            }
            
            // Durum bilgisini al ve İngilizce karşılıklara çevir
            $status = $taskData['status'] ?? $taskData['durum'] ?? 'beklemede';
            $statusMap = [
                'beklemede' => 'pending',
                'devam_ediyor' => 'in_progress', 
                'tamamlandı' => 'completed',
                'iptal' => 'cancelled'
            ];
            $status = $statusMap[$status] ?? 'pending';
            
            // Öncelik bilgisini al
            $priority = $taskData['priority'] ?? $taskData['öncelik'] ?? 2; // Varsayılan orta öncelik
            if (is_string($priority)) {
                // Metin olarak öncelik belirtilmişse sayıya çevir
                $priorityMap = [
                    'düşük' => 1,
                    'normal' => 2, 
                    'orta' => 2,
                    'yüksek' => 3,
                    'acil' => 3,
                    'kritik' => 3,
                    'low' => 1,
                    'medium' => 2,
                    'high' => 3,
                    'urgent' => 3,
                    'critical' => 3
                ];
                $priority = $priorityMap[strtolower($priority)] ?? 2;
            }
            
            // Tamamlanma durumunu al
            $isCompleted = $taskData['is_completed'] ?? $taskData['tamamlandı'] ?? false;
            
            // Boolean değerleri düzelt
            if (is_string($isCompleted)) {
                $isCompleted = in_array(strtolower($isCompleted), ['true', 'evet', 'yes', 'tamamlandı', 'completed']);
            }
            
            // Durum "tamamlandı" ise is_completed = true olmalı
            if ($status === 'completed') {
                $isCompleted = true;
            }
            
            // Görevi oluştur
            $task = Task::create([
                'title' => $title,
                'description' => $taskData['description'] ?? $taskData['açıklama'] ?? null,
                'due_date' => $dueDate,
                'status' => $status,
                'priority' => $priority,
                'is_completed' => $isCompleted,
                'user_id' => auth()->id() ?? 1
            ]);

            return "Yeni görev başarıyla oluşturuldu: {$task->title} - " . $dueDate->format('d.m.Y H:i');
        } catch (\Exception $e) {
            return 'Görev oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }
} 