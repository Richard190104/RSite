<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddKontaktPage extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('pages')->insert([
            ['slug' => 'kontakt', 'title' => 'Kontakt', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM pages WHERE slug = 'kontakt'");
    }
}