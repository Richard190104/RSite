<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddHierarchyToCategories extends BaseMigration
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
        $table = $this->table('categories');
        $table->addColumn('parent_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->addColumn('show_in_gallery', 'boolean', [
            'default' => true,
            'null' => false,
        ]);
        $table->addIndex([
            'parent_id',
            ], [
            'name' => 'BY_PARENT_ID',
            'unique' => false,
        ]);
        $table->addForeignKey('parent_id', 'categories', 'id', [
            'delete' => 'SET_NULL',
            'update' => 'CASCADE',
        ]);
        $table->update();
    }
}
