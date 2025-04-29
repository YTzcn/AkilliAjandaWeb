<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\GoogleCalendar\GoogleCalendar;

class GoogleCalendarService
{
    protected $client;
    protected $service;
    protected $calendarId;

    public function __construct()
    {
        $this->calendarId = config('google-calendar.calendar_id');
    }

    /**
     * Google istemcisini ayarla
     *
     * @param User $user
     * @return void
     */
    public function setupClient(User $user)
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        if ($user->google_token) {
            $accessToken = json_decode($user->google_token, true);
            $client->setAccessToken($accessToken);

            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $user->google_token = json_encode($client->getAccessToken());
                    $user->save();
                } else {
                    // Token geçersiz - kullanıcı tekrar yetkilendirmeli
                    return false;
                }
            }
            
            $this->client = $client;
            $this->service = new Google_Service_Calendar($client);
            return true;
        }
        
        $this->client = $client;
        return false;
    }

    /**
     * Google yetkilendirme URL'si oluştur
     *
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Kod ile token al
     *
     * @param string $code
     * @return array
     */
    public function getAccessToken($code)
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * Google takviminden olayları listele
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function listEvents(Carbon $startDate, Carbon $endDate)
    {
        try {
            $optParams = [
                'timeMin' => $startDate->toRfc3339String(),
                'timeMax' => $endDate->toRfc3339String(),
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ];

            $results = $this->service->events->listEvents($this->calendarId, $optParams);
            return $results->getItems();
        } catch (\Exception $e) {
            Log::error('Google Calendar Event Listesi Alınamadı: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Etkinliği Google Takvim'e senkronize et
     *
     * @param Event $event
     * @return bool
     */
    public function syncEventToGoogle(Event $event)
    {
        try {
            if (!$this->service) {
                return false;
            }

            $googleEvent = new Google_Service_Calendar_Event([
                'summary' => $event->title,
                'description' => $event->description ?? '',
                'start' => [
                    'dateTime' => $event->start_date->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => $event->end_date->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'location' => $event->location ?? '',
            ]);

            if ($event->google_event_id) {
                // Güncelleme işlemi
                $updatedEvent = $this->service->events->update($this->calendarId, $event->google_event_id, $googleEvent);
            } else {
                // Yeni oluşturma işlemi
                $createdEvent = $this->service->events->insert($this->calendarId, $googleEvent);
                $event->google_event_id = $createdEvent->getId();
            }

            $event->synced_to_google = true;
            $event->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar Event Sync Hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Google Takvimden etkinliği sil
     *
     * @param Event $event
     * @return bool
     */
    public function deleteEventFromGoogle(Event $event)
    {
        try {
            if (!$this->service || !$event->google_event_id) {
                return false;
            }

            $this->service->events->delete($this->calendarId, $event->google_event_id);
            
            $event->google_event_id = null;
            $event->synced_to_google = false;
            $event->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar Event Silme Hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Google Takvimden olayları içe aktar
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function importEventsFromGoogle(Carbon $startDate, Carbon $endDate)
    {
        try {
            $googleEvents = $this->listEvents($startDate, $endDate);
            $importedEvents = [];
            $user = Auth::user();

            foreach ($googleEvents as $googleEvent) {
                // ID'ye göre mevcut olayı kontrol et
                $existingEvent = Event::where('google_event_id', $googleEvent->getId())->first();

                if ($existingEvent) {
                    // Güncelleme
                    $existingEvent->update([
                        'title' => $googleEvent->getSummary(),
                        'description' => $googleEvent->getDescription(),
                        'start_date' => Carbon::parse($googleEvent->getStart()->dateTime),
                        'end_date' => Carbon::parse($googleEvent->getEnd()->dateTime),
                        'location' => $googleEvent->getLocation(),
                        'synced_to_google' => true,
                    ]);
                    $importedEvents[] = $existingEvent;
                } else {
                    // Yeni oluştur
                    $newEvent = Event::create([
                        'user_id' => $user->id,
                        'title' => $googleEvent->getSummary(),
                        'description' => $googleEvent->getDescription(),
                        'start_date' => Carbon::parse($googleEvent->getStart()->dateTime),
                        'end_date' => Carbon::parse($googleEvent->getEnd()->dateTime),
                        'location' => $googleEvent->getLocation(),
                        'google_event_id' => $googleEvent->getId(),
                        'synced_to_google' => true,
                    ]);
                    $importedEvents[] = $newEvent;
                }
            }

            return $importedEvents;
        } catch (\Exception $e) {
            Log::error('Google Calendar Import Hatası: ' . $e->getMessage());
            return [];
        }
    }
} 