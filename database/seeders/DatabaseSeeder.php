<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
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
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
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

        $this->seedLastMonthReportSamples($user);

        // Test kullanıcısı
        User::create([
            'name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }

    /**
     * Dönem raporunda dolu görünmesi için son ~30 güne yayılmış örnek görev ve etkinlikler.
     */
    private function seedLastMonthReportSamples(User $user): void
    {
        $now = Carbon::now();

        $lastMonthTasks = [
            ['title' => 'Sprint retrospective notları', 'description' => 'Geçen sprint aksiyon maddeleri', 'days_ago' => 4, 'hour' => 16, 'priority' => 2, 'status' => 'completed', 'is_completed' => true],
            ['title' => 'Müşteri NDA imza süreci', 'description' => 'Hukuk ile koordinasyon', 'days_ago' => 2, 'hour' => 11, 'priority' => 3, 'status' => 'in-progress', 'is_completed' => false],
            ['title' => 'Q2 roadmap taslak', 'description' => 'Ürün ve geliştirme birlikte gözden geçirilecek', 'days_ago' => 1, 'hour' => 14, 'priority' => 3, 'status' => 'pending', 'is_completed' => false],
            ['title' => 'Staging ortamı smoke test', 'description' => 'Kritik akışlar', 'days_ago' => 6, 'hour' => 10, 'priority' => 2, 'status' => 'completed', 'is_completed' => true],
            ['title' => 'Fatura mutabakatı', 'description' => 'Muhasebe ekibi', 'days_ago' => 9, 'hour' => 9, 'priority' => 2, 'status' => 'pending', 'is_completed' => false],
            ['title' => 'Güvenlik bağımlılık taraması', 'description' => 'composer audit çıktısı', 'days_ago' => 11, 'hour' => 15, 'priority' => 3, 'status' => 'completed', 'is_completed' => true],
            ['title' => 'Kullanıcı geri bildirim özeti', 'description' => 'Support kanalından derleme', 'days_ago' => 13, 'hour' => 13, 'priority' => 1, 'status' => 'pending', 'is_completed' => false],
            ['title' => 'Performans metrikleri panosu', 'description' => 'Grafana / log özet', 'days_ago' => 16, 'hour' => 11, 'priority' => 2, 'status' => 'in-progress', 'is_completed' => false],
            ['title' => 'Çalışan memnuniyet anketi', 'description' => 'İK hatırlatması', 'days_ago' => 19, 'hour' => 10, 'priority' => 1, 'status' => 'completed', 'is_completed' => true],
            ['title' => 'Yedekleme geri yükleme testi', 'description' => 'Aylık rutin', 'days_ago' => 22, 'hour' => 8, 'priority' => 3, 'status' => 'completed', 'is_completed' => true],
            ['title' => 'Dokümantasyon güncelleme (API)', 'description' => 'Swagger / OpenAPI', 'days_ago' => 24, 'hour' => 14, 'priority' => 2, 'status' => 'pending', 'is_completed' => false],
            ['title' => 'Lisans yenileme takvimi', 'description' => 'SaaS araçları', 'days_ago' => 27, 'hour' => 12, 'priority' => 1, 'status' => 'pending', 'is_completed' => false],
            ['title' => 'Ofis ağı kesinti raporu', 'description' => 'IT ticket özeti', 'days_ago' => 29, 'hour' => 17, 'priority' => 2, 'status' => 'completed', 'is_completed' => true],
        ];

        foreach ($lastMonthTasks as $row) {
            $due = $now->copy()->subDays($row['days_ago'])->setTime((int) $row['hour'], 0, 0);
            Task::create([
                'user_id' => $user->id,
                'title' => $row['title'],
                'description' => $row['description'],
                'due_date' => $due,
                'priority' => $row['priority'],
                'status' => $row['status'],
                'is_completed' => $row['is_completed'],
            ]);
        }

        $lastMonthEvents = [
            [
                'title' => 'Üç günlük planlama workshop',
                'description' => 'Ürün + teknik backlog',
                'start_days_ago' => 21,
                'end_days_ago' => 19,
                'start_hour' => 9,
                'end_hour' => 17,
                'location' => 'Toplantı salonu A',
                'all_day' => false,
            ],
            [
                'title' => 'Mart dönem kapanış',
                'description' => 'Muhasebe ve raporlama',
                'start_days_ago' => 28,
                'end_days_ago' => 28,
                'start_hour' => 0,
                'end_hour' => 23,
                'location' => null,
                'all_day' => true,
            ],
            [
                'title' => 'Müşteri demo: Akıllı Ajanda',
                'description' => 'Canlı ortam gösterimi',
                'start_days_ago' => 7,
                'end_days_ago' => 7,
                'start_hour' => 14,
                'end_hour' => 15,
                'location' => 'Google Meet',
                'all_day' => false,
            ],
            [
                'title' => 'Haftalık senkron',
                'description' => 'Tüm ekipler',
                'start_days_ago' => 3,
                'end_days_ago' => 3,
                'start_hour' => 10,
                'end_hour' => 10,
                'location' => 'Zoom',
                'all_day' => false,
            ],
            [
                'title' => '1:1 görüşmeler (geliştirme)',
                'description' => 'Birebir blok',
                'start_days_ago' => 12,
                'end_days_ago' => 12,
                'start_hour' => 13,
                'end_hour' => 17,
                'location' => 'Ofis / uzaktan',
                'all_day' => false,
            ],
            [
                'title' => 'Altyapı bakım penceresi',
                'description' => 'Veritabanı minor upgrade',
                'start_days_ago' => 17,
                'end_days_ago' => 17,
                'start_hour' => 23,
                'end_hour' => 23,
                'location' => 'Sunucu odası',
                'all_day' => false,
            ],
            [
                'title' => 'Tasarım kritik oturumu',
                'description' => 'Yeni dashboard mockup',
                'start_days_ago' => 8,
                'end_days_ago' => 8,
                'start_hour' => 11,
                'end_hour' => 12,
                'location' => 'Figma + toplantı odası',
                'all_day' => false,
            ],
            [
                'title' => 'Çapraz fonksiyon kahvaltısı',
                'description' => 'Satış + destek',
                'start_days_ago' => 14,
                'end_days_ago' => 14,
                'start_hour' => 9,
                'end_hour' => 10,
                'location' => 'Kafeterya',
                'all_day' => false,
            ],
            [
                'title' => 'Sürüm notları yayın',
                'description' => 'v1.4.0 duyurusu',
                'start_days_ago' => 5,
                'end_days_ago' => 5,
                'start_hour' => 16,
                'end_hour' => 16,
                'location' => null,
                'all_day' => false,
            ],
            [
                'title' => 'Yasal danışmanlık görüşmesi',
                'description' => 'Sözleşme maddeleri',
                'start_days_ago' => 25,
                'end_days_ago' => 25,
                'start_hour' => 15,
                'end_hour' => 16,
                'location' => 'Avukat ofisi',
                'all_day' => false,
            ],
        ];

        foreach ($lastMonthEvents as $row) {
            if (! empty($row['all_day'])) {
                $start = $now->copy()->subDays($row['start_days_ago'])->startOfDay();
                $end = $now->copy()->subDays($row['end_days_ago'])->endOfDay();
                if ($end->lessThan($start)) {
                    $end = $start->copy()->endOfDay();
                }
            } else {
                $start = $now->copy()->subDays($row['start_days_ago'])->setTime((int) $row['start_hour'], 0, 0);
                $end = $now->copy()->subDays($row['end_days_ago'])->setTime((int) $row['end_hour'], (int) $row['end_hour'] === 23 ? 45 : 0, 0);
                if ($end->lessThanOrEqualTo($start)) {
                    $end = $start->copy()->addHour();
                }
            }

            Event::create([
                'user_id' => $user->id,
                'title' => $row['title'],
                'description' => $row['description'],
                'start_date' => $start,
                'end_date' => $end,
                'location' => $row['location'],
                'all_day' => $row['all_day'],
            ]);
        }
    }
}
