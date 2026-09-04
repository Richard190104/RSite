<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddAktivityPage extends BaseMigration
{
    public function up(): void
    {
        $exists = $this->fetchRow("SELECT id FROM pages WHERE slug = 'aktivity'");
        if ($exists) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->table('pages')->insert([
            ['slug' => 'aktivity', 'title' => 'Aktivity', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM pages WHERE slug = 'aktivity'");
    }
}
