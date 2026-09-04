<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPositionToCategories extends BaseMigration
{
    public function change(): void
    {
        $categories = $this->table('categories');
        $categories->addColumn('position', 'integer', [
            'default' => 0,
            'null' => false,
            'after' => 'title',
        ]);
        $categories->update();
    }
}
