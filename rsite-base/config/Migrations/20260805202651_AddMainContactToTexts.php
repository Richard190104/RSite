<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMainContactToTexts extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('texts')->insert([
            ['slug' => 'mainContact', 'value' => '000000000', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM texts WHERE slug = 'mainContact'");
    }
}