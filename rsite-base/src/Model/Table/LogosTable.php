<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * No app-specific columns — this pass-through exists only so the bare
 * 'Logos' alias (still used directly by templates/element/footer.php)
 * keeps resolving to a real class and hands out the plugin's own entity.
 */
class LogosTable extends \Rcore\Model\Table\LogosTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setEntityClass(\Rcore\Model\Entity\Logo::class);
    }
}
