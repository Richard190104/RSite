<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property int|null $parent_id
 * @property bool $show_in_gallery
 * @property string|null $image
 * @property string|null $description
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\Category|null $parent_category
 */
class Category extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'parent_id' => true,
        'show_in_gallery' => true,
        'image' => true,
        'description' => true,
        'created' => true,
        'modified' => true,
    ];
}