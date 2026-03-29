<?php

namespace App\Observers;

use App\Models\Event;
use Pusher\Pusher;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    private function triggerPusherEvent($event, $action)
    {
        try {
            if (!env('PUSHER_APP_KEY') || !env('PUSHER_APP_SECRET') || !env('PUSHER_APP_ID')) {
                return;
            }

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

            $channel = 'calendar-' . $event->user_id;
            $eventName = 'calendar-update';
            $data = [
                'action' => $action,
                'type' => 'event',
                'item' => $event
            ];

            Log::info('Pusher event gönderiliyor', [
                'channel' => $channel,
                'event' => $eventName,
                'data' => $data
            ]);

            $result = $pusher->trigger($channel, $eventName, $data);
            
            Log::info('Pusher event gönderildi', [
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Pusher event gönderme hatası', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        Log::info('Event created', ['event' => $event]);
        $this->triggerPusherEvent($event, 'created');
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        Log::info('Event updated', ['event' => $event]);
        $this->triggerPusherEvent($event, 'updated');
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        Log::info('Event deleted', ['event' => $event]);
        $this->triggerPusherEvent($event, 'deleted');
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }
}
