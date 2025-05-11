<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use App\Mail\UpcomingNotification;
use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendUpcomingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-upcoming';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yaklaşan etkinlik ve görevler için bildirim gönderir';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendEventNotifications();
        $this->sendTaskNotifications();
        
        $this->info('Bildirimler gönderildi.');
    }

    private function sendNotificationWithRetry($userId, $title, $body, $data, $maxRetries = 3)
    {
        $attempt = 0;
        $success = false;

        while (!$success && $attempt < $maxRetries) {
            $attempt++;
            $response = NotificationHelper::sendNotificationToUser($userId, $title, $body, $data);
            
            if ($response) {
                $success = true;
                break;
            }

            if ($attempt < $maxRetries) {
                sleep(2); // 2 saniye bekle
            }
        }

        return $success;
    }

    private function sendEventNotifications()
    {
        // 30 dakika içinde başlayacak etkinlikleri bul
        $upcomingEvents = Event::where('start_date', '>', Carbon::now())
            ->where('start_date', '<=', Carbon::now()->addMinutes(30))
            ->where('notification_sent', false)
            ->with('user') // Kullanıcı bilgilerini eager loading ile al
            ->get();

        foreach ($upcomingEvents as $event) {
            $timeUntilStart = Carbon::now()->diffInMinutes($event->start_date);
            
            $title = '📅 Yaklaşan Etkinlik Hatırlatması';
            $body = "{$event->title} etkinliği {$timeUntilStart} dakika içinde başlayacak!";
            
            // Push bildirimi gönder
            $pushSent = $this->sendNotificationWithRetry($event->user_id, $title, $body, [
                'type' => 'event',
                'event_id' => $event->id
            ]);

            // E-posta gönder
            try {
                Mail::to($event->user->email)->send(new UpcomingNotification(
                    $title,
                    $body,
                    'event',
                    $event->id,
                    $event->title,
                    $timeUntilStart
                ));
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error('E-posta gönderme hatası:', [
                    'error' => $e->getMessage(),
                    'event_id' => $event->id,
                    'user_id' => $event->user_id
                ]);
                $emailSent = false;
            }

            // Eğer push veya e-posta bildirimlerinden en az biri başarılıysa
            if ($pushSent || $emailSent) {
                $event->update(['notification_sent' => true]);
            }
        }
    }

    private function sendTaskNotifications()
    {
        // Bugün son teslim tarihi olan ve tamamlanmamış görevleri bul
        $upcomingTasks = Task::where('due_date', '>=', Carbon::now()->startOfDay())
            ->where('due_date', '<=', Carbon::now()->endOfDay())
            ->where('is_completed', false)
            ->where('notification_sent', false)
            ->with('user') // Kullanıcı bilgilerini eager loading ile al
            ->get();

        foreach ($upcomingTasks as $task) {
            $timeUntilDue = Carbon::now()->diffInHours($task->due_date);
            
            $title = '📋 Görev Hatırlatması';
            $body = "{$task->title} görevinin teslim tarihi bugün!";
            
            if ($timeUntilDue <= 3) {
                $body = "⚠️ {$task->title} görevinin teslim tarihine {$timeUntilDue} saat kaldı!";
            }
            
            // Push bildirimi gönder
            $pushSent = $this->sendNotificationWithRetry($task->user_id, $title, $body, [
                'type' => 'task',
                'task_id' => $task->id
            ]);

            // E-posta gönder
            try {
                Mail::to($task->user->email)->send(new UpcomingNotification(
                    $title,
                    $body,
                    'task',
                    $task->id,
                    $task->title,
                    $timeUntilDue
                ));
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error('E-posta gönderme hatası:', [
                    'error' => $e->getMessage(),
                    'task_id' => $task->id,
                    'user_id' => $task->user_id
                ]);
                $emailSent = false;
            }

            // Eğer push veya e-posta bildirimlerinden en az biri başarılıysa
            if ($pushSent || $emailSent) {
                $task->update(['notification_sent' => true]);
            }
        }
    }
}
