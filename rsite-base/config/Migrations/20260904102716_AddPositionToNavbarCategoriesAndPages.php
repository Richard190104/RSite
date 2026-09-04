<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPositionToNavbarCategoriesAndPages extends BaseMigration
{
    public function change(): void
    {
        $navbarCategories = $this->table('navbar_categories');
        $navbarCategories->addColumn('position', 'integer', [
            'default' => 0,
            'null' => false,
            'after' => 'title',
        ]);
        $navbarCategories->update();

        $pages = $this->table('pages');
        $pages->addColumn('position', 'integer', [
            'default' => 0,
            'null' => false,
            'after' => 'navbar_category_id',
        ]);
        $pages->update();
    }
}
