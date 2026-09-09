<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Public "Zarybnenie a úlovky" page — same convention as the other fixed
 * pages (see e.g. 20260907120000_AddReviryPage.php): one row in `pages`
 * with no content of its own, the actual PDF listings are managed
 * separately (Admin\StockingDocumentsController, Admin\CatchDocumentsController).
 */
class AddZarybneniePage extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('pages')->insert([
            ['slug' => 'zarybnenie', 'title' => 'Stocking & catches', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM pages WHERE slug = 'zarybnenie'");
    }
}
