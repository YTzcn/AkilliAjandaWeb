<?php

namespace App\Services\LLM\Handlers;

use App\Models\Event;
use Carbon\Carbon;
use Exception;

class EventHandler
{
    /**
     * Yeni etkinlik ekleme işlemlerini yönetir
     *
     * @param array $data İşlem verileri
     * @return string Yanıt
     */
    public function handleNewEvent(array $data): string
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
        } catch (Exception $e) {
            return 'Etkinlik eklenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
    
    /**
     * Özet bilgi isteği işlemlerini yönetir
     *
     * @param array $data İşlem verileri
     * @param App\Services\LLM\Providers\ProviderInterface $provider
     * @return string Yanıt
     */
    public function handleSummaryRequest(array $data, $provider): string
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
            $tasks = \App\Models\Task::whereBetween('due_date', [$startDate, $endDate])->get();

            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            $prompt = [
                'ÖNEMLİ: Şu anki gerçek tarih ve saat: ' . $currentDate . ' Bu tarihi referans alarak işlem yap.',
                'Şu tarih aralığındaki etkinlik ve görevleri kullanıcıya doğal bir dille özetle:',
                'Tarih Aralığı: ' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
                'Etkinlikler: ' . json_encode($events, JSON_UNESCAPED_UNICODE),
                'Görevler: ' . json_encode($tasks, JSON_UNESCAPED_UNICODE),
                'Yanıtını kullanıcı anlayacak şekilde Türkçe olarak ver ve şu anki tarihle karşılaştırmalı ifadeler kullan.'
            ];

            $summary = $provider->generateContent($prompt);
            return $summary;
        } catch (Exception $e) {
            return 'Özet bilgi oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }
} 