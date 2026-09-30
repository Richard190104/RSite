<?php
declare(strict_types=1);

namespace App\Model\Table;

class PagesTable extends \Rcore\Model\Table\PagesTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Required so fetchTable('Pages') hands out this app's own Page
        // entity (which has the extra navbar_category_id/position columns
        // the plugin's generic entity doesn't know about) rather than the
        // plugin's own — see this plugin's README for why this is needed.
        $this->setEntityClass(\App\Model\Entity\Page::class);

        $this->belongsTo('NavbarCategories', [
            'foreignKey' => 'navbar_category_id',
        ]);
    }
}
