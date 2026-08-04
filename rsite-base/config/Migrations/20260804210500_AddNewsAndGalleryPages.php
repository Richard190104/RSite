<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddNewsAndGalleryPages extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('pages')->insert([
            ['slug' => 'news', 'title' => 'News', 'created' => $now, 'modified' => $now],
            ['slug' => 'gallery', 'title' => 'Gallery', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM pages WHERE slug IN ('news', 'gallery')");
    }
}