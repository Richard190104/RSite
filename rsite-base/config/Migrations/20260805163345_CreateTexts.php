<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateTexts extends BaseMigration
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
        $table->addColumn('slug', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('value', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex(['slug'], [
            'name' => 'UNIQUE_SLUG',
            'unique' => true,
        ]);
        $table->create();

        $now = date('Y-m-d H:i:s');
        $table->insert([
            ['slug' => 'title', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'name', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'organisation', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'city', 'value' => null, 'created' => $now, 'modified' => $now],
        ])->saveData();
    }
}