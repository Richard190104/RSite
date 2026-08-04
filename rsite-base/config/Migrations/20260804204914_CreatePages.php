<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePages extends BaseMigration
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
        $table = $this->table('pages');
        $table->addColumn('slug', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('title', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex([
            'slug',
            ], [
            'name' => 'UNIQUE_SLUG',
            'unique' => true,
        ]);
        $table->create();

        // Seed with the only real public page that exists so far. The rest
        // (news, galleries, ...) are still just admin-side stubs, not actual
        // pages yet — add them here once they're real.
        $now = date('Y-m-d H:i:s');
        $table->insert([
            ['slug' => 'home', 'title' => 'Home', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }
}
