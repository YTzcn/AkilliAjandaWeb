<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Task;
use App\Models\Note;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo kullanıcısı oluştur
        $user = User::create([
            'name' => 'Demo Kullanıcı',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Bugünün tarihini al
        $today = Carbon::today();

        // Örnek etkinlikler oluştur
        $events = [
            [
                'title' => 'Toplantı',
                'description' => 'Haftalık ekip toplantısı',
                'start_date' => $today->copy()->addDays(1)->setHour(10),
                'end_date' => $today->copy()->addDays(1)->setHour(11),
                'location' => 'Toplantı Odası',
                'all_day' => false,
            ],
            [
                'title' => 'Doğum Günü',
                'description' => 'Ahmet\'in doğum günü kutlaması',
                'start_date' => $today->copy()->addDays(3),
                'end_date' => $today->copy()->addDays(3),
                'location' => 'Kafe',
                'all_day' => true,
            ],
            [
                'title' => 'Proje Teslimi',
                'description' => 'Proje final teslimi',
                'start_date' => $today->copy()->addDays(5)->setHour(15),
                'end_date' => $today->copy()->addDays(5)->setHour(16),
                'location' => 'Ofis',
                'all_day' => false,
            ],
        ];

        foreach ($events as $event) {
            Event::create(array_merge($event, ['user_id' => $user->id]));
        }

        // Örnek görevler oluştur
        $tasks = [
            [
                'title' => 'Rapor Hazırla',
                'description' => 'Aylık satış raporu hazırlanacak',
                'due_date' => $today->copy()->addDays(2)->setHour(17),
                'priority' => 3, // Yüksek
                'status' => 'pending',
                'is_completed' => false,
            ],
            [
                'title' => 'E-postaları Yanıtla',
                'description' => 'Bekleyen e-postalar yanıtlanacak',
                'due_date' => $today->copy()->addDay()->setHour(12),
                'priority' => 2, // Orta
                'status' => 'pending',
                'is_completed' => false,
            ],
            [
                'title' => 'Dosyaları Düzenle',
                'description' => 'Proje dosyaları düzenlenecek',
                'due_date' => $today->copy()->addDays(4)->setHour(15),
                'priority' => 1, // Düşük
                'status' => 'pending',
                'is_completed' => false,
            ],
            [
                'title' => 'Tamamlanmış Görev',
                'description' => 'Bu görev tamamlandı',
                'due_date' => $today->copy()->subDay(),
                'priority' => 2,
                'status' => 'completed',
                'is_completed' => true,
            ],
        ];

        foreach ($tasks as $task) {
            Task::create(array_merge($task, ['user_id' => $user->id]));
        }

        // Test kullanıcısı
        User::create([
            'name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'password' => Hash::make('123456'),
        ]);
    }
}
