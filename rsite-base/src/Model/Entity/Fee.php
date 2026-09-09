<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property string $price Free text — e.g. "35 €, 5 €" or "12 € + permit 15 €".
 * @property string $category Key into FeesTable::CATEGORIES.
 * @property int $position
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Fee extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'price' => true,
        'category' => true,
        'position' => true,
        'created' => true,
        'modified' => true,
    ];
}
