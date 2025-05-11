<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use App\Models\Event;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
            ->get();

        foreach ($upcomingEvents as $event) {
            $timeUntilStart = Carbon::now()->diffInMinutes($event->start_date);
            
            $title = '📅 Yaklaşan Etkinlik Hatırlatması';
            $body = "{$event->title} etkinliği {$timeUntilStart} dakika içinde başlayacak!";
            
            if ($this->sendNotificationWithRetry($event->user_id, $title, $body, [
                'type' => 'event',
                'event_id' => $event->id
            ])) {
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
            ->get();

        foreach ($upcomingTasks as $task) {
            $timeUntilDue = Carbon::now()->diffInHours($task->due_date);
            
            $title = '📋 Görev Hatırlatması';
            $body = "{$task->title} görevinin teslim tarihi bugün!";
            
            if ($timeUntilDue <= 3) {
                $body = "⚠️ {$task->title} görevinin teslim tarihine {$timeUntilDue} saat kaldı!";
            }
            
            if ($this->sendNotificationWithRetry($task->user_id, $title, $body, [
                'type' => 'task',
                'task_id' => $task->id
            ])) {
                $task->update(['notification_sent' => true]);
                
            }
        }
    }
}
