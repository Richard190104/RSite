<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * No app-specific columns — this pass-through exists only so the bare
 * 'NavbarCategories' alias (still used directly by templates/element/navbar.php)
 * keeps resolving to a real class and hands out the plugin's own entity.
 */
class NavbarCategoriesTable extends \Rcore\Model\Table\NavbarCategoriesTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setEntityClass(\Rcore\Model\Entity\NavbarCategory::class);
    }
}
