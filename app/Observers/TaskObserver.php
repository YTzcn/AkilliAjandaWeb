<?php

namespace App\Observers;

use App\Models\Task;
use Pusher\Pusher;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    private function triggerPusherEvent($task, $action)
    {
        try {
            $options = [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ];

            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                $options
            );

            $channel = 'calendar-' . $task->user_id;
            $eventName = 'calendar-update';
            $data = [
                'action' => $action,
                'type' => 'task',
                'item' => $task
            ];

            Log::info('Pusher task event gönderiliyor', [
                'channel' => $channel,
                'event' => $eventName,
                'data' => $data
            ]);

            $result = $pusher->trigger($channel, $eventName, $data);
            
            Log::info('Pusher task event gönderildi', [
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Pusher task event gönderme hatası', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        Log::info('Task created', ['task' => $task]);
        $this->triggerPusherEvent($task, 'created');
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        Log::info('Task updated', ['task' => $task]);
        $this->triggerPusherEvent($task, 'updated');
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        Log::info('Task deleted', ['task' => $task]);
        $this->triggerPusherEvent($task, 'deleted');
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
