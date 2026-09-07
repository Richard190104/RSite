<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddReviryPage extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('pages')->insert([
            ['slug' => 'reviry', 'title' => 'Fishing grounds', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM pages WHERE slug = 'reviry'");
    }
}
