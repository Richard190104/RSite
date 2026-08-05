<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $image
 * @property int|null $category_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\Category|null $category
 */
class Gallery extends Entity
{
    protected array $_accessible = [
        'image' => true,
        'category_id' => true,
        'created' => true,
        'modified' => true,
    ];
}