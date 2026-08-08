<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $path
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Logo extends Entity
{
    /**
     * 'name' identifies the logo slot the templates look up, so it is not
     * mass assignable — it only ever changes in a migration.
     */
    protected array $_accessible = [
        'name' => false,
        'path' => true,
        'created' => true,
        'modified' => true,
    ];
}
