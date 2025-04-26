<?php

namespace App\Services\LLM\Handlers;

use App\Models\Task;
use Carbon\Carbon;
use Exception;

class TaskHandler
{
    /**
     * Yeni görev ekleme işlemlerini yönetir
     *
     * @param array $data İşlem verileri
     * @return string Yanıt
     */
    public function handleNewTask(array $data): string
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
        } catch (Exception $e) {
            return 'Görev oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }
    
    /**
     * Görev güncelleme işlemlerini yönetir
     *
     * @param array $data İşlem verileri
     * @return string Yanıt
     */
    public function handleTaskUpdate(array $data): string
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
        } catch (Exception $e) {
            return 'Görev güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
} 