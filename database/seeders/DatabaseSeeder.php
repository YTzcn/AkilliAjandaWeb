<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Task;
use App\Models\Note;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        ]);

        // Demo için etkinlikler oluştur
        Event::create([
            'user_id' => $user->id,
            'title' => 'Proje Toplantısı',
            'description' => 'Haftalık proje ilerleme toplantısı',
            'start_time' => now()->addDays(2)->setHour(10)->setMinute(0),
            'end_time' => now()->addDays(2)->setHour(11)->setMinute(30),
            'location' => 'Toplantı Salonu A',
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Müşteri Görüşmesi',
            'description' => 'XYZ firması ile yeni proje hakkında görüşme',
            'start_time' => now()->addDays(3)->setHour(14)->setMinute(0),
            'end_time' => now()->addDays(3)->setHour(15)->setMinute(0),
            'location' => 'Zoom Toplantısı',
        ]);

        // Demo için görevler oluştur
        Task::create([
            'user_id' => $user->id,
            'title' => 'Rapor Hazırlama',
            'description' => 'Aylık finansal raporu hazırla',
            'due_date' => now()->addDays(5),
            'is_completed' => false,
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'E-postaları Yanıtla',
            'description' => 'Bekleyen tüm e-postaları yanıtla',
            'due_date' => now()->addDay(),
            'is_completed' => false,
        ]);

        Task::create([
            'user_id' => $user->id,
            'title' => 'Sunum Hazırla',
            'description' => 'Müşteri toplantısı için sunum hazırla',
            'due_date' => now()->addDays(2),
            'is_completed' => true,
        ]);

        // Demo için notlar oluştur
        Note::create([
            'user_id' => $user->id,
            'title' => 'Toplantı Notları',
            'content' => 'Proje ekibi ile yapılan toplantıda alınan kararlar:
            1. Sprint planlaması güncellendi
            2. Yeni özellikler önceliklendirildi
            3. Bir sonraki toplantı önümüzdeki hafta Çarşamba günü yapılacak',
        ]);

        Note::create([
            'user_id' => $user->id,
            'title' => 'Alışveriş Listesi',
            'content' => '- Ekmek
            - Süt
            - Yumurta
            - Meyve
            - Sebze',
        ]);

        // Test kullanıcısı
        User::create([
            'name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'password' => Hash::make('123456'),
        ]);
    }
}
