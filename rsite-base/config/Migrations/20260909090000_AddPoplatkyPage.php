<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Public "Poplatky" (fees/membership prices) page — same convention as the
 * other fixed pages (see e.g. 20260908092000_AddZarybneniePage.php): one
 * row in `pages`. All the actual price content is hardcoded in
 * templates/Pages/poplatky.php for now (no admin management yet) — this
 * row only exists so the page gets a title/banner location like every
 * other page.
 */
class AddPoplatkyPage extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('pages')->insert([
            ['slug' => 'poplatky', 'title' => 'Poplatky', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM pages WHERE slug = 'poplatky'");
    }
}
