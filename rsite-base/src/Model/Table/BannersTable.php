<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * No app-specific columns — this pass-through exists only so the bare
 * 'Banners' alias (still used directly by public templates/PagesController
 * for the "virtual location" tiles) keeps resolving to a real class and
 * hands out the plugin's own entity.
 */
class BannersTable extends \Rcore\Model\Table\BannersTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setEntityClass(\Rcore\Model\Entity\Banner::class);
    }
}
