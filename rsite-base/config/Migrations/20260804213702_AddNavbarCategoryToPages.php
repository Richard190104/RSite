<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddNavbarCategoryToPages extends BaseMigration
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
        $table->addColumn('navbar_category_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->addIndex([
            'navbar_category_id',
            ], [
            'name' => 'BY_NAVBAR_CATEGORY_ID',
            'unique' => false,
        ]);
        $table->addForeignKey('navbar_category_id', 'navbar_categories', 'id', [
            'delete' => 'SET_NULL',
            'update' => 'CASCADE',
        ]);
        $table->update();
    }
}
