<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddFooterTextsConfig extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('texts');
        $table->truncate();
        $now = date('Y-m-d H:i:s');
        $table->insert([
            ['slug' => 'Footer Description', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'Organisation ICO', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'Organisation Gmail', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'Organisation Address', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'City', 'value' => null, 'created' => $now, 'modified' => $now],
        ])->saveData();
    }
}
