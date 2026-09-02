<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \Cake\I18n\Date|null $date
 * @property string|null $location
 * @property string|null $time
 * @property int|null $category_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\Category|null $category
 */
class Event extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'description' => true,
        'date' => true,
        'location' => true,
        'time' => true,
        'category_id' => true,
        'created' => true,
        'modified' => true,
    ];
}
