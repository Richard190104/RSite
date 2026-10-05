<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * No app-specific columns — this pass-through exists only so the bare
 * 'Galleries' alias (still referenced by CategoriesTable's own hasMany)
 * keeps resolving to a real class and hands out the plugin's own entity.
 */
class GalleriesTable extends \Rcore\Model\Table\GalleriesTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setEntityClass(\Rcore\Model\Entity\Gallery::class);
    }
}
