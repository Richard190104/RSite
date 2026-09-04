<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string|null $content
 * @property string|null $image
 * @property \Cake\I18n\Date $date
 * @property int|null $category_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\Category|null $category
 */
class News extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'description' => true,
        'content' => true,
        'image' => true,
        'date' => true,
        'category_id' => true,
        'created' => true,
        'modified' => true,
    ];
}