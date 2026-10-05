<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * No app-specific columns — this pass-through exists so the bare
 * 'Categories' alias (still used directly by News/Events/Galleries) keeps
 * resolving to a real class and hands out the plugin's own entity, plus the
 * reverse associations back onto this app's own domain tables that the core
 * table deliberately doesn't know about.
 */
class CategoriesTable extends \Rcore\Model\Table\CategoriesTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setEntityClass(\Rcore\Model\Entity\Category::class);

        $this->hasMany('News', [
            'foreignKey' => 'category_id',
        ]);
        $this->hasMany('Events', [
            'foreignKey' => 'category_id',
        ]);
        $this->hasMany('Galleries', [
            'foreignKey' => 'category_id',
        ]);
    }
}
