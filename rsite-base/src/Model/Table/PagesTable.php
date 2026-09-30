<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * No app-specific columns anymore — navbar_category_id/position are now
 * natively supported by the plugin's own Page/PagesTable (see
 * Rcore\Model\Table\PagesTable), so this pass-through only needs to exist
 * for the bare 'Pages' alias many other tables/templates reference directly.
 */
class PagesTable extends \Rcore\Model\Table\PagesTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setEntityClass(\Rcore\Model\Entity\Page::class);
    }
}
