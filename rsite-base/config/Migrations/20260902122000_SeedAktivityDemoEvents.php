<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class SeedAktivityDemoEvents extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('categories')->insert([
            ['title' => 'Brigády', 'parent_id' => null, 'show_in_gallery' => false, 'created' => $now, 'modified' => $now],
            ['title' => 'Športové podujatia', 'parent_id' => null, 'show_in_gallery' => false, 'created' => $now, 'modified' => $now],
            ['title' => 'Zarybňovanie', 'parent_id' => null, 'show_in_gallery' => false, 'created' => $now, 'modified' => $now],
            ['title' => 'Vzdelávanie', 'parent_id' => null, 'show_in_gallery' => false, 'created' => $now, 'modified' => $now],
            ['title' => 'Súťaže', 'parent_id' => null, 'show_in_gallery' => false, 'created' => $now, 'modified' => $now],
        ])->saveData();

        $categories = $this->fetchAll('SELECT id, title FROM categories WHERE title IN (\'Brigády\', \'Športové podujatia\', \'Zarybňovanie\', \'Vzdelávanie\', \'Súťaže\')');
        $byTitle = [];
        foreach ($categories as $category) {
            $byTitle[$category['title']] = (int)$category['id'];
        }

        $this->table('events')->insert([
            [
                'title' => 'Jarná brigáda pri VN Mláka',
                'description' => 'Spoločné čistenie okolia revíru a údržba prístupových ciest. Tešíme sa na každého dobrovoľníka.',
                'date' => date('Y-m-d', strtotime('+7 days')),
                'location' => 'VN Mláka',
                'time' => '08:00',
                'category_id' => $byTitle['Brigády'] ?? null,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'title' => 'Detské rybárske preteky',
                'description' => 'Zábavné popoludnie pre najmenších rybárov s cenami a občerstvením.',
                'date' => date('Y-m-d', strtotime('+14 days')),
                'location' => 'Rybník za mestom',
                'time' => '09:30',
                'category_id' => $byTitle['Športové podujatia'] ?? null,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'title' => 'Zarybňovanie pstruhom',
                'description' => 'Vysádzanie pstruha potočného do vybraných úsekov našich revírov.',
                'date' => date('Y-m-d', strtotime('+21 days')),
                'location' => 'Laborec',
                'time' => '07:00',
                'category_id' => $byTitle['Zarybňovanie'] ?? null,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'title' => 'Prednáška o ochrane vôd',
                'description' => 'Odborná prednáška pre členov a verejnosť o ochrane vodných tokov.',
                'date' => date('Y-m-d', strtotime('-10 days')),
                'location' => 'Kultúrny dom',
                'time' => '17:00',
                'category_id' => $byTitle['Vzdelávanie'] ?? null,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'title' => 'Klubové majstrovstvá',
                'description' => 'Tradičné klubové majstrovstvá v športovom rybolove.',
                'date' => date('Y-m-d', strtotime('-3 days')),
                'location' => 'VN Mláka',
                'time' => '06:00',
                'category_id' => $byTitle['Súťaže'] ?? null,
                'created' => $now,
                'modified' => $now,
            ],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM events WHERE title IN (
            'Jarná brigáda pri VN Mláka',
            'Detské rybárske preteky',
            'Zarybňovanie pstruhom',
            'Prednáška o ochrane vôd',
            'Klubové majstrovstvá'
        )");
        $this->execute("DELETE FROM categories WHERE title IN (
            'Brigády',
            'Športové podujatia',
            'Zarybňovanie',
            'Vzdelávanie',
            'Súťaže'
        )");
    }
}
